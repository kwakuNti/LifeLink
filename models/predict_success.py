#!/usr/bin/env python3
"""Advanced transplant success prediction using machine learning models."""

import argparse
import json
import logging
import sys
from pathlib import Path
from typing import Dict, Any

import numpy as np
import pandas as pd
from sklearn.base import BaseEstimator
import joblib

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Configuration
MODEL_PATH = Path('models/best_model_pipeline.pkl')
FEATURE_CONFIG = {
    'base_features': ['init_age', 'bmi_tcr', 'dayswait_alloc', 'kidney_cluster'],
    'additional_features': ['wgt_kg_tcr', 'hgt_cm_tcr', 'dgn_tcr'],
    'interaction_terms': [('donor_init_age', 'donor_bmi_tcr')],
    'transformations': {
        'donor_dayswait_alloc': lambda x: np.log(x + 1)
    }
}
CONFIDENCE_THRESHOLDS = {
    'high': 0.7,
    'medium': 0.5,
    'low': 0.3
}


def load_data(file_path: Path) -> Dict[str, Any]:
    """Load and validate JSON data."""
    try:
        with open(file_path, 'r') as f:
            data = json.load(f)
            
        if not isinstance(data, dict):
            raise ValueError("Invalid data format: Expected dictionary")
            
        return data
    except (json.JSONDecodeError, IOError) as e:
        logger.error(f"Data loading failed: {str(e)}")
        sys.exit(1)


def validate_medical_data(data: Dict[str, Any]) -> bool:
    """Validate medical data structure and values."""
    required_fields = FEATURE_CONFIG['base_features'] + FEATURE_CONFIG['additional_features']
    return all(field in data for field in required_fields)


def create_features(donor: Dict[str, Any], recipient: Dict[str, Any]) -> pd.DataFrame:
    """Create engineered feature set for prediction."""
    # Validate input data
    if not all(validate_medical_data(d) for d in [donor, recipient]):
        raise ValueError("Missing required features in input data")

    # Create combined feature set
    features = {}
    for prefix, source in [('donor_', donor), ('recipient_', recipient)]:
        for feature in FEATURE_CONFIG['base_features'] + FEATURE_CONFIG['additional_features']:
            key = f"{prefix}{feature}"
            value = source.get(feature, 0)  # Default to 0 for missing values
            features[key] = float(value) if value not in [None, 'N/A'] else 0.0

    # Apply transformations
    for feature, transform in FEATURE_CONFIG['transformations'].items():
        if feature in features:
            features[feature] = transform(features[feature])

    # Create interaction terms
    for term1, term2 in FEATURE_CONFIG['interaction_terms']:
        features[f"{term1}_X_{term2}"] = features.get(term1, 0) * features.get(term2, 0)

    # Convert to DataFrame
    df = pd.DataFrame([features])
    
    # Ensure proper typing
    return df.apply(pd.to_numeric, errors='coerce').fillna(0)


def load_model(model_path: Path) -> BaseEstimator:
    """Load and validate trained model."""
    try:
        model = joblib.load(model_path)
        if not hasattr(model, 'predict_proba'):
            raise AttributeError("Loaded model does not support probability predictions")
        return model
    except Exception as e:
        logger.error(f"Model loading failed: {str(e)}")
        sys.exit(1)


def get_confidence_level(probability: float) -> str:
    """Determine confidence level based on thresholds."""
    if probability >= CONFIDENCE_THRESHOLDS['high']:
        return 'high'
    if probability >= CONFIDENCE_THRESHOLDS['medium']:
        return 'medium'
    if probability >= CONFIDENCE_THRESHOLDS['low']:
        return 'low'
    return 'very low'


def main():
    # Parse arguments
    parser = argparse.ArgumentParser(description='Predict transplant success probability')
    parser.add_argument('--donor', type=Path, required=True, help='Donor data path')
    parser.add_argument('--recipient', type=Path, required=True, help='Recipient data path')
    parser.add_argument('--output', type=Path, required=True, help='Output file path')
    args = parser.parse_args()

    try:
        # Load and validate data
        donor_data = load_data(args.donor)
        recipient_data = load_data(args.recipient)

        # Convert boolean fields
        for data in [donor_data, recipient_data]:
            if 'on_dialysis' in data:
                data['on_dialysis'] = 1 if data['on_dialysis'] else 0

        # Create features
        features_df = create_features(donor_data, recipient_data)

        # Load model
        model = load_model(MODEL_PATH)

        # Make prediction
        success_prob = model.predict_proba(features_df)[0, 1]

        # Prepare result
        prediction = {
            'success_probability': round(float(success_prob), 4),
            'success_percentage': round(float(success_prob) * 100, 2),
            'confidence': get_confidence_level(success_prob),
            'features_used': list(features_df.columns),
            'model_version': getattr(model, 'version', 'unknown'),
            'message': f"Predicted success probability: {round(success_prob * 100, 1)}%"
        }

        # Save results
        args.output.parent.mkdir(parents=True, exist_ok=True)
        with open(args.output, 'w') as f:
            json.dump(prediction, f, indent=2)

        logger.info(f"Success prediction completed: {prediction['success_percentage']}%")

    except Exception as e:
        logger.error(f"Prediction failed: {str(e)}")
        sys.exit(1)


if __name__ == '__main__':
    main()