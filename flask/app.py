from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import numpy as np
import pandas as pd
from sklearn.neighbors import NearestNeighbors
import os
import mysql.connector
from mysql.connector import Error
import subprocess  # Add this line

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
import os
import mysql.connector
from mysql.connector import Error

def connect_to_database():
    try:
        # always read your usual 4 from env
        cfg = {
            'user'    : os.environ.get('DB_USER', 'root'),
            'password': os.environ.get('DB_PASS', 'root'),
            'database': os.environ.get('DB_NAME', 'life'),
        }

        # if they gave us a socket, use it; otherwise use TCP host
        db_socket = os.environ.get('DB_SOCKET')
        if db_socket:
            # remove host so connector won't try TCP
            # (not strictly required, but clearer)
            cfg.pop('host', None)
            cfg['unix_socket'] = db_socket
        else:
            cfg['host'] = os.environ.get('DB_HOST', 'localhost')

        return mysql.connector.connect(**cfg)

    except Error as e:
        print(f"Error connecting to MySQL: {e}")
        return None

# def connect_to_database():
#     try:
#         return mysql.connector.connect(
#             user='root',
#             password='Nti2702',
#             database='life',
#             unix_socket='/opt/lampp/var/mysql/mysql.sock'
#         )
#     except Error as e:
#         print(f"Error connecting to MySQL: {e}")
#         return None


###############################################
# HELPER FUNCTIONS
###############################################
def determine_cluster(patient_data, organ_type):
    """
    Uses the appropriate kmeans and scaler based on the organ type to determine the cluster.
    """
    try:
        if organ_type == 'Kidney':
            chosen_kmeans = kidney_kmeans
            chosen_scaler = kidney_scaler
        elif organ_type == 'Liver':
            chosen_kmeans = liver_kmeans
            chosen_scaler = liver_scaler
        else:
            print("Unknown organ type, defaulting to kidney models")
            chosen_kmeans = kidney_kmeans
            chosen_scaler = kidney_scaler

        df = pd.DataFrame([patient_data])
        # Convert ON_DIALYSIS to numeric, if necessary
        if 'ON_DIALYSIS' in df.columns:
            if isinstance(df['ON_DIALYSIS'].iloc[0], str):
                df['ON_DIALYSIS'] = df['ON_DIALYSIS'].map({'Y': 1, 'N': 0})
            else:
                df['ON_DIALYSIS'] = df['ON_DIALYSIS'].apply(lambda x: int(x))
        # Ensure ABO columns exist
        abo_columns = [col for col in candidate_feature_names if col.startswith('ABO_')]
        for col in abo_columns:
            if col not in df.columns:
                df[col] = 0
        for col in candidate_feature_names:
            if col not in df.columns:
                df[col] = 0
        df = df[candidate_feature_names]
        X_scaled = chosen_scaler.transform(df)
        cluster = chosen_kmeans.predict(X_scaled)[0]
        print(f"Determined cluster ({organ_type}): {cluster}")
        return int(cluster)
    except Exception as e:
        print(f"Error in determine_cluster: {str(e)}")
        return 0

def get_donor_data(donor_id):
    """Fetch donor data from the database including organ_type"""
    connection = connect_to_database()
    if not connection:
        return None
    try:
        cursor = connection.cursor(dictionary=True)
        query = """
        SELECT 
            d.init_age as INIT_AGE, 
            d.bmi_tcr as BMI_TCR, 
            d.dayswait_alloc as DAYSWAIT_ALLOC,
            d.gfr as GFR, 
            d.on_dialysis as ON_DIALYSIS,
            d.blood_type as BLOOD_TYPE,
            d.wgt_kg_tcr as WGT_KG_TCR,
            d.hgt_cm_tcr as HGT_CM_TCR,
            d.dgn_tcr as DGN_TCR,
            d.organ_type as ORGAN_TYPE
        FROM donors d
        WHERE d.id = %s
        """
        cursor.execute(query, (donor_id,))
        donor_data = cursor.fetchone()
        cursor.close()
        return donor_data
    except Error as e:
        print(f"Error fetching donor data: {e}")
        return None
    finally:
        if connection.is_connected():
            connection.close()

