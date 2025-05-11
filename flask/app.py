from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import numpy as np
import pandas as pd
from sklearn.neighbors import NearestNeighbors
import os
import mysql.connector
from mysql.connector import Error
import subprocess

app = Flask(__name__)
CORS(app)

# Define model directories for kidney and liver
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_DIR_KIDNEY = os.path.join(BASE_DIR, '..', 'models', 'kidney')
MODEL_DIR_LIVER = os.path.join(BASE_DIR, '..', 'models', 'liver')

###############################################
# LOAD MODELS & SCALERS FOR KIDNEY
###############################################

def safe_load(path):
    try:
        return joblib.load(path)
    except Exception as e:
        print(f"Could not load {path}: {e}")
        return None

kidney_model  = safe_load(os.path.join(MODEL_DIR_KIDNEY, 'best_model_pipeline.pkl'))
kidney_scaler = safe_load(os.path.join(MODEL_DIR_KIDNEY, 'scaler_candidate_features.pkl'))
kidney_kmeans = safe_load(os.path.join(MODEL_DIR_KIDNEY, 'kmeans_model.pkl'))

liver_model   = safe_load(os.path.join(MODEL_DIR_LIVER,  'best_liver_model_pipeline.pkl'))
liver_scaler  = safe_load(os.path.join(MODEL_DIR_LIVER,  'scaler_liver_candidate_features.pkl'))
liver_kmeans  = safe_load(os.path.join(MODEL_DIR_LIVER,  'liver_kmeans_model.pkl'))

candidate_feature_names = (
    list(kidney_scaler.feature_names_in_)
    if hasattr(kidney_scaler, 'feature_names_in_') else
    ['GFR','ON_DIALYSIS','INIT_AGE','BMI_TCR','DAYSWAIT_ALLOC','ABO_A','ABO_B','ABO_AB','ABO_O']
)

kidney_outcome_features = [
    'INIT_AGE','BMI_TCR','Kidney_Cluster','WGT_KG_TCR','HGT_CM_TCR','DGN_TCR',
    'AGE_BMI_Interaction','Log_DAYSWAIT_ALLOC'
]
liver_outcome_features  = [
    'INIT_AGE','BMI_TCR','Liver_Cluster','WGT_KG_TCR','HGT_CM_TCR',
    'AGE_BMI_Interaction','Log_DAYSWAIT_CHRON'
]

###############################################
# DATABASE CONNECTION
###############################################

def connect_to_database():
    try:
        return mysql.connector.connect(
            user='root',
            password='Nti2702',
            database='life',
            unix_socket='/opt/lampp/var/mysql/mysql.sock'
        )
    except Error as e:
        print(f"Error connecting to MySQL: {e}")
        return None

###############################################
# HELPER FUNCTIONS
###############################################

def determine_cluster(patient_data, organ_type):
    try:
        if organ_type == 'Kidney':
            chosen_kmeans = kidney_kmeans
            chosen_scaler = kidney_scaler
        else:
            chosen_kmeans = liver_kmeans
            chosen_scaler = liver_scaler

        df = pd.DataFrame([patient_data])
        # ON_DIALYSIS conversion
        if 'ON_DIALYSIS' in df.columns:
            df['ON_DIALYSIS'] = df['ON_DIALYSIS'].astype(int)
        # ensure ABO dummies
        for bt in ['ABO_A','ABO_B','ABO_AB','ABO_O']:
            if bt not in df.columns:
                df[bt] = 0
        df = df[candidate_feature_names]
        X_scaled = chosen_scaler.transform(df)
        return int(chosen_kmeans.predict(X_scaled)[0])
    except Exception as e:
        print(f"Error in determine_cluster: {e}")
        return 0


def get_donor_data(donor_id):
    conn = connect_to_database()
    if not conn: return None
    try:
        cur = conn.cursor(dictionary=True)
        cur.execute("""
            SELECT d.init_age as INIT_AGE, d.bmi_tcr as BMI_TCR, d.dayswait_alloc as DAYSWAIT_ALLOC,
                   d.gfr as GFR, d.on_dialysis as ON_DIALYSIS, d.blood_type as BLOOD_TYPE,
                   d.wgt_kg_tcr as WGT_KG_TCR, d.hgt_cm_tcr as HGT_CM_TCR, d.dgn_tcr as DGN_TCR,
                   d.organ_type as ORGAN_TYPE
            FROM donors d WHERE d.id = %s
        """, (donor_id,))
        return cur.fetchone()
    finally:
        conn.close()


