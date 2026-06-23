@extends('layouts.app')

@section('title', 'CBR Process Visualization')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <div id="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-yellow-600"></div>
        <p class="mt-4 text-gray-600">Loading CBR process details...</p>
    </div>

    <div id="process-content" class="hidden space-y-6"></div>
</div>

<script>
const apiToken      = document.querySelector('meta[name="api-token"]').content;
const consultationId = {{ $consultationId }};

// Feature names
const FEATURE_NAMES = [
    'Age (normalized)',
    'Gender (encoded)',
    'Marital Status (encoded)',
    'Occupation Risk (normalized)',
    'Dependents (normalized)',
    'BMI (normalized)',
    'Insurance Period (normalized)',
    'Health Risk (normalized)',
    'Overseas (encoded)',
    'Existing Health Insurance (encoded)',
    'High Risk Hobby (encoded)',
    'Nominal Received (normalized)',
    'Beneficiary Relationship (encoded)',
    'Region: Asia (exc HKG/SG/JPN)',
    'Region: HKG / SG / JPN',
    'Region: Europe',
    'Region: North America',
    'Region: South America',
    'Region: Africa',
    'Region: Oceania',
    'Goal: Life',
    'Goal: Health',
    'Goal: Retirement',
    'Goal: Education',
    'Goal: Critical Illness',
    'Goal: Income Protection',
    'Goal: Savings',
    'Goal: Accidents',
    'Holder Income (normalized)',
    'Holder Relationship (encoded)',

];

function fmt(v, d = 4) {
    return (v == null ? 'N/A' : parseFloat(v).toFixed(d));
}

function parseField(raw) {
    if (Array.isArray(raw) || (typeof raw === 'object' && raw !== null)) return raw;
    try { return JSON.parse(raw); } catch { return raw; }
}