def get_recipient_data(recipient_id=None):
    """Fetch recipient data from the database by joining recipients with users, including organ_type"""
    connection = connect_to_database()
    if not connection:
        return None
    try:
        cursor = connection.cursor(dictionary=True)
        if recipient_id:
            query = """
            SELECT 
                r.id,
                r.patient_code,
                r.init_age as INIT_AGE, 
                r.bmi_tcr as BMI_TCR, 
                r.dayswait_alloc as DAYSWAIT_ALLOC,
                r.gfr as GFR, 
                r.on_dialysis as ON_DIALYSIS,
                r.blood_type as BLOOD_TYPE,
                r.wgt_kg_tcr as WGT_KG_TCR,
                r.hgt_cm_tcr as HGT_CM_TCR,
                r.dgn_tcr as DGN_TCR,
                r.organ_type as ORGAN_TYPE,
                u.name,
                u.email
            FROM recipients r
            JOIN users u ON r.user_id = u.id
            WHERE r.id = %s
            """
            cursor.execute(query, (recipient_id,))
            return cursor.fetchone()
        else:
            query = """
            SELECT 
                r.id,
                r.patient_code,
                r.init_age as INIT_AGE, 
                r.bmi_tcr as BMI_TCR, 
                r.dayswait_alloc as DAYSWAIT_ALLOC,
                r.gfr as GFR, 
                r.on_dialysis as ON_DIALYSIS,
                r.blood_type as BLOOD_TYPE,
                r.wgt_kg_tcr as WGT_KG_TCR,
                r.hgt_cm_tcr as HGT_CM_TCR,
                r.dgn_tcr as DGN_TCR,
                r.organ_type as ORGAN_TYPE,
                u.name,
                u.email
            FROM recipients r
            JOIN users u ON r.user_id = u.id
            """
            cursor.execute(query)
            return cursor.fetchall()
    except Error as e:
        print(f"Error fetching recipient data: {e}")
        return None
    finally:
        if connection.is_connected():
            connection.close()

def format_patient_data_for_clustering(patient_data):
    """
    Formats patient data for clustering by extracting needed features and converting blood type to one-hot encoding.
    """
    formatted_data = {
        'INIT_AGE': float(patient_data['INIT_AGE']),
        'BMI_TCR': float(patient_data['BMI_TCR']),
        'DAYSWAIT_ALLOC': float(patient_data['DAYSWAIT_ALLOC']),
        'GFR': float(patient_data['GFR']),
        'ON_DIALYSIS': 1 if patient_data['ON_DIALYSIS'] else 0
    }
    blood_types = ['A', 'B', 'AB', 'O']
    for bt in blood_types:
        formatted_data[f'ABO_{bt}'] = 0
    if patient_data['BLOOD_TYPE'] in blood_types:
        formatted_data[f"ABO_{patient_data['BLOOD_TYPE']}"] = 1
    return formatted_data

