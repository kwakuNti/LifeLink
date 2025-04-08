from flask import Flask, request, jsonify
import joblib
import numpy as np
import pandas as pd
from sklearn.neighbors import NearestNeighbors
import os
import mysql.connector
from mysql.connector import Error

app = Flask(__name__)

# Set MODEL_DIR to point to Lifelink/models/kidney relative to this file's directory
MODEL_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'models', 'kidney')

###############################################
# LOAD MODELS & SCALERS
###############################################
try:
    # Outcome model pipeline
    model = joblib.load(os.path.join(MODEL_DIR, 'best_model_pipeline.pkl'))
    # Scaler used for candidate features in clustering
    scaler_candidate = joblib.load(os.path.join(MODEL_DIR, 'scaler_candidate_features.pkl'))
    # KMeans model for clustering
    kmeans_model = joblib.load(os.path.join(MODEL_DIR, 'kmeans_model.pkl'))
    
    # Get column names from scaler if available
    if hasattr(scaler_candidate, 'feature_names_in_'):
        candidate_feature_names = list(scaler_candidate.feature_names_in_)
        print("Candidate feature names from scaler:", candidate_feature_names)
    else:
        candidate_feature_names = [
            'GFR', 'ON_DIALYSIS', 'INIT_AGE', 'BMI_TCR', 'DAYSWAIT_ALLOC', 
            'ABO_A', 'ABO_B', 'ABO_AB', 'ABO_O'
        ]
    
    # Define expected features for outcome model
    outcome_model_features = [
        'INIT_AGE', 'BMI_TCR', 'Kidney_Cluster', 'WGT_KG_TCR', 
        'HGT_CM_TCR', 'DGN_TCR', 'AGE_BMI_Interaction', 'Log_DAYSWAIT_ALLOC'
    ]
    
except Exception as e:
    print(f"Error loading models: {e}")
    model = None
    kmeans_model = None
    scaler_candidate = None
    candidate_feature_names = [
        'GFR', 'ON_DIALYSIS', 'INIT_AGE', 'BMI_TCR', 'DAYSWAIT_ALLOC', 
        'ABO_A', 'ABO_B', 'ABO_AB', 'ABO_O'
    ]
    outcome_model_features = [
        'INIT_AGE', 'BMI_TCR', 'Kidney_Cluster', 'WGT_KG_TCR', 
        'HGT_CM_TCR', 'DGN_TCR', 'AGE_BMI_Interaction', 'Log_DAYSWAIT_ALLOC'
    ]

###############################################
# DATABASE CONNECTION
###############################################
def connect_to_database():
    try:
        connection = mysql.connector.connect(
            host="localhost",
            user="root",
            password="root",
            database="life"
        )
        return connection
    except Error as e:
        print(f"Error connecting to MySQL: {e}")
        return None

###############################################
# HELPER FUNCTIONS
###############################################
def determine_kidney_cluster(patient_data):
    """
    Uses the kmeans_model to determine the kidney cluster based on the patient's data.
    """
    try:
        if kmeans_model is None or scaler_candidate is None:
            print("Models not loaded, returning default cluster 0")
            return 0
            
        # Convert data to DataFrame
        df = pd.DataFrame([patient_data])
        
        # Convert ON_DIALYSIS to numeric (if needed)
        if 'ON_DIALYSIS' in df.columns:
            if isinstance(df['ON_DIALYSIS'].iloc[0], str):
                df['ON_DIALYSIS'] = df['ON_DIALYSIS'].map({'Y': 1, 'N': 0})
            else:
                # Ensure it's a native int
                df['ON_DIALYSIS'] = df['ON_DIALYSIS'].apply(lambda x: int(x))
        
        # Handle ABO one-hot encoding
        abo_columns = [col for col in candidate_feature_names if col.startswith('ABO_')]
        for col in abo_columns:
            if col not in df.columns:
                df[col] = 0
        
        # Ensure DataFrame has all expected columns from scaler
        for col in candidate_feature_names:
            if col not in df.columns:
                df[col] = 0
        
        # Keep only columns expected by the scaler
        df = df[candidate_feature_names]
        
        # Scale features using the same scaler used in training
        X_scaled = scaler_candidate.transform(df)
        
        # Predict cluster
        cluster = kmeans_model.predict(X_scaled)[0]
        print(f"Determined cluster: {cluster}")
        return int(cluster)
        
    except Exception as e:
        print(f"Error in determine_kidney_cluster: {str(e)}")
        return 0

