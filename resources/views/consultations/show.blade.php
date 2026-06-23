@extends('layouts.app')

@section('title', 'Consultation Details')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div id="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-yellow-600"></div>
            <p class="mt-4 text-gray-600">Loading consultation details...</p>
        </div>

        <div id="error-state" class="hidden text-center py-12 bg-red-50 border border-red-200 rounded">
            <p class="text-red-600 text-lg font-semibold">Error loading consultation</p>
            <p id="error-message" class="text-red-500 mt-2"></p>
            <a href="{{ route('consultations.index') }}" class="text-yellow-600 hover:text-yellow-700 mt-4 inline-block">
                ← Back to Consultations
            </a>
        </div>

        <!-- Consultation details -->
        <div id="consultation-details" class="hidden space-y-6"></div>
    </div>

    <script>
        const apiToken = document.querySelector('meta[name="api-token"]').content;
        const consultationId = {{ $consultationId }};

        function calculateAge(dob) {
            const birthDate = new Date(dob);
            const today = new Date();

            let age = today.getFullYear() - birthDate.getFullYear();

            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (
                monthDiff < 0 ||
                (monthDiff === 0 && today.getDate() < birthDate.getDate())
            ) {
                age--;
            }

            return age;
        }

        async function loadConsultation() {
            try {
                const response = await fetch(`/api/recommendations/${consultationId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${apiToken}`
                    }
                });

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || 'Failed to load consultation');
                }

                const result = await response.json();
                displayConsultation(result.data);

            } catch (error) {
                console.error('Error:', error);
                document.getElementById('loading').classList.add('hidden');
                document.getElementById('error-state').classList.remove('hidden');
                document.getElementById('error-message').textContent = error.message;
            }
        }

        function displayConsultation(consultation) {
            document.getElementById('loading').classList.add('hidden');

            const matchPercentage = Math.round(
                (parseFloat(consultation.euclidean_score) + parseFloat(consultation.weighted_euclidean_score) + parseFloat(consultation.random_forest_score)) / 3 * 100
            );

            const goalLabels = {
                'life': 'Life Protection',
                'health': 'Health Coverage',
                'retirement': 'Retirement Planning',
                'education': 'Education Fund',
                'critical_illness': 'Critical Illness',
                'income_protection': 'Income Protection',
                'savings': 'Savings/Investment',
                'accidents': 'Accident Coverage',
            };

            const goals = Array.isArray(consultation.financial_goals)
                ? consultation.financial_goals.map(g => goalLabels[g] || g).join(', ')
                : 'N/A';

            const incomeLabels = {
                'below_50m': 'Below Rp 50 Million',
                '50m_100m': 'Rp 50M - 100M',
                '100m_300m': 'Rp 100M - 300M',
                '300m_500m': 'Rp 300M - 500M',
                '500m_1b': 'Rp 500M - 1B',
                'above_1b': 'Above Rp 1 Billion'
            };

            const formatDate = (dateStr) => {
                if (!dateStr) return 'N/A';
                return new Date(dateStr).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            };

            // BMI
            const getBMICategory = (bmi) => {
                if (bmi < 18.5) return { text: 'Underweight', color: 'text-blue-600' };
                if (bmi < 25) return { text: 'Normal', color: 'text-green-600' };
                if (bmi < 30) return { text: 'Overweight', color: 'text-yellow-600' };
                return { text: 'Obese', color: 'text-red-600' };
            };

            const bmiCategory = getBMICategory(consultation.bmi);

            const recommendationsListHtml = consultation.all_recommendations.filter(rec => rec.rank >= 2 && rec.rank <= 5).map(rec => {
                return `
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Rank #${rec.rank}</span>
                            <h3 class="text-xl font-bold text-gray-900 mt-1">${rec.product_name}</h3>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 uppercase">Match</p>
                            <span class="text-2xl font-bold text-yellow-600">${rec.match_percentage}%</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 text-sm border-t border-gray-100 pt-4 mt-2">
                        <div>
                            <p class="text-gray-500 text-xs">Euclidean</p>
                            <p class="font-semibold text-gray-800">${(rec.euclidean_score * 100).toFixed(1)}%</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs">Weighted</p>
                            <p class="font-semibold text-gray-800">${(rec.weighted_euclidean_score * 100).toFixed(1)}%</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs">Random Forest</p>
                            <p class="font-semibold text-gray-800">${(rec.random_forest_score * 100).toFixed(1)}%</p>
                        </div>
                    </div>
                </div>
            `;
            }).join('');

            const reviseOptionsHtml = consultation.all_recommendations.filter(rec => rec.rank <= 5).map(rec => `
                <option value="${rec.product_id}" ${rec.product_id === consultation.product_id ? 'selected' : ''}>
                    ${rec.product_name} (${rec.match_percentage}% match)
                </option>
            `).join('');

            document.getElementById('consultation-details').innerHTML = `
                    <div class="flex justify-between items-center mb-8">
                        <h1 class="text-4xl font-bold text-gray-900">Consultation Details</h1>
                        <a href="{{ route('consultations.index') }}" class="text-yellow-600 hover:text-yellow-700 font-semibold">
                            &larr; Back to List
                        </a>
                    </div>

                    <div class="bg-gradient-to-r from-yellow-600 to-yellow-500 text-white p-8 rounded-lg shadow-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-yellow-100 text-sm uppercase tracking-wide mb-2">Recommended Product</p>
                                <h2 class="text-4xl font-bold mb-4">${consultation.product.name}</h2>
                                <p class="text-yellow-100 mb-4">${consultation.product.description || ''}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-yellow-100 text-sm">Overall Match</p>
                                <p class="text-5xl font-bold">${matchPercentage}%</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-6 mt-6 pt-6 border-t border-yellow-400">
                            <div>
                                <p class="text-yellow-100 text-sm">Euclidean Distance</p>
                                <p class="text-3xl font-bold">${(consultation.euclidean_score * 100).toFixed(1)}%</p>
                            </div>
                            <div>
                                <p class="text-yellow-100 text-sm">Weighted Euclidean</p>
                                <p class="text-3xl font-bold">${(consultation.weighted_euclidean_score * 100).toFixed(1)}%</p>
                            </div>
                            <div>
                                <p class="text-yellow-100 text-sm">Random Forest Proximity</p>
                                <p class="text-3xl font-bold">${(consultation.random_forest_score * 100).toFixed(1)}%</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Adjust Recommendation</h3>
                            <p class="text-sm text-gray-500">Not convinced by the algorithm's top pick? Choose a different product from the candidates below.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <select id="revise-product-select" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                                ${reviseOptionsHtml}
                            </select>
                            <button id="revise-save-btn" class="bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-semibold px-4 py-2 rounded-md transition">
                                Save
                            </button>
                            <span id="revise-status" class="text-sm"></span>
                        </div>
                    </div>

                    <div class="mt-12 mb-12">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Algorithm Results & Rankings</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                            ${recommendationsListHtml}
                        </div>
                    </div>


                    <!-- Customer Information -->
                    <div class="bg-white border border-gray-200 p-6 rounded-lg" >
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-yellow-600">Customer Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Full Name</p>
                            <p class="text-lg font-semibold text-gray-900">${consultation.customer.name}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Gender</p>
                            <p class="text-lg font-semibold text-gray-900 capitalize">${consultation.customer.gender}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Date of Birth</p>
                            <p class="text-lg font-semibold text-gray-900">${formatDate(consultation.customer.dob)}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Date of Birth</p>
                            <p class="text-lg font-semibold text-gray-900">${calculateAge(consultation.customer.dob)} years old</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Marital Status</p>
                            <p class="text-lg font-semibold text-gray-900 capitalize">${consultation.customer.marital_status || 'N/A'}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Occupation</p>
                            <p class="text-lg font-semibold text-gray-900">${consultation.customer.occupation ? consultation.customer.occupation.name : 'N/A'}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Income Range</p>
                            <p class="text-lg font-semibold text-gray-900">${incomeLabels[consultation.customer.income_range] || 'N/A'}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Number of Dependents</p>
                            <p class="text-lg font-semibold text-gray-900">${consultation.customer.num_dependents}</p>
                        </div>
                    </div>
                </div>

                <!--Beneficiary Information-->
                <div class="bg-white border border-gray-200 p-6 rounded-lg">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-yellow-600">Beneficiary Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Beneficiary Name</p>
                            <p class="text-lg font-semibold text-gray-900">${consultation.beneficiary_name || 'N/A'}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Date of Birth</p>
                            <p class="text-lg font-semibold text-gray-900">${formatDate(consultation.beneficiary_dob)}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Gender</p>
                            <p class="text-lg font-semibold text-gray-900 capitalize">${consultation.beneficiary_gender || 'N/A'}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Relationship</p>
                            <p class="text-lg font-semibold text-gray-900 capitalize">${consultation.beneficiary_relationship || 'N/A'}</p>
                        </div>
                    </div>
                </div>

                <!--Insurance Details-->
                <div class="bg-white border border-gray-200 p-6 rounded-lg">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-yellow-600">Insurance Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Financial Goals</p>
                            <p class="text-lg font-semibold text-gray-900">${goals}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Insurance Period</p>
                            <p class="text-lg font-semibold text-gray-900">${consultation.insurance_period} years</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Nominal Received</p>
                            <p class="text-lg font-semibold text-gray-900">Rp ${(consultation.nominal_received || 0).toLocaleString('id-ID')}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Overseas Plans</p>
                            <p class="text-lg font-semibold text-gray-900">${consultation.overseas_medical_plans ? '✓ Yes' : '✗ No'}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Existing Health Insurance</p>
                            <p class="text-lg font-semibold text-gray-900">${consultation.has_existing_health_insurance ? '✓ Yes' : '✗ No'}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">High Risk Hobby</p>
                            <p class="text-lg font-semibold text-gray-900">${consultation.high_risk_hobby ? '✓ Yes' : '✗ No'}</p>
                        </div>
                    </div>
                </div>

                <!--Regional Coverage Display-->
                    ${consultation.overseas_medical_plans ? `
            <div class="bg-white border border-gray-200 p-6 rounded-lg">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-yellow-600">
                    Overseas Medical Coverage Regions
                </h2>

                <div class="grid grid-cols-3 gap-4">

                    ${(() => {
                        const regions = consultation.coverage_regions ? consultation.coverage_regions : [];

                        const regionLabels = {
                            'asia_exc_hkg_sg_jpn': 'Asia (Except HKG, SG, JPN)',
                            'hkg_sg_jpn': 'Hong Kong, Singapore, Japan',
                            'europe': 'Europe',
                            'north_america': 'North America',
                            'south_america': 'South America',
                            'africa': 'Africa',
                            'oceania': 'Oceania',
                        };

                        return regions.map(region => `
                            <div class="bg-blue-50 border border-blue-300 p-3 rounded flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-semibold text-blue-900">${regionLabels[region] || region}</span>
                            </div>
                        `).join('');
                    })()}
                </div>
            </div>
        ` : `
            <div class="bg-white border border-gray-200 p-6 rounded-lg">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Regional Coverage</h2>
                <p class="text-gray-600">No overseas medical coverage required</p>
            </div>
        `}

                <!--Health Information-->
                <div class="bg-white border border-gray-200 p-6 rounded-lg">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-yellow-600">Health Information</h2>

                    ${consultation.has_serious_illness ? `
                        <div class="bg-orange-50 border border-orange-200 text-orange-800 p-4 rounded mb-6">
                            <p class="font-semibold">⚠️ Important Notice</p>
                            <p class="text-sm mt-1">Customer has a serious illness. Health-focused insurance products have been filtered from recommendations.</p>
                        </div>
                    ` : ''}

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Height</p>
                            <p class="text-lg font-semibold text-gray-900">${consultation.height} cm</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Weight</p>
                            <p class="text-lg font-semibold text-gray-900">${consultation.weight} kg</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">BMI</p>
                            <p class="text-lg font-semibold ${bmiCategory.color}">${consultation.bmi} (${bmiCategory.text})</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Health Risk Score</p>
                            <p class="text-lg font-semibold text-gray-900">${parseFloat(consultation.health_risk_score || 0).toFixed(1)} / 25</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 p-4 rounded">
                        <p class="font-semibold text-gray-900 mb-3">Health Factors</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                            <div class="flex items-center">
                                <span class="mr-2">${consultation.weight_change_last_year ? '✓' : '✗'}</span>
                                Weight change last year
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2">${consultation.smoked_last_year ? '✓' : '✗'}</span>
                                Smoked last year
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2">${consultation.hospitalization_last_5_years ? '✓' : '✗'}</span>
                                Hospitalization (5 years)
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2">${consultation.lab_tests_last_5_years ? '✓' : '✗'}</span>
                                Lab tests (5 years)
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2">${consultation.accident_poisoning_last_5_years ? '✓' : '✗'}</span>
                                Accident/poisoning (5 years)
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2">${consultation.has_disability ? '✓' : '✗'}</span>
                                Has disability
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2">${consultation.has_serious_illness ? '✓' : '✗'}</span>
                                Has serious illness
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2">${consultation.receiving_treatment ? '✓' : '✗'}</span>
                                Receiving treatment
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2">${consultation.family_medical_history ? '✓' : '✗'}</span>
                                Family medical history
                            </div>
                            ${consultation.customer.gender === 'female' ? `
                                <div class="flex items-center">
                                    <span class="mr-2">${consultation.is_pregnant ? '✓' : '✗'}</span>
                                    Is pregnant
                                </div>
                            ` : ''}
                        </div>

                        ${consultation.health_details ? `
                            <div class="mt-4 pt-4 border-t border-gray-300">
                                <p class="font-semibold text-gray-900 mb-2">Additional Details</p>
                                <p class="text-gray-700">${consultation.health_details}</p>
                            </div>
                        ` : ''}
                    </div>
                </div>

                <!--Agent Information-->
                <div class="bg-white border border-gray-200 p-6 rounded-lg">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-yellow-600">Consultation Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Consultation ID</p>
                            <p class="text-lg font-semibold text-gray-900">#${consultation.id}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Agent</p>
                            <p class="text-lg font-semibold text-gray-900">${consultation.agent ? consultation.agent.name : 'N/A'}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Date Created</p>
                            <p class="text-lg font-semibold text-gray-900">${formatDate(consultation.created_at)}</p>
                        </div>
                    </div>
                </div>

                <!--Action Buttons-->
                    <div class="flex gap-4">
                        <a href="/consultations/${consultation.id}/process"
                            class="bg-black text-white px-6 py-3 rounded hover:bg-gray-800 transition">
                            View CBR Process Visualization →
                        </a>
                    </div>
            `;

            document.getElementById('consultation-details').classList.remove('hidden');

            document.getElementById('revise-save-btn').addEventListener('click', async () => {
                const select = document.getElementById('revise-product-select');
                const status = document.getElementById('revise-status');
                const productId = select.value;

                status.textContent = 'Saving...';
                status.className = 'text-sm text-gray-500';

                try {
                    const response = await fetch(`/api/recommendations/${consultationId}`, {
                        method: 'PUT',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${apiToken}`
                        },
                        body: JSON.stringify({ product_id: productId })
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'Failed to update recommendation');
                    }

                    status.textContent = `Saved — now recommending "${result.data.product.name}"`;
                    status.className = 'text-sm text-green-600';
                } catch (error) {
                    status.textContent = error.message;
                    status.className = 'text-sm text-red-600';
                }
            });
        }

        loadConsultation();
    </script>
@endsection