def format_data_for_prediction(donor_data, recipient_data):
    """
    Format donor and recipient data for the prediction model.
    For Kidney donors, use features: Kidney_Cluster, DGN_TCR, Log_DAYSWAIT_ALLOC.
    For Liver donors, use features: Liver_Cluster, Log_DAYSWAIT_CHRON.
    """
    organ_type = donor_data.get('ORGAN_TYPE', 'Kidney')
    if organ_type == 'Kidney':
        donor_formatted = format_patient_data_for_clustering(donor_data)
        kidney_cluster = determine_cluster(donor_formatted, 'Kidney')
        input_dict = {
            'INIT_AGE': float(recipient_data['INIT_AGE']),
            'BMI_TCR': float(recipient_data['BMI_TCR']),
            'Kidney_Cluster': kidney_cluster,
            'WGT_KG_TCR': float(recipient_data['WGT_KG_TCR']) if recipient_data['WGT_KG_TCR'] else 0.0,
            'HGT_CM_TCR': float(recipient_data['HGT_CM_TCR']) if recipient_data['HGT_CM_TCR'] else 0.0,
            'DGN_TCR': float(recipient_data['DGN_TCR']) if recipient_data['DGN_TCR'] else 0.0,
        }
        input_dict['AGE_BMI_Interaction'] = input_dict['INIT_AGE'] * input_dict['BMI_TCR']
        input_dict['Log_DAYSWAIT_ALLOC'] = float(np.log(float(recipient_data['DAYSWAIT_ALLOC']) + 1))
    else:  # Liver
        donor_formatted = format_patient_data_for_clustering(donor_data)
        liver_cluster = determine_cluster(donor_formatted, 'Liver')
        input_dict = {
            'INIT_AGE': float(recipient_data['INIT_AGE']),
            'BMI_TCR': float(recipient_data['BMI_TCR']),
            'Liver_Cluster': liver_cluster,
            'WGT_KG_TCR': float(recipient_data['WGT_KG_TCR']) if recipient_data['WGT_KG_TCR'] else 0.0,
            'HGT_CM_TCR': float(recipient_data['HGT_CM_TCR']) if recipient_data['HGT_CM_TCR'] else 0.0,
        }
        input_dict['AGE_BMI_Interaction'] = input_dict['INIT_AGE'] * input_dict['BMI_TCR']
        input_dict['Log_DAYSWAIT_CHRON'] = float(np.log(float(recipient_data['DAYSWAIT_ALLOC']) + 1))
    return input_dict

def find_matches(donor_data, recipients_data, n_matches=5):
    """
    Find the top n_matches recipients for the given donor based on feature similarity.
    Only include recipients whose organ_type matches the donor's organ type (if defined).
    """
    try:
        donor_formatted = format_patient_data_for_clustering(donor_data)
        donor_organ = donor_data.get('ORGAN_TYPE', 'Kidney')
        
        recipients_formatted = []
        for recipient in recipients_data:
            # If recipient has organ_type defined and it doesn't match, skip candidate
            if 'ORGAN_TYPE' in recipient and recipient['ORGAN_TYPE'] is not None:
                if recipient['ORGAN_TYPE'] != donor_organ:
                    continue
            recipient_formatted = format_patient_data_for_clustering(recipient)
            recipients_formatted.append({
                'id': recipient['id'],
                'name': recipient['name'],
                'email': recipient['email'],
                'data': recipient_formatted,
                'original': recipient
            })
        if not recipients_formatted:
            return []
        donor_row = []
        for feature in candidate_feature_names:
            if feature in donor_formatted:
                donor_row.append(donor_formatted[feature] if donor_formatted[feature] != 'Y' else 1)
            else:
                donor_row.append(0)
        donor_features = np.array([donor_row])
        recipient_features = []
        recipient_ids = []
        for recipient in recipients_formatted:
            recipient_row = []
            for feature in candidate_feature_names:
                if feature in recipient['data']:
                    recipient_row.append(recipient['data'][feature] if recipient['data'][feature] != 'Y' else 1)
                else:
                    recipient_row.append(0)
            recipient_features.append(recipient_row)
            recipient_ids.append(recipient['id'])
        recipient_features = np.array(recipient_features)
        # Choose appropriate scaler based on donor organ type
        if donor_organ == 'Kidney':
            chosen_scaler = kidney_scaler
        elif donor_organ == 'Liver':
            chosen_scaler = liver_scaler
        else:
            chosen_scaler = kidney_scaler
            
        if chosen_scaler:
            donor_features_scaled = chosen_scaler.transform(donor_features)
            recipient_features_scaled = chosen_scaler.transform(recipient_features)
        else:
            donor_features_scaled = donor_features
            recipient_features_scaled = recipient_features
        nn_model = NearestNeighbors(n_neighbors=min(n_matches, len(recipient_features_scaled)), algorithm='auto')
        nn_model.fit(recipient_features_scaled)
        distances, indices = nn_model.kneighbors(donor_features_scaled)
        matches = []
        for i, idx in enumerate(indices[0]):
            # Directly access the recipient using the index
            recipient_data = recipients_formatted[idx]
            
            matches.append({
                'id': recipient_data['id'],
                'patient_code': recipient_data['original']['patient_code'],
                'name': recipient_data['name'],
                'email': recipient_data['email'],
                'distance': float(distances[0][i]),
                'compatibility_score': 100 * (1 - float(distances[0][i]) / max(1, float(distances[0].max()))),
                'data': recipient_data['original']
            })
            
        return matches
    except Exception as e:
        print(f"Error in finding matches: {e}")
        return []


