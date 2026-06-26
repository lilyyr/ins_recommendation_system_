import json
import sys
import numpy as np
from datetime import datetime
from sklearn.ensemble import RandomForestClassifier
import joblib
import mysql.connector
from typing import List, Dict, Tuple
import os

def connect_db():
    return mysql.connector.connect(
        host=os.environ.get('DB_HOST', 'localhost'),
        port=int(os.environ.get('DB_PORT', 3306)),
        user=os.environ.get('DB_USERNAME', 'root'),
        password=os.environ.get('DB_PASSWORD', ''),
        database=os.environ.get('DB_DATABASE', 'rec_ins_cbr')
    )

def get_feature_weights():
    conn = connect_db()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT feature_name, weight FROM weights ORDER BY id")
    weights = cursor.fetchall()
    cursor.close()
    conn.close()
    return [{'feature': w['feature_name'], 'weight': float(w['weight'])} for w in weights]

# Preprocessing
def calculate_age(dob: str) -> int:
    birth_year = int(dob.split('-')[0])
    current_year = datetime.now().year
    return current_year - birth_year

def calculate_bmi(height: float, weight: float) -> float:
    height_m = height / 100
    return weight / (height_m ** 2)

def calculate_health_risk(data: Dict) -> float:
    """Calculate health risk score (0-25)"""
    risk = 0.0

    # Health factors with weights
    risk_factors = {
        'weight_change_last_year': 1.0,
        'smoked_last_year': 2.0,
        'hospitalization_last_5_years': 3.0,
        'lab_tests_last_5_years': 1.0,
        'accident_poisoning_last_5_years': 2.5,
        'has_disability': 4.0,
        'has_serious_illness': 5.0,
        'receiving_treatment': 3.0,
        'family_medical_history': 1.5,
        'is_pregnant': 0.5
    }

    for factor, weight in risk_factors.items():
        if data.get(factor, False):
            risk += weight

    # BMI risk
    bmi = data['bmi']
    if bmi < 18.5:
        risk += 2.0
    elif bmi >= 30:
        risk += 3.0
    elif bmi >= 25:
        risk += 1.0

    return risk

def normalize_value(value: float, min_val: float, max_val: float) -> float:
    if max_val == min_val:
        return 0.0
    return (value - min_val) / (max_val - min_val)

def preprocess_case(data: Dict) -> Dict:
    # Policy insured
    age = calculate_age(data['dob'])
    bmi = calculate_bmi(data['height'], data['weight'])
    data['age'] = age
    data['bmi'] = bmi
    health_risk = calculate_health_risk(data)

    conn = connect_db()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT risk_score FROM occupations WHERE id = %s", (data['occupation_id'],))
    occupation = cursor.fetchone()
    occupation_risk = float(occupation['risk_score'])
    cursor.close()
    conn.close()

    age_norm = normalize_value(age, 1, 70)
    gender_encoded = 1 if data['gender'] == 'male' else 0
    marital_encoded = 1 if data['marital_status'] == 'married' else 0
    occupation_risk_norm = normalize_value(occupation_risk, 1, 3)
    dependents_norm = normalize_value(data['num_dependents'], 0, 10)
    bmi_norm = normalize_value(bmi, 15, 40)
    ins_period_norm = normalize_value(data['insurance_period'], 1, 50)
    health_risk_norm = normalize_value(health_risk, 0, 25)
    health_ins_encoded = 1 if data['has_existing_health_insurance'] else 0
    high_risk_hobby_encoded = 1 if data['high_risk_hobby'] else 0
    nominal_received_norm = normalize_value(data['nominal_received'], 0, 5000000000)

    relationship_map = {
        'orang tua kandung': 1.0,
        'suami/istri': 0.9,
        'adik/kakak kandung': 0.8,
        'nenek/kakek kandung': 0.7,
        'anak kandung': 0.6,
        'cucu/cicit': 0.5,
        'lainnya': 0.3
    }

    beneficiary_encoded = relationship_map.get(data['beneficiary_relationship'], 0.3)

    overseas_encoded = 1 if data['overseas_medical_plans'] else 0

    coverage = data['coverage_regions']
    coverage_asia_exc = 1 if 'asia_exc_hkg_sg_jpn' in coverage else 0
    coverage_hkg_sg_jpn = 1 if 'hkg_sg_jpn' in coverage else 0
    coverage_europe = 1 if 'europe' in coverage else 0
    coverage_north_america = 1 if 'north_america' in coverage else 0
    coverage_south_america = 1 if 'south_america' in coverage else 0
    coverage_africa = 1 if 'africa' in coverage else 0
    coverage_oceania = 1 if 'oceania' in coverage else 0

    goals = data['financial_goals']
    goal_life = 1 if 'life' in goals else 0
    goal_health = 1 if 'health' in goals else 0
    goal_retirement = 1 if 'retirement' in goals else 0
    goal_education = 1 if 'education' in goals else 0
    goal_critical = 1 if 'critical_illness' in goals else 0
    goal_income = 1 if 'income_protection' in goals else 0
    goal_savings = 1 if 'savings' in goals else 0
    goal_accidents = 1 if 'accidents' in goals else 0

    # Policy holder
    incomeMap = {
            'below_50m': 25000000,
            '50m_100m': 75000000,
            '100m_300m': 200000000,
            '300m_500m': 400000000,
            '500m_1b': 750000000,
            'above_1b': 1500000000
    }

    holder_income_range = data['holder_income_range']
    holder_income = incomeMap.get(holder_income_range, 0)
    holder_income_norm = normalize_value(holder_income, 25000000, 1500000000)


    holder_relationship_map = {
        'diri sendiri': 1.0,
        'suami/istri': 0.9,
        'orang tua kandung': 0.8,
        'adik/kakak kandung': 0.7,
        'nenek/kakek kandung': 0.6,
        'anak kandung': 0.5,
        'cucu/cicit': 0.4,
        'lainnya': 0.2,
    }

    holder_relationship = data['holder_relationship_to_insured']
    holder_relationship_encoded = holder_relationship_map.get(holder_relationship, 0.2)

    feature_vector = [
        age_norm, gender_encoded, marital_encoded, occupation_risk_norm,
        dependents_norm, float(bmi_norm), ins_period_norm, health_risk_norm,
        overseas_encoded, health_ins_encoded, high_risk_hobby_encoded,
        float(nominal_received_norm), beneficiary_encoded,
        coverage_asia_exc, coverage_hkg_sg_jpn, coverage_europe,
        coverage_north_america, coverage_south_america, coverage_africa, coverage_oceania,
        goal_life, goal_health, goal_retirement, goal_education,
        goal_critical, goal_income, goal_savings, goal_accidents,
        holder_income_norm, holder_relationship_encoded
    ]

    return {
        'feature_vector': feature_vector,
        'health_risk_score': health_risk
    }

