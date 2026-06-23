import sys
import json
import time
import os
import mysql.connector
import numpy as np
from datetime import datetime
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from cbr_system import (
    connect_db,
    preprocess_case,
    load_historical_cases,
    euclidean,
    weighted_euclidean,
    random_forest_proximity,
    aggregate_results
)

# train_test split
splits = [
    ('80_20', 0.2),
    ('70_30', 0.3)
]

rf_parameter_list = [{
        'n_estimators': n,
        'max_depth': depth,
        'max_features': features,
        'min_samples_leaf': msl,
        }
    for n in [100, 200, 300]
    for depth in [5, 10, None]
    for features in ['sqrt', 'log2', None]
    for msl in [1, 2, 3]
]

def split_data(all_cases, test_ratio):
    labels = [case['product_id'] for case in all_cases]
    index = list(range(len(all_cases)))

    training_ids, testing_ids = train_test_split(index, test_size=test_ratio, stratify=labels, random_state=42)

    training_cases = [all_cases[i] for i in training_ids]
    testing_cases = [all_cases[i] for i in testing_ids]

    return training_cases, testing_cases

def euclidean_or_weighted(training_cases, testing_cases, algorithm_chosen, all_product_ids):
    print(f"\nTesting {algorithm_chosen} algorithm...")

    training_vectors = np.array([case['feature_vector'] for case in training_cases])

    predictions = []
    time_taken = []

    for case in testing_cases:
        testing_vector = np.array(case['feature_vector'])

        start_time = time.time()
        if algorithm_chosen == 'euclidean':
            similarities = euclidean(testing_vector, training_vectors, training_cases)
        else: # weighted_euclidean
            similarities = weighted_euclidean(testing_vector, training_vectors, training_cases)
        end_time = time.time()
        time_used = (end_time - start_time) * 1000
        time_taken.append(time_used)

        top_ids = [r['product_id'] for r in similarities]
        top_cases = [r['case_id'] for r in similarities]

        predictions.append({
            'test_case_id': case['id'],
            'correct_product_id': case['product_id'],
            'correct_product_name': case['product_name'],
            'top_predictions_ids': top_ids,
            'top_predictions_cases': top_cases,
            'time_taken': time_used
        })

    metrics = calculate_metrics(predictions, all_product_ids)
    metrics['avg_time_taken'] = np.mean(time_taken)
    metrics['total_time_taken'] = np.sum(time_taken)

    results = {
        'algorithm_name': algorithm_chosen,
        'predictions': predictions,
        'metrics': metrics,
        'training_size': len(training_cases),
        'testing_size': len(testing_cases)
    }

    return results

def random_forest(training_cases, testing_cases, all_product_ids, rf_parameter):
    print(f"\nTesting random forest algorithm...")

    training_vectors = np.array([case['feature_vector'] for case in training_cases])
    training_labels = np.array([case['product_id'] for case in training_cases])

    rf = RandomForestClassifier(
        n_estimators=rf_parameter['n_estimators'],
        max_depth=rf_parameter['max_depth'],
        max_features=rf_parameter['max_features'],
        min_samples_leaf=rf_parameter['min_samples_leaf'],
        random_state=42)
    rf.fit(training_vectors, training_labels)

    leaf_assignments_all = rf.apply(training_vectors)
    leaf_cache = {str(case['id']): leaf_assignments_all[i] for i, case in enumerate(training_cases)}

    predictions = []
    time_taken = []

    for testing_case in testing_cases:
        testing_vector = np.array(testing_case['feature_vector'])

        start_time = time.time()

        testing_leaves = rf.apply(testing_vector.reshape(1, -1))[0]
        n_trees = rf.n_estimators

        similarities = []
        for i, training_case in enumerate(training_cases):
            training_leaves = np.array(leaf_cache[str(training_case['id'])])
            matches = np.sum(testing_leaves == training_leaves)
            proximity = matches / n_trees

            similarities.append({
                'case_id': training_case['id'],
                'product_id': training_case['product_id'],
                'product_name': training_case['product_name'],
                'similarity': proximity
            })

        similarities.sort(key=lambda x: x['similarity'], reverse=True)

        results = []
        for similar_case in similarities:
            product_id = similar_case['product_id']
            if product_id not in [r['product_id'] for r in results]:
                results.append(similar_case)

        end_time = time.time()
        time_used = (end_time - start_time) * 1000
        time_taken.append(time_used)

        top_ids = [r['product_id'] for r in results]
        top_cases = [r['case_id'] for r in results]

        predictions.append({
            'test_case_id': testing_case['id'],
            'correct_product_id': testing_case['product_id'],
            'correct_product_name': testing_case['product_name'],
            'top_predictions_ids': top_ids,
            'top_predictions_cases': top_cases,
            'time_taken': time_used
        })

    metrics = calculate_metrics(predictions, all_product_ids)
    metrics['avg_time_taken'] = np.mean(time_taken)
    metrics['total_time_taken'] = np.sum(time_taken)

    results = {
        'algorithm_name': 'random_forest',
        'rf_parameter': rf_parameter,
        'predictions': predictions,
        'metrics': metrics,
        'training_size': len(training_cases),
        'testing_size': len(testing_cases)
    }

    return results