# --- after your existing find_matches(...) function ---

def find_matches_two_stage(donor_data, recipients_data, n_matches=5, α=0.6):
    """
    Two‐stage matching:
      1) Get top 3×n_matches by similarity.
      2) Predict transplant success for each and combine scores.
    α = weight on success_probability (rest on compatibility_score).
    """
    # Stage 1: broad similarity filter
    first_stage = find_matches(donor_data, recipients_data, n_matches=n_matches * 3)

    # Stage 2: predict and re‐rank
    for m in first_stage:
        # prepare prediction input
        input_dict = format_data_for_prediction(donor_data, m['data'])
        organ_type = donor_data.get('ORGAN_TYPE', 'Kidney')

        if organ_type == 'Kidney':
            feats, model = kidney_outcome_features, kidney_model
        else:
            feats, model = liver_outcome_features, liver_model

        df_in = pd.DataFrame([input_dict])
        # ensure all outcome features exist
        for col in feats:
            if col not in df_in:
                df_in[col] = 0.0
        df_in = df_in[feats]

        # predict probability
        prob = model.predict_proba(df_in)[0][1] * 100
        m['success_probability'] = float(prob)

        # combined score
        m['combined_score'] = α * m['success_probability'] + (1 - α) * m['compatibility_score']

    # sort and return top n_matches
    first_stage.sort(key=lambda x: x['combined_score'], reverse=True)
    return first_stage[:n_matches]

def is_blood_compatible(donor_type, recipient_type):
    """Check if donor blood type is compatible with recipient blood type."""
    compatibility = {
        'O': ['O', 'A', 'B', 'AB'],
        'A': ['A', 'AB'],
        'B': ['B', 'AB'],
        'AB': ['AB']
    }
    return recipient_type in compatibility.get(donor_type, [])

###############################################
# API ENDPOINTS
###############################################

@app.route('/api/find_matches', methods=['POST'])
def api_find_matches():
    """
    Find compatible recipient matches for a given donor using two‐stage matching.
    Expected JSON input: { "donor_id": 123 }
    """
    data = request.json or {}
    donor_id = data.get('donor_id')
    if not donor_id:
        return jsonify({'error': 'Missing donor_id parameter'}), 400

    donor = get_donor_data(donor_id)
    if not donor:
        return jsonify({'error': f'Donor with ID {donor_id} not found'}), 404

    recipients = get_recipient_data()
    if not recipients:
        return jsonify({'error': 'No recipients found'}), 404

    # use two‐stage matching now
    matches = find_matches_two_stage(donor, recipients, n_matches=5)

    return jsonify({'matches': matches})

@app.route('/api/predict_success', methods=['POST'])
def api_predict_success():
    """
    Predict transplant success for a donor-recipient pair.
    Expected JSON input:
      { "donor_id": 123, "recipient_id": 456 }
    """
    try:
        data = request.json
        donor_id = data.get('donor_id')
        recipient_id = data.get('recipient_id')
        if not donor_id or not recipient_id:
            return jsonify({'error': 'Missing donor_id or recipient_id parameter'}), 400
        donor_data = get_donor_data(donor_id)
        recipient_data = get_recipient_data(recipient_id)
        if not donor_data:
            return jsonify({'error': f'Donor with ID {donor_id} not found'}), 404
        if not recipient_data:
            return jsonify({'error': f'Recipient with ID {recipient_id} not found'}), 404
        input_dict = format_data_for_prediction(donor_data, recipient_data)
        organ_type = donor_data.get('ORGAN_TYPE', 'Kidney')
        if organ_type == 'Kidney':
            outcome_features = kidney_outcome_features
            chosen_model = kidney_model
        else:
            outcome_features = liver_outcome_features
            chosen_model = liver_model
        input_features = pd.DataFrame([input_dict])
        for col in outcome_features:
            if col not in input_features.columns:
                input_features[col] = 0.0
        input_features = input_features[outcome_features]
        if chosen_model is None:
            return jsonify({
                'error': 'Model not loaded',
                'prediction': None,
                'probability': None
            }), 500
        prediction = chosen_model.predict(input_features)[0]
        prediction_prob = chosen_model.predict_proba(input_features)[0][1]
        prediction = int(prediction)
        prediction_percentage = round(float(prediction_prob) * 100, 2)
        outcome_text = "Transplant Success" if prediction == 1 else "Transplant Failure"
        return jsonify({
            'prediction': outcome_text,
            'probability': prediction_percentage,
            'is_success': bool(prediction == 1),
            'input_data': input_dict
        })
    except Exception as e:
        print(f"Error in api_predict_success: {e}")
        return jsonify({'error': str(e)}), 500