def get_recipient_data(recipient_id=None):
    conn = connect_to_database()
    if not conn: return None
    try:
        cur = conn.cursor(dictionary=True)
        if recipient_id:
            cur.execute("""
                SELECT r.id, r.patient_code, r.init_age as INIT_AGE, r.bmi_tcr as BMI_TCR,
                       r.dayswait_alloc as DAYSWAIT_ALLOC, r.gfr as GFR, r.on_dialysis as ON_DIALYSIS,
                       r.blood_type as BLOOD_TYPE, r.wgt_kg_tcr as WGT_KG_TCR, r.hgt_cm_tcr as HGT_CM_TCR,
                       r.dgn_tcr as DGN_TCR, r.organ_type as ORGAN_TYPE,
                       u.name, u.email
                FROM recipients r JOIN users u ON r.user_id = u.id WHERE r.id = %s
            """, (recipient_id,))
            return cur.fetchone()
        else:
            cur.execute("""
                SELECT r.id, r.patient_code, r.init_age as INIT_AGE, r.bmi_tcr as BMI_TCR,
                       r.dayswait_alloc as DAYSWAIT_ALLOC, r.gfr as GFR, r.on_dialysis as ON_DIALYSIS,
                       r.blood_type as BLOOD_TYPE, r.wgt_kg_tcr as WGT_KG_TCR, r.hgt_cm_tcr as HGT_CM_TCR,
                       r.dgn_tcr as DGN_TCR, r.organ_type as ORGAN_TYPE,
                       u.name, u.email
                FROM recipients r JOIN users u ON r.user_id = u.id
            """
            )
            return cur.fetchall()
    finally:
        conn.close()


def format_patient_data_for_clustering(patient_data):
    data = {
        'INIT_AGE': float(patient_data['INIT_AGE']),
        'BMI_TCR': float(patient_data['BMI_TCR']),
        'DAYSWAIT_ALLOC': float(patient_data['DAYSWAIT_ALLOC']),
        'GFR': float(patient_data['GFR']),
        'ON_DIALYSIS': 1 if patient_data['ON_DIALYSIS'] else 0
    }
    for bt in ['A','B','AB','O']:
        data[f'ABO_{bt}'] = 1 if patient_data.get('BLOOD_TYPE') == bt else 0
    return data


def format_data_for_prediction(donor_data, recipient_data):
    organ = donor_data.get('ORGAN_TYPE', 'Kidney')
    if organ == 'Kidney':
        donor_fmt = format_patient_data_for_clustering(donor_data)
        kc = determine_cluster(donor_fmt, 'Kidney')
        rec = recipient_data
        return {
            'INIT_AGE': float(rec['INIT_AGE']),
            'BMI_TCR': float(rec['BMI_TCR']),
            'Kidney_Cluster': kc,
            'WGT_KG_TCR': float(rec['WGT_KG_TCR']),
            'HGT_CM_TCR': float(rec['HGT_CM_TCR']),
            'DGN_TCR': float(rec['DGN_TCR']),
            'AGE_BMI_Interaction': float(rec['INIT_AGE'])*float(rec['BMI_TCR']),
            'Log_DAYSWAIT_ALLOC': float(np.log(float(rec['DAYSWAIT_ALLOC'])+1))
        }
    else:
        donor_fmt = format_patient_data_for_clustering(donor_data)
        lc = determine_cluster(donor_fmt, 'Liver')
        rec = recipient_data
        return {
            'INIT_AGE': float(rec['INIT_AGE']),
            'BMI_TCR': float(rec['BMI_TCR']),
            'Liver_Cluster': lc,
            'WGT_KG_TCR': float(rec['WGT_KG_TCR']),
            'HGT_CM_TCR': float(rec['HGT_CM_TCR']),
            'AGE_BMI_Interaction': float(rec['INIT_AGE'])*float(rec['BMI_TCR']),
            'Log_DAYSWAIT_CHRON': float(np.log(float(rec['DAYSWAIT_ALLOC'])+1))
        }


def find_matches(donor_data, recipients_data, n_matches=5):
    try:
        donors = format_patient_data_for_clustering(donor_data)
        organ = donor_data.get('ORGAN_TYPE', 'Kidney')
        # prepare candidates
        cands = []
        for r in recipients_data:
            if r.get('ORGAN_TYPE') and r['ORGAN_TYPE'] != organ:
                continue
            fmt = format_patient_data_for_clustering(r)
            cands.append({'orig': r, 'fmt': fmt})
        if not cands:
            return []

        # build matrices
        feat_names = candidate_feature_names
        donor_vec = np.array([[donors.get(f,0) for f in feat_names]])
        recip_mat = np.array([[c['fmt'].get(f,0) for f in feat_names] for c in cands])
        scaler = kidney_scaler if organ=='Kidney' else liver_scaler
        d_scaled = scaler.transform(donor_vec)
        r_scaled = scaler.transform(recip_mat)

        # neighbor search
        nn = NearestNeighbors(n_neighbors=min(n_matches, len(r_scaled))).fit(r_scaled)
        dists, idxs = nn.kneighbors(d_scaled)
        d0 = dists[0]
        max_d, min_d = float(d0.max()), float(d0.min())
        rng = max_d - min_d if (max_d-min_d)>1e-6 else 1e-6

        out = []
        for i, ridx in enumerate(idxs[0]):
            rec = cands[ridx]['orig']
            score = round(100.0*(max_d - float(d0[i]))/rng,1)
            out.append({
                'id': rec['id'],
                'patient_code': rec.get('patient_code'),
                'name': rec.get('name'),
                'email': rec.get('email'),
                'distance': float(d0[i]),
                'compatibility_score': score,
                'data': rec
            })
        return out
    except Exception as e:
        print(f"Error in finding matches: {e}")
        return []