def calculate_metrics(predictions, all_product_ids):
    tp = 0  # Correct product in top-1
    fp = 0  # Wrong product in top-1
    tn = 0  # Irrelevant products correctly excluded (not in top 1)
    fn = 0  # Correct product not in top-1
    reciprocal_ranks = []
    hit_rate_at_3 = []
    hit_rate_at_5 = []
    rank_list = []

    for pred in predictions:
        correct_id = pred['correct_product_id']
        top_ids = pred['top_predictions_ids']

        # Confusion Matrix
        if top_ids[0] == correct_id:
            tp += 1
        else:
            fp += 1

        incorrect_products_ids = [product_id for product_id in all_product_ids if product_id != correct_id]
        for product_id in incorrect_products_ids:
            if product_id != top_ids[0]:
                tn += 1
            else:
                fn += 1

        # MRR
        if correct_id in top_ids:
            rank = top_ids.index(correct_id) + 1
            reciprocal_ranks.append(1.0 / rank)
        else:
            reciprocal_ranks.append(0.0)

        #h Hit Rate @3
        if correct_id in top_ids[:3]:
            hit_rate_at_3.append(1.0)
        else:
            hit_rate_at_3.append(0.0)

        # Hit Rate @5
        if correct_id in top_ids[:5]:
            hit_rate_at_5.append(1.0)
        else:
            hit_rate_at_5.append(0.0)

        # Rank List
        if correct_id in top_ids:
            rank_list.append(top_ids.index(correct_id) + 1)
        else:
            rank_list.append(len(all_product_ids) + 1)

    print(f"\nConfusion Matrix:")
    print(f"  True Positives (TP):  {tp}")
    print(f"  False Positives (FP): {fp}")
    print(f"  True Negatives (TN):  {tn}")
    print(f"  False Negatives (FN): {fn}")

    precision = tp / (tp + fp)
    recall = tp / (tp + fn)
    f1_score = 2 * (precision * recall) / (precision + recall)

    results = {
        'tp': tp,
        'fp': fp,
        'tn': tn,
        'fn': fn,
        'f1_score': f1_score,
        'mrr': np.mean(reciprocal_ranks),
        'hr_at_3': np.mean(hit_rate_at_3),
        'hr_at_5': np.mean(hit_rate_at_5),
        'mean_rank': np.mean(rank_list)
    }

    return results

