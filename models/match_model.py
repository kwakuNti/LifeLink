#!/usr/bin/env python3
"""Advanced donor-recipient matching using machine learning models."""

import argparse
import json
import logging
import sys
from pathlib import Path
from typing import Dict, List, Union

import numpy as np
import pandas as pd
from sklearn.exceptions import NotFittedError
from sklearn.neighbors import NearestNeighbors
from sklearn.preprocessing import StandardScaler

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

# Configuration
FEATURE_COLUMNS = [
    'init_age', 'bmi_tcr', 'dayswait_alloc', 'kidney_cluster',
    'dgn_tcr', 'wgt_kg_tcr', 'hgt_cm_tcr', 'gfr', 'on_dialysis'
]
SCALER_PATH = Path('models/scaler_candidate_features.pkl')
MAX_DISTANCE = 5.0  # Based on training data analysis


def load_data(file_path: Path) -> Union[Dict, List[Dict]]:
    """Load and validate JSON data."""
    try:
        with open(file_path, 'r') as f:
            data = json.load(f)
            
        if not data:
            raise ValueError("Empty data file")
            
        return data
    except (json.JSONDecodeError, IOError) as e:
        logger.error(f"Data loading failed: {str(e)}")
        sys.exit(1)


def preprocess_features(data: Union[Dict, List[Dict]], features: List[str]) -> pd.DataFrame:
    """Preprocess and validate feature data."""
    df = pd.DataFrame(data if isinstance(data, list) else [data])
    
    # Convert boolean fields
    if 'on_dialysis' in df.columns:
        df['on_dialysis'] = df['on_dialysis'].astype(int)
    
    # Ensure feature presence
    missing_features = set(features) - set(df.columns)
    if missing_features:
        logger.warning(f"Missing features: {missing_features}. Imputing with NaN")
        df = df.reindex(columns=features)
    
    # Type conversion and imputation
    numeric_df = df[features].apply(pd.to_numeric, errors='coerce')
    return numeric_df.fillna(numeric_df.median()).clip(lower=0)


def calculate_match_score(distance: float) -> float:
    """Convert distance to normalized match score."""
    return max(0.0, 1.0 - (distance / MAX_DISTANCE))


def main():
    # Parse arguments
    parser = argparse.ArgumentParser(description='Find compatible organ matches')
    parser.add_argument('--donor', type=Path, required=True, help='Donor data path')
    parser.add_argument('--recipients', type=Path, required=True, help='Recipients data path')
    parser.add_argument('--output', type=Path, required=True, help='Output file path')
    args = parser.parse_args()

    try:
        # Load and validate models
        scaler = joblib.load(SCALER_PATH)
        if not hasattr(scaler, 'transform'):
            raise NotFittedError("Scaler not properly initialized")

        # Load and preprocess data
        donor_data = load_data(args.donor)
        recipients_data = load_data(args.recipients)

        donor_features = preprocess_features(donor_data, FEATURE_COLUMNS)
        recipient_features = preprocess_features(recipients_data, FEATURE_COLUMNS)

        # Validate data integrity
        if donor_features.empty or recipient_features.empty:
            raise ValueError("Invalid feature data after preprocessing")

        # Transform features
        donor_scaled = scaler.transform(donor_features)
        recipients_scaled = scaler.transform(recipient_features)

        # Find nearest neighbors
        nn = NearestNeighbors(n_neighbors=min(5, len(recipients_scaled)), algorithm='ball_tree')
        nn.fit(recipients_scaled)
        distances, indices = nn.kneighbors(donor_scaled)

        # Generate matches
        matches = []
        for i, (distance, idx) in enumerate(zip(distances[0], indices[0])):
            recipient = recipients_data[idx]
            matches.append({
                'recipient_id': recipient['id'],
                'patient_code': recipient.get('patient_code', f'PT-{idx}'),
                'hospital': recipient.get('hospital_name', 'Unknown Hospital'),
                'location': recipient.get('hospital_city', 'Unknown Location'),
                'match_score': round(calculate_match_score(distance), 3),
                'medical_data': {
                    'age': recipient.get('init_age', 'N/A'),
                    'blood_type': recipient.get('blood_type', 'N/A'),
                    'dialysis_status': 'Yes' if recipient.get('on_dialysis', 0) == 1 else 'No',
                    'wait_time': recipient.get('dayswait_alloc', 'N/A')
                }
            })

        # Save results
        args.output.parent.mkdir(parents=True, exist_ok=True)
        with open(args.output, 'w') as f:
            json.dump(matches, f, indent=2)

        logger.info(f"Successfully generated {len(matches)} matches")

    except Exception as e:
        logger.error(f"Matching failed: {str(e)}")
        sys.exit(1)


if __name__ == '__main__':
    main()