def get_donor_data(donor_id):
    """Fetch donor data from the database"""
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
            d.dgn_tcr as DGN_TCR
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
    """Fetch recipient data from the database by joining recipients with users"""
    connection = connect_to_database()
    if not connection:
        return None

    try:
        cursor = connection.cursor(dictionary=True)
        if recipient_id:
            query = """
            SELECT 
                r.id,
                r.init_age as INIT_AGE, 
                r.bmi_tcr as BMI_TCR, 
                r.dayswait_alloc as DAYSWAIT_ALLOC,
                r.gfr as GFR, 
                r.on_dialysis as ON_DIALYSIS,
                r.blood_type as BLOOD_TYPE,
                r.wgt_kg_tcr as WGT_KG_TCR,
                r.hgt_cm_tcr as HGT_CM_TCR,
                r.dgn_tcr as DGN_TCR,
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
                r.init_age as INIT_AGE, 
                r.bmi_tcr as BMI_TCR, 
                r.dayswait_alloc as DAYSWAIT_ALLOC,
                r.gfr as GFR, 
                r.on_dialysis as ON_DIALYSIS,
                r.blood_type as BLOOD_TYPE,
                r.wgt_kg_tcr as WGT_KG_TCR,
                r.hgt_cm_tcr as HGT_CM_TCR,
                r.dgn_tcr as DGN_TCR,
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
    Formats patient data for clustering by extracting needed features
    and converting blood type to one-hot encoding.
    """
    formatted_data = {
        'INIT_AGE': float(patient_data['INIT_AGE']),
        'BMI_TCR': float(patient_data['BMI_TCR']),
        'DAYSWAIT_ALLOC': float(patient_data['DAYSWAIT_ALLOC']),
        'GFR': float(patient_data['GFR']),
        # Convert ON_DIALYSIS to a native int (0 or 1)
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
    Explicitly convert computed values to native Python types.
    """
    donor_formatted = format_patient_data_for_clustering(donor_data)
    donor_cluster = determine_kidney_cluster(donor_formatted)
    input_dict = {
        'INIT_AGE': float(recipient_data['INIT_AGE']),
        'BMI_TCR': float(recipient_data['BMI_TCR']),
        'Kidney_Cluster': donor_cluster,
        'WGT_KG_TCR': float(recipient_data['WGT_KG_TCR']) if recipient_data['WGT_KG_TCR'] else 0.0,
        'HGT_CM_TCR': float(recipient_data['HGT_CM_TCR']) if recipient_data['HGT_CM_TCR'] else 0.0,
        'DGN_TCR': float(recipient_data['DGN_TCR']) if recipient_data['DGN_TCR'] else 0.0,
    }
    input_dict['AGE_BMI_Interaction'] = float(input_dict['INIT_AGE'] * input_dict['BMI_TCR'])
    input_dict['Log_DAYSWAIT_ALLOC'] = float(np.log(float(recipient_data['DAYSWAIT_ALLOC']) + 1))
    return input_dict

def find_matches(donor_data, recipients_data, n_matches=5):
    """
    Find the top n_matches recipients for the given donor based on feature similarity.
    """
    try:
        donor_formatted = format_patient_data_for_clustering(donor_data)
        recipients_formatted = []
        for recipient in recipients_data:
            if is_blood_compatible(donor_data['BLOOD_TYPE'], recipient['BLOOD_TYPE']):
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
        donor_features = []
        recipient_features = []
        recipient_ids = []
        donor_row = []
        for feature in candidate_feature_names:
            if feature in donor_formatted:
                donor_row.append(donor_formatted[feature] if donor_formatted[feature] != 'Y' else 1)
            else:
                donor_row.append(0)
        donor_features = np.array([donor_row])
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
        if scaler_candidate:
            donor_features_scaled = scaler_candidate.transform(donor_features)
            recipient_features_scaled = scaler_candidate.transform(recipient_features)
        else:
            donor_features_scaled = donor_features
            recipient_features_scaled = recipient_features
        nn_model = NearestNeighbors(n_neighbors=min(n_matches, len(recipient_features_scaled)), algorithm='auto')
        nn_model.fit(recipient_features_scaled)
        distances, indices = nn_model.kneighbors(donor_features_scaled)
        matches = []
        for i, idx in enumerate(indices[0]):
            recipient_id = recipient_ids[idx]
            recipient_data = next((r for r in recipients_formatted if r['id'] == recipient_id), None)
            if recipient_data:
                matches.append({
                    'id': recipient_id,
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
    Find compatible recipient matches for a given donor.
    Expected JSON input:
      { "donor_id": 123 }
    """
    try:
        data = request.json
        donor_id = data.get('donor_id')
        if not donor_id:
            return jsonify({'error': 'Missing donor_id parameter'}), 400
        donor_data = get_donor_data(donor_id)
        if not donor_data:
            return jsonify({'error': f'Donor with ID {donor_id} not found'}), 404
        recipients_data = get_recipient_data()
        if not recipients_data:
            return jsonify({'error': 'No recipients found'}), 404
        matches = find_matches(donor_data, recipients_data)
        return jsonify({'matches': matches})
    except Exception as e:
        print(f"Error in api_find_matches: {e}")
        return jsonify({'error': str(e)}), 500

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
        input_features = pd.DataFrame([input_dict])
        for col in outcome_model_features:
            if col not in input_features.columns:
                input_features[col] = 0.0
        input_features = input_features[outcome_model_features]
        if model is None:
            return jsonify({
                'error': 'Model not loaded',
                'prediction': None,
                'probability': None
            }), 500
        prediction = model.predict(input_features)[0]
        prediction_prob = model.predict_proba(input_features)[0][1]
        # Convert NumPy types to native Python types
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

@app.route('/api/health', methods=['GET'])
def health_check():
    """Simple health check endpoint."""
    status = {
        'status': 'ok',
        'models_loaded': {
            'outcome_model': model is not None,
            'kmeans_model': kmeans_model is not None,
            'scaler': scaler_candidate is not None
        }
    }
    return jsonify(status)

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