@app.route('/api/find_matches', methods=['POST'])
def api_find_matches():
    data = request.json
    donor_id = data.get('donor_id')
    if not donor_id:
        return jsonify({'error':'Missing donor_id'}),400
    d = get_donor_data(donor_id)
    if not d:
        return jsonify({'error':f'Donor {donor_id} not found'}),404
    recs = get_recipient_data()
    if not recs:
        return jsonify({'error':'No recipients'}),404
    return jsonify({'matches': find_matches(d, recs)})

@app.route('/api/predict_success', methods=['POST'])
def api_predict_success():
    data = request.json
    did, rid = data.get('donor_id'), data.get('recipient_id')
    if not did or not rid:
        return jsonify({'error':'Missing donor_id or recipient_id'}),400
    d = get_donor_data(did)
    r = get_recipient_data(rid)
    if not d or not r:
        return jsonify({'error':'Donor or recipient not found'}),404
    # validation
    missing = [f for f in ['WGT_KG_TCR','HGT_CM_TCR','DAYSWAIT_ALLOC'] if r.get(f) is None]
    if missing:
        return jsonify({'error': f'Missing field(s): {", ".join(missing)}'}),400
    inp = format_data_for_prediction(d, r)
    organ = d.get('ORGAN_TYPE','Kidney')
    feats = kidney_outcome_features if organ=='Kidney' else liver_outcome_features
    model = kidney_model if organ=='Kidney' else liver_model
    X = pd.DataFrame([inp])
    for c in feats:
        X[c] = X.get(c,0.0)
    X = X[feats]
    if model is None:
        return jsonify({'error':'Model not loaded'}),500
    pred = int(model.predict(X)[0])
    prob = round(float(model.predict_proba(X)[0][1])*100,2)
    return jsonify({
        'prediction': 'Transplant Success' if pred==1 else 'Transplant Failure',
        'probability': prob,
        'is_success': pred==1,
        'input_data': inp
    })

@app.route('/api/confirm_match', methods=['POST'])
def api_confirm_match():
    data = request.json
    don, rec = data.get('donor_id'), data.get('recipient_id')
    score, status = data.get('match_score',0.0), data.get('status','matched')
    if not don or not rec:
        return jsonify({'error':'Missing donor_id or recipient_id'}),400
    conn = connect_to_database()
    cur = conn.cursor()
    cur.execute("INSERT INTO matches (donor_id,recipient_id,match_score,status,created_at) VALUES (%s,%s,%s,%s,NOW())",
                (don,rec,score,status))
    conn.commit()
    mid = cur.lastrowid
    cur.close(); conn.close()
    # blockchain call omitted for brevity
    return jsonify({'match_id':mid})

@app.route('/api/confirm_transplant', methods=['POST'])
def api_confirm_transplant():
    data = request.json
    mid, status, perf = data.get('match_id'), data.get('status','scheduled'), data.get('performed_at')
    if not mid or not status:
        return jsonify({'error':'Missing match_id or status'}),400
    conn = connect_to_database(); cur = conn.cursor()
    cur.execute("SELECT r.hospital_id FROM matches m JOIN recipients r ON r.id=m.recipient_id WHERE m.id=%s",(mid,))
    row = cur.fetchone(); hid = row[0] if row else None
    if not hid:
        return jsonify({'error':'No hospital for this recipient'}),400
    if perf:
        cur.execute("INSERT INTO transplants (match_id,hospital_id,status,performed_at,created_at) VALUES (%s,%s,%s,%s,NOW())",
                    (mid,hid,status,perf))
    else:
        cur.execute("INSERT INTO transplants (match_id,hospital_id,status,performed_at,created_at) VALUES (%s,%s,%s,NOW(),NOW())",
                    (mid,hid,status))
    conn.commit(); tid = cur.lastrowid
    cur.execute("UPDATE matches SET status='transplanted' WHERE id=%s",(mid,)); conn.commit()
    cur.close(); conn.close()
    return jsonify({'transplant_id': tid})

@app.route('/api/health', methods=['GET'])
def health_check():
    models_ok = all([kidney_model,liver_model,kidney_kmeans,liver_kmeans,kidney_scaler,liver_scaler])
    db_up = False
    try:
        conn = connect_to_database();
        if conn and conn.is_connected():
            db_up=True; conn.close()
    except: pass
    return jsonify({'status': 'ok' if models_ok and db_up else 'degraded'})

if __name__ == "__main__":
    app.run(host='127.0.0.1', port=5000, debug=False)