@app.route('/api/confirm_match', methods=['POST'])
def api_confirm_match():
    """
    Confirm a match between donor and recipient.
    Expected JSON input:
      { "donor_id": 123, "recipient_id": 456, "match_score": 85.5, "status": "matched" }
    """
    try:
        data = request.json
        donor_id = data.get('donor_id')
        recipient_id = data.get('recipient_id')
        match_score = data.get('match_score', 0.0)
        status = data.get('status', 'matched')
        if not donor_id or not recipient_id:
            return jsonify({'error': 'Missing donor_id or recipient_id parameter'}), 400
        connection = connect_to_database()
        if not connection:
            return jsonify({'error': 'Database connection error'}), 500
        cursor = connection.cursor()
        query = """
            INSERT INTO matches (donor_id, recipient_id, match_score, status, created_at)
            VALUES (%s, %s, %s, %s, NOW())
        """
        cursor.execute(query, (donor_id, recipient_id, match_score, status))
        connection.commit()
        match_id = cursor.lastrowid
        cursor.close()
        connection.close()
        # Prepare to invoke the chaincode function CreateMatch on the blockchain
        # Construct a unique match identifier for the blockchain (e.g., "match_{match_id}")
        blockchain_match_id = f"match_{match_id}"

        # Define the path to your bash script (adjust the path if necessary)
        bash_script = "../blockchain/invoke-chaincode.sh"
        # We'll call the function "CreateMatch" with:
        #   blockchain_match_id, donor_id, recipient_id, match_score, status
        cmd = [
            bash_script,
            "CreateMatch",
            blockchain_match_id,
            str(donor_id),
            str(recipient_id),
            str(match_score),
            status
        ]

        # Log and execute the command
        try:
            output = subprocess.check_output(cmd, stderr=subprocess.STDOUT)
            # Optionally, you can decode and log the output:
            blockchain_response = output.decode()
            print("Blockchain invoke output:", blockchain_response)
        except subprocess.CalledProcessError as e:
            print("Blockchain invoke failed:", e.output.decode())
            return jsonify({'error': 'Match recorded in SQL, but failed to record on blockchain', 'blockchain_error': e.output.decode()}), 500

        return jsonify({'message': 'Match confirmed on blockchain', 'match_id': match_id})
    except Exception as e:
        return jsonify({'error': str(e)}), 500