async function loadProcess() {
    try {
        const res  = await fetch(`/api/recommendations/${consultationId}`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${apiToken}` }
        });
        if (!res.ok) throw new Error('Failed to load process details');
        const result = await res.json();
        renderProcess(result.data);
    } catch (e) {
        document.getElementById('loading').innerHTML = `
            <div class="text-red-600 text-center py-12">
                <p class="text-lg font-semibold">Error loading process</p>
                <p class="text-sm mt-1">${e.message}</p>
            </div>`;
    }
}

function renderProcess(data) {
    document.getElementById('loading').classList.add('hidden');

    const fv = parseField(data.feature_vector);
    const ad = parseField(data.algorithm_details);

    if (!ad) {
        document.getElementById('process-content').innerHTML = `
            <div class="text-center py-12 bg-yellow-50 border border-yellow-200 rounded">
                <p class="text-yellow-800 text-lg">No algorithm details available for this consultation.</p>
                <a href="/consultations/${data.id}" class="text-yellow-600 hover:text-yellow-700 mt-4 inline-block">
                    ← Back to Consultation
                </a>
            </div>`;
        document.getElementById('process-content').classList.remove('hidden');
        return;
    }

    const euc = ad.euclidean || {};
    const weuc = ad.weighted_euclidean || {};
    const rf = ad.random_forest || {};
    const eucTop = euc.top_5_matches || [];
    const weucTop = weuc.top_5_matches || [];
    const rfTop = rf.top_5_matches || [];

    const eScore = parseFloat(data.euclidean_score) || 0;
    const wScore = parseFloat(data.weighted_euclidean_score) || 0;
    const rScore = parseFloat(data.random_forest_score) || 0;
    const avg = (eScore + wScore + rScore) / 3;

    document.getElementById('process-content').innerHTML = [
        renderHeader(data),
        renderOverview(data, eScore, wScore, rScore, eucTop, weucTop, rfTop),
        renderFeatureVector(fv),
        renderEuclidean(euc, eucTop, fv),
        renderWeightedEuclidean(weuc, weucTop, fv),
        renderRandomForest(rf, rfTop),
        renderAggregation(eScore, wScore, rScore, avg, data),
        renderBackButton(data.id)
    ].join('');

    document.getElementById('process-content').classList.remove('hidden');
}

// Header
function renderHeader(data) {
    return `
    <div class="mb-2 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold text-gray-900">CBR Process Visualization</h1>
            <p class="text-gray-600 mt-2">Detailed algorithmic breakdown for
                <span class="font-semibold">${data.customer?.name ?? '–'}</span>
            </p>
        </div>
        <a href="/consultations/${data.id}"
            class="text-yellow-600 hover:text-yellow-700 font-semibold">
            ← Back to Results
        </a>
    </div>`;
}

// Overview banner
function renderOverview(data, eScore, wScore, rScore, eucTop, weucTop, rfTop) {
    const best = (arr) => arr[0]?.product_name ?? 'N/A';
    return `
    <div class="bg-black text-white p-8 rounded-lg">
        <h2 class="text-2xl font-bold mb-6">🔬 CBR Process Overview</h2>
        <div class="grid md:grid-cols-3 gap-6">
            ${[
                ['Euclidean Distance', eScore, best(eucTop)],
                ['Weighted Euclidean', wScore, best(weucTop)],
                ['Random Forest Proximity', rScore, best(rfTop)]
            ].map(([label, score, product]) => `
                <div class="border border-gray-700 p-6 rounded">
                    <div class="text-yellow-400 text-3xl font-bold mb-1">${fmt(score * 100, 2)}%</div>
                    <div class="text-sm text-gray-400">${label}</div>
                    <div class="text-xs text-gray-500 mt-2">Best match: ${product}</div>
                </div>`).join('')}
        </div>
    </div>`;
}

// Feature vector
function renderFeatureVector(fv) {
    const arr = Array.isArray(fv) ? fv : [];
    const dims = arr.length;
    const rows = FEATURE_NAMES.map((name, i) => `
        <div class="flex justify-between items-center p-3 bg-white border border-gray-200 rounded">
            <span class="text-xs text-gray-600 font-mono">[${i}]</span>
            <span class="text-sm text-gray-800 flex-1 mx-3">${name}</span>
            <span class="font-mono font-bold text-gray-900 text-sm">${arr[i] != null ? fmt(arr[i]) : '–'}</span>
        </div>`).join('');

    return `
    <div class="bg-white border border-gray-200 p-8 rounded-lg">
        <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-black text-white rounded-full flex items-center justify-center font-bold text-xl mr-4">1</div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Input Data & Feature Extraction</h2>
                <p class="text-sm text-gray-500 mt-1">Feature vector: ${dims} dimensions (normalized 0 – 1)</p>
            </div>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-2">${rows}</div>
    </div>`;
}

// Euclidean
function renderEuclidean(euc, eucTop, fv) {
    const newVec = Array.isArray(fv) ? fv : [];
    const best = eucTop[0] ?? null;
    const histVec = best?.historical_vector ?? [];
    const diffs = best?.feature_differences ?? [];

    // Top 5
    const top5 = eucTop.map((c, i) => `
        <div class="flex justify-between items-center p-4 border border-gray-200 rounded hover:border-yellow-500 transition">
            <div>
                <span class="font-bold text-gray-500 mr-2">#${i + 1}</span>
                <span class="text-sm text-gray-600">Case #${c.case_id} → ${c.product_name}</span>
            </div>
            <div class="text-right">
                <div class="text-xl font-bold text-yellow-600">${fmt(c.similarity * 100, 2)}%</div>
                <div class="text-xs text-gray-500">dist = ${fmt(c.distance, 4)}</div>
            </div>
        </div>`).join('');

    // Calculation logic table
    const tableRows = FEATURE_NAMES.map((name, i) => `
        <tr class="${i % 2 === 0 ? 'bg-white' : 'bg-gray-50'}">
            <td class="px-3 py-2 text-xs text-gray-700">${name}</td>
            <td class="px-3 py-2 text-center font-mono text-xs">${newVec[i] != null ? fmt(newVec[i]) : '–'}</td>
            <td class="px-3 py-2 text-center font-mono text-xs">${histVec[i] != null ? fmt(histVec[i]) : '–'}</td>
            <td class="px-3 py-2 text-center font-mono text-xs text-yellow-700 font-semibold">${diffs[i] != null ? fmt(diffs[i], 6) : '–'}</td>
        </tr>`).join('');

    const sumSq = best?.sum_squared_diff ?? null;
    const dist = best?.distance ?? null;
    const sim = best?.similarity ?? null;

    return `
    <div class="bg-white border border-gray-200 p-8 rounded-lg">
        <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-black text-white rounded-full flex items-center justify-center font-bold text-xl mr-4">2</div>
            <h2 class="text-2xl font-bold text-gray-900">Euclidean Distance Algorithm</h2>
        </div>

        <!-- Formula -->
        <div class="mb-6 p-6 bg-gray-50 border-l-4 border-yellow-500 rounded-r">
            <div class="text-xs text-gray-500 mb-2 uppercase tracking-wide">Formula</div>
            <div class="font-mono text-base text-gray-900 mb-2">${euc.formula ?? 'd(x,y) = √∑(xᵢ − yᵢ)²'}</div>
            <div class="font-mono text-base text-gray-900">${euc.similarity_formula ?? 'similarity = 1 / (1 + distance)'}</div>
        </div>

        <!-- Top 5 -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-900 mb-3">Top 5 Similar Cases</h3>
            <div class="space-y-2">${top5}</div>
        </div>

        ${best ? `
        <!-- Calculation table for best match -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-900 mb-3">
                Step-by-step calculation — Best match: Case #${best.case_id}
                <span class="text-yellow-600">(${best.product_name})</span>
            </h3>
            <div class="overflow-x-auto rounded border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-800 text-white text-xs">
                        <tr>
                            <th class="px-3 py-2 text-left">Feature</th>
                            <th class="px-3 py-2 text-center">New case (xᵢ)</th>
                            <th class="px-3 py-2 text-center">Historical (yᵢ)</th>
                            <th class="px-3 py-2 text-center">(xᵢ − yᵢ)²</th>
                        </tr>
                    </thead>
                    <tbody>${tableRows}</tbody>
                    <tfoot>
                        <tr class="bg-yellow-50 font-bold border-t-2 border-yellow-400">
                            <td class="px-3 py-2 text-sm" colspan="3">∑ (sum of squares)</td>
                            <td class="px-3 py-2 text-center font-mono text-yellow-700">${sumSq != null ? fmt(sumSq, 6) : '–'}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Result box -->
            <div class="mt-4 p-5 bg-black text-white rounded-lg font-mono text-sm space-y-2">
                <div>Distance  = √${sumSq != null ? fmt(sumSq, 6) : '?'}
                    = <span class="text-yellow-400 font-bold">${dist != null ? fmt(dist, 6) : '?'}</span>
                </div>
                <div>Similarity = 1 / (1 + ${dist != null ? fmt(dist, 6) : '?'})
                    = <span class="text-yellow-400 font-bold text-xl">${sim != null ? fmt(sim, 4) : '?'}</span>
                    = <span class="text-yellow-400 font-bold text-xl">${sim != null ? fmt(sim * 100, 2) : '?'}%</span>
                </div>
            </div>
        </div>` : ''}

        <!-- Score banner -->
        <div class="bg-gradient-to-r from-gray-900 to-black text-white p-6 rounded-lg text-center">
            <div class="text-sm text-gray-400 mb-1">Final Euclidean Similarity Score (best match)</div>
            <div class="text-5xl font-bold text-yellow-400">${sim != null ? fmt(sim * 100, 2) : '–'}%</div>
        </div>
    </div>`;
}

// Weighted Euclidean
function renderWeightedEuclidean(weuc, weucTop, fv) {
    const newVec = Array.isArray(fv) ? fv : [];
    const best = weucTop[0] ?? null;
    const histVec = best?.historical_vector ?? [];
    const wDiffs = best?.weighted_differences ?? [];
    const weights = weuc.weights_used ?? best?.weights_used ?? [];

    // Weight cards
    const weightBars = weights.map(w => `
        <div class="bg-gray-50 border border-gray-200 p-3 rounded">
            <div class="flex justify-between items-center mb-1">
                <span class="text-xs text-gray-700">${w.feature}</span>
                <span class="font-mono font-bold text-yellow-600 text-xs">${fmt(w.weight, 4)}</span>
            </div>
        </div>`).join('');

    // Top 5
    const top5 = weucTop.map((c, i) => `
        <div class="flex justify-between items-center p-4 border border-gray-200 rounded hover:border-yellow-500 transition">
            <div>
                <span class="font-bold text-gray-500 mr-2">#${i + 1}</span>
                <span class="text-sm text-gray-600">Case #${c.case_id} → ${c.product_name}</span>
            </div>
            <div class="text-right">
                <div class="text-xl font-bold text-yellow-600">${fmt(c.similarity * 100, 2)}%</div>
                <div class="text-xs text-gray-500">w-dist = ${fmt(c.distance, 4)}</div>
            </div>
        </div>`).join('');

    // Calculation logic table
    const tableRows = FEATURE_NAMES.map((name, i) => {
        const xi  = newVec[i];
        const yi  = histVec[i];
        const wi  = weights[i]?.weight;
        const sq  = (xi != null && yi != null) ? Math.pow(xi - yi, 2) : null;
        const wdi = wDiffs[i];
        return `
        <tr class="${i % 2 === 0 ? 'bg-white' : 'bg-gray-50'}">
            <td class="px-3 py-2 text-xs text-gray-700">${name}</td>
            <td class="px-3 py-2 text-center font-mono text-xs text-yellow-700 font-semibold">${wi != null ? fmt(wi, 4) : '–'}</td>
            <td class="px-3 py-2 text-center font-mono text-xs">${sq != null ? fmt(sq, 6) : '–'}</td>
            <td class="px-3 py-2 text-center font-mono text-xs text-yellow-700 font-semibold">${wdi != null ? fmt(wdi, 6) : '–'}</td>
        </tr>`;
    }).join('');

    const sumW = best?.sum_weighted_squared ?? null;
    const dist = best?.distance ?? null;
    const sim = best?.similarity ?? null;

    return `
    <div class="bg-white border border-gray-200 p-8 rounded-lg">
        <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-black text-white rounded-full flex items-center justify-center font-bold text-xl mr-4">3</div>
            <h2 class="text-2xl font-bold text-gray-900">Weighted Euclidean Distance Algorithm</h2>
        </div>

        <!-- Formula -->
        <div class="mb-6 p-6 bg-gray-50 border-l-4 border-yellow-500 rounded-r">
            <div class="text-xs text-gray-500 mb-2 uppercase tracking-wide">Formula</div>
            <div class="font-mono text-base text-gray-900 mb-2">${weuc.formula ?? 'dw(x,y) = √∑ wᵢ(xᵢ − yᵢ)²'}</div>
            <div class="font-mono text-base text-gray-900">${weuc.similarity_formula ?? 'similarity = 1 / (1 + weighted_distance)'}</div>
        </div>

        ${weights.length > 0 ? `
        <!-- Feature weights -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-900 mb-3">Feature Weights (wᵢ)</h3>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">${weightBars}</div>
        </div>` : ''}

        <!-- Top 5 -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-900 mb-3">Top 5 Weighted Similar Cases</h3>
            <div class="space-y-2">${top5}</div>
        </div>

        ${best ? `
        <!-- Calculation table -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-900 mb-3">
                Step-by-step calculation — Best match: Case #${best.case_id}
                <span class="text-yellow-600">(${best.product_name})</span>
            </h3>
            <div class="overflow-x-auto rounded border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-800 text-white text-xs">
                        <tr>
                            <th class="px-3 py-2 text-left">Feature</th>
                            <th class="px-3 py-2 text-center">Weight (wᵢ)</th>
                            <th class="px-3 py-2 text-center">(xᵢ − yᵢ)²</th>
                            <th class="px-3 py-2 text-center">wᵢ × (xᵢ − yᵢ)²</th>
                        </tr>
                    </thead>
                    <tbody>${tableRows}</tbody>
                    <tfoot>
                        <tr class="bg-yellow-50 font-bold border-t-2 border-yellow-400">
                            <td class="px-3 py-2 text-sm" colspan="3">Weighted ∑</td>
                            <td class="px-3 py-2 text-center font-mono text-yellow-700">${sumW != null ? fmt(sumW, 6) : '–'}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-4 p-5 bg-black text-white rounded-lg font-mono text-sm space-y-2">
                <div>Weighted distance = √${sumW != null ? fmt(sumW, 6) : '?'}
                    = <span class="text-yellow-400 font-bold">${dist != null ? fmt(dist, 6) : '?'}</span>
                </div>
                <div>Similarity = 1 / (1 + ${dist != null ? fmt(dist, 6) : '?'})
                    = <span class="text-yellow-400 font-bold text-xl">${sim != null ? fmt(sim, 4) : '?'}</span>
                    = <span class="text-yellow-400 font-bold text-xl">${sim != null ? fmt(sim * 100, 2) : '?'}%</span>
                </div>
            </div>
        </div>` : ''}

        <!-- Score banner -->
        <div class="bg-gradient-to-r from-gray-900 to-black text-white p-6 rounded-lg text-center">
            <div class="text-sm text-gray-400 mb-1">Final Weighted Euclidean Similarity Score (best match)</div>
            <div class="text-5xl font-bold text-yellow-400">${sim != null ? fmt(sim * 100, 2) : '–'}%</div>
        </div>
    </div>`;
}

// Random Forest
function renderRandomForest(rf, rfTop) {
    const best = rfTop[0] ?? null;
    const treeMatches = best?.tree_by_tree_matches ?? [];
    const first10 = treeMatches.slice(0, 10);

    // Top 5
    const top5 = rfTop.map((c, i) => `
        <div class="flex justify-between items-center p-4 border border-gray-200 rounded hover:border-yellow-500 transition">
            <div>
                <span class="font-bold text-gray-500 mr-2">#${i + 1}</span>
                <span class="text-sm text-gray-600">Case #${c.case_id} → ${c.product_name}</span>
            </div>
            <div class="text-right">
                <div class="text-xl font-bold text-yellow-600">${fmt(c.similarity * 100, 2)}%</div>
                <div class="text-xs text-gray-500">${c.matches} / ${c.total_trees} trees match</div>
            </div>
        </div>`).join('');

    // First 10 leaves
    const compRows = first10.map(t => `
        <div class="flex items-center justify-between p-4 border rounded
             ${t.match ? 'border-green-400 bg-green-50' : 'border-red-300 bg-red-50'}">
            <span class="font-semibold text-gray-900 text-sm">Tree ${t.tree_id}</span>
            <div class="flex items-center gap-6">
                <div class="text-center">
                    <div class="text-xs text-gray-500">New case</div>
                    <div class="font-mono font-bold text-sm">Leaf ${t.new_leaf}</div>
                </div>
                <div class="text-xl font-bold ${t.match ? 'text-green-600' : 'text-red-500'}">
                    ${t.match ? '=' : '≠'}
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500">Historical</div>
                    <div class="font-mono font-bold text-sm">Leaf ${t.hist_leaf}</div>
                </div>
                <div class="text-xl ${t.match ? 'text-green-600' : 'text-red-500'}">
                    ${t.match ? '✓' : '✗'}
                </div>
            </div>
        </div>`).join('');

    return `
    <div class="bg-white border border-gray-200 p-8 rounded-lg">
        <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-black text-white rounded-full flex items-center justify-center font-bold text-xl mr-4">4</div>
            <h2 class="text-2xl font-bold text-gray-900">Random Forest Proximity Algorithm</h2>
        </div>

        <!-- Explanation -->
        <div class="mb-6 p-6 bg-gray-50 border-l-4 border-yellow-500 rounded-r">
            <div class="text-xs text-gray-500 mb-2 uppercase tracking-wide">Formula</div>
            <div class="font-mono text-base text-gray-900 mb-3">
                ${rf.proximity_formula ?? 'proximity = matching_leaves / total_trees'}
            </div>
            <ol class="list-decimal list-inside space-y-1 text-sm text-gray-700">
                <li>The RF model contains <strong>${best?.total_trees ?? 100}</strong> decision trees.</li>
                <li>Each case is passed through all trees; each tree assigns it to a leaf node.</li>
                <li>We compare leaf node IDs between the new case and each historical case.</li>
                <li>The more trees where both cases land in the same leaf, the more similar they are.</li>
            </ol>
        </div>

        <!-- Top 5 -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-900 mb-3">Top 5 Random Forest Similar Cases</h3>
            <div class="space-y-2">${top5}</div>
        </div>

        ${best ? `
        <!-- Leaf grid -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-900 mb-2">
                Leaf-node comparison — Best match: Case #${best.case_id}
                <span class="text-yellow-600">(${best.product_name})</span>
            </h3>

            <!-- First 10 detailed -->
            <h4 class="font-bold text-gray-900 mb-3">First 10 trees — detailed view</h4>
            <div class="space-y-2 mb-6">${compRows}</div>

            <!-- Proximity calc -->
            <div class="p-6 bg-black text-white rounded-lg font-mono">
                <div class="text-lg mb-3">Proximity Calculation:</div>
                <div class="text-3xl mb-2">
                    Proximity = <span class="text-yellow-400">${best.matches}</span> / ${best.total_trees}
                </div>
                <div class="text-5xl font-bold text-yellow-400">
                    = ${fmt(best.similarity, 4)} (${fmt(best.similarity * 100, 2)}%)
                </div>
            </div>
        </div>

        <!-- Tree visualization -->
        <div>
            <h3 class="font-bold text-gray-900 mb-4">Decision Tree Visualizations</h3>
            <button onclick="generateTrees()"
                    id="generate-trees-btn"
                    class="bg-gradient-to-r from-yellow-600 to-yellow-500 text-white px-6 py-3 rounded hover:from-yellow-700 hover:to-yellow-600">
                Generate Tree Visualizations
            </button>
            <div id="tree-status" class="mt-4 hidden"></div>
            <div id="tree-images" class="mt-4 space-y-6"></div>
        </div>` : ''}

        <!-- Score banner -->
        ${best ? `
        <div class="bg-gradient-to-r from-gray-900 to-black text-white p-6 rounded-lg text-center mt-6">
            <div class="text-sm text-gray-400 mb-1">Final Random Forest Proximity Score (best match)</div>
            <div class="text-5xl font-bold text-yellow-400">${fmt(best.similarity * 100, 2)}%</div>
            <div class="text-sm text-gray-400 mt-3">
                Based on ${best.matches} matching leaf nodes out of ${best.total_trees} trees
            </div>
        </div>` : ''}
    </div>`;
}

// Aggregation
function renderAggregation(eScore, wScore, rScore, avg, data) {
    return `
    <div class="bg-black text-white p-8 rounded-lg">
        <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-yellow-500 text-black rounded-full flex items-center justify-center font-bold text-xl mr-4">5</div>
            <h2 class="text-2xl font-bold">Final Score Aggregation</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            ${[
                ['Euclidean', eScore],
                ['Weighted Euclidean', wScore],
                ['Random Forest', rScore]
            ].map(([label, score]) => `
                <div class="border border-gray-700 p-6 rounded">
                    <div class="text-sm text-gray-400 mb-1">${label}</div>
                    <div class="text-4xl font-bold text-yellow-400">${fmt(score * 100, 2)}%</div>
                </div>`).join('')}
        </div>
        <div class="bg-yellow-500 text-black p-8 rounded-lg text-center">
            <div class="text-sm font-semibold mb-2 uppercase tracking-wide">Final Aggregated Score</div>
            <div class="font-mono text-lg mb-3">
                (${fmt(eScore * 100, 2)} + ${fmt(wScore * 100, 2)} + ${fmt(rScore * 100, 2)}) / 3
            </div>
            <div class="text-7xl font-bold">${fmt(avg * 100, 2)}%</div>
            <div class="text-sm mt-4 font-semibold">
                Recommended Product: ${data.product?.name ?? '–'}
            </div>
        </div>
    </div>`;
}

// Back button
function renderBackButton(id) {
    return `
    <div class="text-center pb-8">
        <a href="/consultations/${id}"
           class="inline-block bg-white border-2 border-black text-black px-8 py-4 hover:bg-black hover:text-white transition font-semibold rounded">
            ← Back to Consultation Results
        </a>
    </div>`;
}

// Tree generation
async function generateTrees() {
    const btn    = document.getElementById('generate-trees-btn');
    const status = document.getElementById('tree-status');
    const imgs   = document.getElementById('tree-images');

    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-2">⟳</span> Generating...';
    status.classList.remove('hidden');
    status.innerHTML = '<p class="text-gray-600 text-sm">Generating decision tree visualizations…</p>';

    try {
        const res = await fetch(`/api/visualizations/trees/${consultationId}`, {
            method: 'POST',
            headers: { Accept: 'application/json', Authorization: `Bearer ${apiToken}` }
        });

        const result = await res.json();
        if (!res.ok) throw new Error(result.message ?? 'Failed to generate trees');

        status.innerHTML = '<p class="text-gray-600 text-sm">Rendering decision trees… this can take a little while.</p>';
        await pollTreeStatus(result.data.request_id);

    } catch (e) {
        status.innerHTML = `<p class="text-red-600 text-sm">✗ Error: ${e.message}</p>`;
        btn.textContent  = 'Try Again';
        btn.disabled     = false;
    }
}

async function pollTreeStatus(requestId) {
    const btn    = document.getElementById('generate-trees-btn');
    const status = document.getElementById('tree-status');
    const imgs   = document.getElementById('tree-images');

    while (true) {
        const res = await fetch(`/api/visualizations/requests/${requestId}`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${apiToken}` }
        });

        const result = await res.json();

        if (!res.ok || !result.success) {
            status.innerHTML = `<p class="text-red-600 text-sm">✗ Error: ${result.message ?? 'Failed to check status'}</p>`;
            btn.textContent  = 'Try Again';
            btn.disabled     = false;
            return;
        }

        const { status: jobStatus, trees, error_message } = result.data;

        if (jobStatus === 'completed') {
            status.innerHTML = '<p class="text-green-600 font-semibold text-sm">✓ Trees generated successfully!</p>';

            imgs.innerHTML = trees.map((tree, i) => `
                <div class="bg-white border border-gray-200 p-4 rounded">
                    <h4 class="font-bold text-gray-900 mb-2">Decision Tree ${i + 1}</h4>
                    <img src="/tree_visualizations/${tree.image_filename}"
                        alt="Tree ${i + 1}"
                        class="w-full border border-gray-300 rounded">
                </div>`).join('');

            btn.textContent = 'Regenerate Trees';
            btn.disabled = false;
            return;
        }

        if (jobStatus === 'failed') {
            status.innerHTML = `<p class="text-red-600 text-sm">✗ Error: ${error_message ?? 'Tree generation failed'}</p>`;
            btn.textContent  = 'Try Again';
            btn.disabled     = false;
            return;
        }

        // wait 2 seconds before looping
        await new Promise(resolve => setTimeout(resolve, 2000));
    }
}

loadProcess();
</script>
@endsection