def save_result(cursor, conn, result):
    m       = result['metrics']
    rf_p    = result.get('rf_parameter', {})
    total_cases = result['training_size'] + result['testing_size']

    cursor.execute("""
        INSERT INTO algorithm_test_results (
            algorithm_name,
            split_ratio,
            train_size,
            test_size,
            total_test_cases,
            n_estimators,
            max_depth,
            max_features,
            min_samples_leaf,
            true_positives,
            false_positives,
            true_negatives,
            false_negatives,
            f1_score,
            mrr,
            hr_at_3,
            hr_at_5,
            mean_rank,
            avg_time_taken,
            total_time_taken,
            detailed_results,
            created_at,
            updated_at
        ) VALUES (
            %s,%s,%s,%s,%s,%s,%s,%s,%s,
            %s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,
            NOW(), NOW()
        )
    """, (
        result['algorithm_name'],
        result['data_split_ratio'],
        result['training_size'],
        result['testing_size'],
        total_cases,
        rf_p.get('n_estimators'),
        rf_p.get('max_depth'),
        rf_p.get('max_features'),
        rf_p.get('min_samples_leaf'),
        m['tp'], m['fp'], m['tn'], m['fn'],
        m['f1_score'], m['mrr'], m['hr_at_3'], m['hr_at_5'], m['mean_rank'],
        m['avg_time_taken'], m['total_time_taken'],
        json.dumps(result['predictions']),
    ))
    conn.commit()

def print_metrics(label, r):
    print(f"\n{label} Metrics:")
    print(f"    F1-Score    : {r['f1_score']*100:6.2f}%")
    print(f"    MRR         : {r['mrr']*100:6.2f}%")
    print(f"    HR@3        : {r['hr_at_3']*100:6.2f}%")
    print(f"    HR@5        : {r['hr_at_5']*100:6.2f}%")
    print(f"    Mean Rank   : {r['mean_rank']:6.2f}")
    print(f"    Avg time    : {r['avg_time_taken']:.2f} ms/case")
    print(f"    (TP={r['tp']} FP={r['fp']} TN={r['tn']} FN={r['fn']})")

def main():
    print("="*60)
    print("CBR ALGORITHM TESTING SUITE")
    print("="*60)

    conn = connect_db()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT id FROM products")
    all_product_ids = [product['id'] for product in cursor.fetchall()]
    print(f"{len(all_product_ids)} products in catalog")

    all_cases = load_historical_cases()
    print(f"Total cases amount: {len(all_cases)}")

    all_results = []

    for split_label, test_ratio in splits:
        print(f"\n{'─'*70}")
        print(f"SPLIT: {split_label}  "
            f"(train={(1-test_ratio)*100}% | "
            f"test={test_ratio*100}%)")
        print(f"{'─'*70}")

        training_cases, testing_cases = split_data(all_cases, test_ratio)
        print(f"  Train: {len(training_cases)} cases | Test: {len(testing_cases)} cases")

        # Euclidean
        algorithm_chosen = 'euclidean'
        result = euclidean_or_weighted(training_cases, testing_cases, algorithm_chosen, all_product_ids)
        result['data_split_ratio'] = split_label
        print_metrics('Euclidean', result['metrics'])
        save_result(cursor, conn, result)
        all_results.append(result)

        # Weighted Euclidean
        algorithm_chosen = 'weighted_euclidean'
        result = euclidean_or_weighted(training_cases, testing_cases, algorithm_chosen, all_product_ids)
        result['data_split_ratio'] = split_label
        print_metrics('Weighted Euclidean', result['metrics'])
        save_result(cursor, conn, result)
        all_results.append(result)

        # Random Forest Proximity
        for i, rf_parameter in enumerate(rf_parameter_list, 1):
            print(f"\n[RF {i}/{len(rf_parameter_list)}]:")
            print(f"  n_estimators: {rf_parameter['n_estimators']}")
            print(f"  max_depth: {rf_parameter['max_depth']}")
            print(f"  max_features: {rf_parameter['max_features']}")
            print(f"  min_samples_leaf: {rf_parameter['min_samples_leaf']}")

            try:
                result = random_forest(training_cases, testing_cases, all_product_ids, rf_parameter)
                result['data_split_ratio'] = split_label
                print_metrics('Random Forest', result['metrics'])
                save_result(cursor, conn, result)
                all_results.append(result)
            except Exception as e:
                print(f" ERROR: {e}")



    cursor.close()
    conn.close()

    print("\nAll results saved to algorithm_test_results table.")

if __name__ == '__main__':
    main()