# =====  UPDATED ENDPOINT  =====
@app.route('/api/confirm_transplant', methods=['POST'])
def api_confirm_transplant():
    """
    Confirm (or schedule) a transplant for a given match.
    Expected JSON: { "match_id": 789, "status": "completed" }
    `performed_at` may be supplied; hospital_id is looked-up.
    """
    try:
        data        = request.json
        match_id    = data.get('match_id')
        status      = data.get('status', 'scheduled')
        performed_at= data.get('performed_at')      # optional

        if not match_id or not status:
            return jsonify({'error': 'Missing match_id or status'}), 400

        conn = connect_to_database()
        if not conn:
            return jsonify({'error': 'Database connection error'}), 500
        cur = conn.cursor()

        # 1️⃣  fetch the hospital that registered this recipient
        cur.execute("""
            SELECT r.hospital_id
            FROM matches   m
            JOIN recipients r ON r.id = m.recipient_id
            WHERE m.id = %s
        """, (match_id,))
        row = cur.fetchone()
        hospital_id = row[0] if row else None
        if hospital_id is None:
            return jsonify({'error': 'No hospital registered for this recipient'}), 400

        # 2️⃣  insert transplant record
        if performed_at:
            cur.execute("""
                INSERT INTO transplants (match_id,hospital_id,status,performed_at,created_at)
                VALUES (%s,%s,%s,%s,NOW())
            """, (match_id, hospital_id, status, performed_at))
        else:
            cur.execute("""
                INSERT INTO transplants (match_id,hospital_id,status,performed_at,created_at)
                VALUES (%s,%s,%s,NOW(),NOW())
            """, (match_id, hospital_id, status))
        conn.commit()
        transplant_id = cur.lastrowid

        # 3️⃣  set match to transplanted
        cur.execute("UPDATE matches SET status='transplanted' WHERE id=%s", (match_id,))
        conn.commit()

        cur.close()
        conn.close()
        return jsonify({'message':'Transplant confirmed','transplant_id':transplant_id})

    except Exception as e:
        return jsonify({'error': str(e)}), 500
    
@app.route('/api/determine_liver_cluster', methods=['POST'])
def api_determine_liver_cluster():
    """Determine liver cluster based on donor medical data"""
    try:
        data = request.json
        # Map form field names to model expected names
        medical_data = {
            'INIT_AGE': float(data.get('init_age')),
            'HGT_CM_TCR': float(data.get('hgt_cm_tcr')),
            'WGT_KG_TCR': float(data.get('wgt_kg_tcr')),
            'BMI_TCR': float(data.get('bmi_tcr')),
            'GFR': float(data.get('gfr')),
            'ON_DIALYSIS': 1 if data.get('on_dialysis') == 'Y' else 0,
            'BLOOD_TYPE': data.get('blood_type'),
            'DAYSWAIT_ALLOC': 0  # Default value as per your form
        }
        
        cluster = determine_cluster(medical_data, 'Liver')
        return jsonify({'cluster': cluster})
        
    except Exception as e:
        print(f"Error determining cluster: {e}")
        return jsonify({'error': str(e)}), 500


@app.route('/api/determine_kidney_cluster', methods=['POST'])
def api_determine_kidney_cluster():
    """Determine liver cluster based on donor medical data"""
    try:
        data = request.json
        # Map form field names to model expected names
        medical_data = {
            'INIT_AGE': float(data.get('init_age')),
            'HGT_CM_TCR': float(data.get('hgt_cm_tcr')),
            'WGT_KG_TCR': float(data.get('wgt_kg_tcr')),
            'BMI_TCR': float(data.get('bmi_tcr')),
            'GFR': float(data.get('gfr')),
            'ON_DIALYSIS': 1 if data.get('on_dialysis') == 'Y' else 0,
            'BLOOD_TYPE': data.get('blood_type'),
            'DAYSWAIT_ALLOC': 0  # Default value as per your form
        }
        
        cluster = determine_cluster(medical_data, 'Kidney')
        return jsonify({'cluster': cluster})
        
    except Exception as e:
        print(f"Error determining cluster: {e}")
        return jsonify({'error': str(e)}), 500
@app.route('/api/health', methods=['GET'])
def health_check():
    # check models
    models_ok = all([
        kidney_model is not None,
        liver_model is not None,
        kidney_kmeans is not None,
        liver_kmeans is not None,
        kidney_scaler is not None and liver_scaler is not None
    ])
    # check DB
    db_status = 'down'
    try:
        conn = connect_to_database()
        if conn and conn.is_connected():
            cursor = conn.cursor()
            cursor.execute("SELECT 1")
            cursor.fetchone()
            db_status = 'up'
            cursor.close()
            conn.close()
    except Exception:
        db_status = 'down'

    return jsonify({
        'status': 'ok' if models_ok and db_status=='up' else 'degraded',
        'models_loaded': models_ok,
        'db': db_status
    })

# if __name__ == '__main__':
#     app.run(debug=True, host='', port=5000)
if __name__ == "__main__":
    # Just serve HTTP on localhost:5000 — Nginx will handle SSL
    app.run(host='127.0.0.1', port=5000, debug=False)