def load_historical_cases() -> List[Dict]:
    conn = connect_db()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("""
        SELECT c.*, p.name as product_name, cu.*, ph.income_range as holder_income_range
        FROM cases c
        JOIN customers cu ON c.customer_id = cu.id
        JOIN products p ON c.product_id = p.id
        JOIN policy_holders ph ON c.policy_holder_id = ph.id
        ORDER BY c.id
    """)
    cases = cursor.fetchall()
    cursor.close()
    conn.close()

    results = []
    for case in cases:
        input_data = {
            'gender': case['gender'],
            'dob': str(case['dob']),
            'marital_status': case['marital_status'],
            'occupation_id': case['occupation_id'],
            'num_dependents': case['num_dependents'],

            'holder_income_range': case['holder_income_range'],
            'holder_relationship_to_insured': case['holder_relationship_to_insured'],

            'insurance_period': case['insurance_period'],
            'has_existing_health_insurance': case['has_existing_health_insurance'],
            'high_risk_hobby': case['high_risk_hobby'],
            'nominal_received': case['nominal_received'],
            'beneficiary_relationship': case['beneficiary_relationship'],
            'overseas_medical_plans': case['overseas_medical_plans'],
            'coverage_regions': json.loads(case['coverage_regions'] or '[]'),
            'financial_goals': json.loads(case['financial_goals']),
            'height': case['height'],
            'weight': case['weight'],
            'weight_change_last_year': case['weight_change_last_year'],
            'smoked_last_year': case['smoked_last_year'],
            'hospitalization_last_5_years': case['hospitalization_last_5_years'],
            'lab_tests_last_5_years': case['lab_tests_last_5_years'],
            'accident_poisoning_last_5_years': case['accident_poisoning_last_5_years'],
            'has_disability': case['has_disability'],
            'has_serious_illness': case['has_serious_illness'],
            'receiving_treatment': case['receiving_treatment'],
            'family_medical_history': case['family_medical_history'],
            'is_pregnant':case['is_pregnant'],
        }

        preprocessed = preprocess_case(input_data)

        results.append({
            'id':           case['id'],
            'product_id':   case['product_id'],
            'product_name': case['product_name'],
            'feature_vector': preprocessed['feature_vector'],
        })

    return results

# Similarity Algorithms
def euclidean(new_vector: np.ndarray, historical_vectors: np.ndarray, case_info, k: int = 5) -> List[Dict]:
    similarities = []
    results = []

    for i, hist_vector in enumerate(historical_vectors):
        diff_squared = (new_vector - hist_vector) ** 2
        sum_squared = np.sum(diff_squared)
        distance = np.sqrt(sum_squared)
        similarity = 1 / (1 + distance)

        similarities.append({
            'case_id': case_info[i]['id'],
            'product_id': case_info[i]['product_id'],
            'product_name': case_info[i]['product_name'],
            'similarity': float(similarity),
            'distance': float(distance),
            'sum_squared_diff': float(sum_squared),
            'feature_differences': [float(d) for d in diff_squared],
            'historical_vector': case_info[i]['feature_vector']
        })

    similarities.sort(key=lambda x: x['similarity'], reverse=True)

    results = []
    for case in similarities:
        product_id = case['product_id']
        if product_id not in [r['product_id'] for r in results]:
            results.append(case)

    return results[:k]

def weighted_euclidean(new_vector: np.ndarray, historical_vectors: np.ndarray, case_info, k: int = 5) -> List[Dict]:
    get_weights = get_feature_weights()
    weights = np.array([weight['weight'] for weight in get_weights])

    similarities = []

    for i, hist_vector in enumerate(historical_vectors):
        diff = new_vector - hist_vector
        weighted_diff_squared = weights * (diff ** 2)
        sum_weighted_squared = np.sum(weighted_diff_squared)
        distance = np.sqrt(sum_weighted_squared)
        similarity = 1 / (1 + distance)

        similarities.append({
            'case_id': case_info[i]['id'],
            'product_id': case_info[i]['product_id'],
            'product_name': case_info[i]['product_name'],
            'similarity': float(similarity),
            'distance': float(distance),
            'sum_weighted_squared': float(sum_weighted_squared),
            'weighted_differences': [float(d) for d in weighted_diff_squared],
            'historical_vector': case_info[i]['feature_vector'],
    })

    similarities.sort(key=lambda x: x['similarity'], reverse=True)

    results = []
    for case in similarities:
        product_id = case['product_id']
        if product_id not in [r['product_id'] for r in results]:
            results.append(case)

    return results[:k]

def random_forest_proximity(new_vector: np.ndarray, historical_vectors: np.ndarray, case_info, k: int = 5) -> List[Dict]:
    script_dir = os.path.dirname(__file__)
    model_path = os.path.join(script_dir, 'models', 'rf_model.pkl')
    cache_path = os.path.join(script_dir, 'models', 'leaf_cache.json')

    if not os.path.exists(model_path):
        print("Warning: RF model not trained")
        return []

    # Load model and cache
    rf_model = joblib.load(model_path)
    with open(cache_path, 'r') as f:
        leaf_cache = json.load(f)

    # New case leaf nodes
    new_leaves = rf_model.apply(new_vector.reshape(1, -1))[0]

    leaf_assignments_dict = leaf_cache['leaf_assignments']
    n_trees = rf_model.n_estimators

    similarities = []

    for i, hist_vector in enumerate(historical_vectors):
        case_id_str = str(case_info[i]['id'])

        if case_id_str in leaf_assignments_dict:
            hist_leaves = np.array(leaf_assignments_dict[case_id_str])
        else:
            hist_leaves = rf_model.apply(hist_vector.reshape(1, -1))[0]

        matches = np.sum(new_leaves == hist_leaves)
        proximity = matches / n_trees

        tree_matches = [
            {
                'tree_id': j + 1,
                'new_leaf': int(new_leaves[j]),
                'hist_leaf': int(hist_leaves[j]),
                'match': bool(new_leaves[j] == hist_leaves[j])
            }
            for j in range(len(new_leaves[:10]))
        ]

        similarities.append({
            'case_id': case_info[i]['id'],
            'product_id': case_info[i]['product_id'],
            'product_name': case_info[i]['product_name'],
            'similarity': float(proximity),
            'matches': int(matches),
            'total_trees': n_trees,
            'new_case_leaves': [int(l) for l in new_leaves[:10]],
            'historical_case_leaves': [int(l) for l in hist_leaves[:10]],
            'tree_by_tree_matches': tree_matches
        })

    similarities.sort(key=lambda x: x['similarity'], reverse=True)

    results = []
    for case in similarities:
        product_id = case['product_id']
        if product_id not in [r['product_id'] for r in results]:
            results.append(case)

    return results[:k]

# Aggregate
def aggregate_results(euclidean: List[Dict], weighted: List[Dict], rf: List[Dict]) -> List[Dict]:
    product_scores = {}

    for result in euclidean:
        pid = result['product_id']
        if pid not in product_scores:
            product_scores[pid] = {
                'product_id': pid,
                'product_name': result['product_name'],
                'euclidean_scores': [],
                'weighted_scores': [],
                'rf_scores': []
            }
        product_scores[pid]['euclidean_scores'].append(result['similarity'])

    for result in weighted:
        pid = result['product_id']
        if pid not in product_scores:
            product_scores[pid] = {
                'product_id': pid,
                'product_name': result['product_name'],
                'euclidean_scores': [],
                'weighted_scores': [],
                'rf_scores': []
            }
        product_scores[pid]['weighted_scores'].append(result['similarity'])

    for result in rf:
        pid = result['product_id']
        if pid not in product_scores:
            product_scores[pid] = {
                'product_id': pid,
                'product_name': result['product_name'],
                'euclidean_scores': [],
                'weighted_scores': [],
                'rf_scores': []
            }
        product_scores[pid]['rf_scores'].append(result['similarity'])

    # Calculate aggregates
    recommendations = []

    for pid, scores in product_scores.items():
        euc_avg = np.mean(scores['euclidean_scores']) if scores['euclidean_scores'] else 0
        weighted_avg = np.mean(scores['weighted_scores']) if scores['weighted_scores'] else 0
        rf_avg = np.mean(scores['rf_scores']) if scores['rf_scores'] else 0

        total = euc_avg + weighted_avg + rf_avg
        aggregate = total / 3

        recommendations.append({
            'product_id': pid,
            'product_name': scores['product_name'],
            'euclidean_score': float(euc_avg),
            'weighted_euclidean_score': float(weighted_avg),
            'random_forest_score': float(rf_avg),
            'aggregate_score': float(aggregate),
            'match_percentage': round(aggregate * 100, 2)
        })

    recommendations.sort(key=lambda x: x['aggregate_score'], reverse=True)

    for i, rec in enumerate(recommendations, 1):
        rec['rank'] = i

    return recommendations

def main(input_file: str, output_file: str):
    """Main CBR system"""
    print("=" * 60)
    print("CBR System - Processing")
    print("=" * 60)

    # Load input
    with open(input_file, 'r') as f:
        input_data = json.load(f)

    preprocessed_new_case = preprocess_case(input_data)

    historical_cases = load_historical_cases()
    print(f"    {len(historical_cases)} cases loaded")

    # Extract feature vectors
    historical_vectors = []
    case_info = []
    for case in historical_cases:
        historical_vectors.append(case['feature_vector'])
        case_info.append({
            'id': case['id'],
            'product_id': case['product_id'],
            'product_name': case['product_name'],
            'feature_vector': case['feature_vector']
        })

    historical_vectors = np.array(historical_vectors)
    new_case_vector = np.array(preprocessed_new_case['feature_vector'])

    start = datetime.now()
    euclidean_results = euclidean(new_case_vector, historical_vectors, case_info)
    euc_time = (datetime.now() - start).total_seconds() * 1000

    start = datetime.now()
    weighted_results = weighted_euclidean(new_case_vector, historical_vectors, case_info)
    weighted_time = (datetime.now() - start).total_seconds() * 1000

    start = datetime.now()
    rf_results = random_forest_proximity(new_case_vector, historical_vectors, case_info)
    rf_time = (datetime.now() - start).total_seconds() * 1000

    recommendations = aggregate_results(euclidean_results, weighted_results, rf_results)

    total_time = euc_time + weighted_time + rf_time

    output = {
        'success': True,
        'input_data': input_data,
        'feature_vector': preprocessed_new_case['feature_vector'],
        'health_risk_score': preprocessed_new_case['health_risk_score'],
        'recommendations': recommendations,
        'execution_time': {
            'euclidean': round(euc_time, 2),
            'weighted': round(weighted_time, 2),
            'random_forest': round(rf_time, 2),
            'total': round(total_time, 2)
        },
        'algorithm_details': {
            'euclidean': {
                'top_5_matches': euclidean_results,
            },
            'weighted_euclidean': {
                'top_5_matches': weighted_results,
                'weights_used': get_feature_weights()
            },
            'random_forest': {
                'top_5_matches': rf_results,
            }
        }
    }

    with open(output_file, 'w') as f:
        json.dump(output, f, indent=2)

    print(f"\nComplete! Top: {recommendations[0]['product_name']} ({recommendations[0]['match_percentage']}%)")
    print("=" * 60)

if __name__ == '__main__':
    if len(sys.argv) != 3:
        print("Usage: python cbr_system.py <input.json> <output.json>")
        sys.exit(1)

    main(sys.argv[1], sys.argv[2])
