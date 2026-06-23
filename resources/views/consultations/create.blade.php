@extends('layouts.app')

@section('title', 'New Consultation')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">New Consultation</h1>

    <!-- Error display -->
    <div id="error-message" class="hidden bg-red-50 border border-red-200 text-red-800 p-4 rounded mb-6"></div>

    <!-- Success message -->
    <div id="success-message" class="hidden bg-green-50 border border-green-200 text-green-800 p-4 rounded mb-6"></div>

    <form id="consultation-form" class="space-y-8">
        <!-- Step 1: Customer Information -->
        <div class="bg-white border border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Customer Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                    <input type="text" name="name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Gender</label>
                    <select name="gender" required
                            class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date of Birth</label>
                    <input type="date" name="dob" required
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Marital Status</label>
                    <select name="marital_status" required
                            class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                        <option value="">Select Status</option>
                        <option value="single">Single</option>
                        <option value="married">Married</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Occupation</label>
                    <select name="occupation_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                        <option value="">Select Occupation</option>
                        @foreach($occupations as $occupation)
                            <option value="{{ $occupation->id }}">{{ $occupation->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Income Range</label>
                    <select name="income_range" required
                            class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                        <option value="">Select Range</option>
                        <option value="below_50m">Below Rp 50M</option>
                        <option value="50m_100m">Rp 50M - 100M</option>
                        <option value="100m_300m">Rp 100M - 300M</option>
                        <option value="300m_500m">Rp 300M - 500M</option>
                        <option value="500m_1b">Rp 500M - 1B</option>
                        <option value="above_1b">Above Rp 1B</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Number of Dependents</label>
                    <input type="number" name="num_dependents" min="0" max="20" required
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                </div>
            </div>
        </div>

        <!-- Policy Holder Information -->
<div class="bg-white border border-gray-200 p-6">
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Policy Holder Information</h2>
    <p class="text-sm text-gray-500 mb-5">
        The policy holder is the person who signs the contract and pays the premiums.
        This may or may not be the same person as the insured above.
    </p>

    <!-- Same-person toggle -->
    <label class="flex items-start cursor-pointer p-4 bg-gray-50 border border-gray-200 rounded-lg mb-5 hover:bg-gray-100 transition">
        <input type="checkbox"
               id="holder_is_insured_checkbox"
               name="holder_is_insured"
               value="1"
               checked
               onchange="toggleHolderFields(this.checked)"
               class="w-5 h-5 mt-0.5 mr-3 flex-shrink-0">
        <div>
            <span class="text-base font-semibold text-gray-900">
                Policy holder and policy insured is the same person
            </span>
            <p class="text-sm text-gray-500 mt-0.5">
                Uncheck if someone else (e.g. a parent) is purchasing this insurance on behalf of the insured.
            </p>
        </div>
    </label>

    <!-- Extra fields — hidden when same person -->
    <div id="holder-fields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Holder Full Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="holder_name" id="holder_name"
                   class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Holder Date of Birth <span class="text-red-500">*</span>
            </label>
            <input type="date" name="holder_dob" id="holder_dob"
                   class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Holder Gender <span class="text-red-500">*</span>
            </label>
            <select name="holder_gender" id="holder_gender"
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Holder Income Range <span class="text-red-500">*</span>
            </label>
            <select name="holder_income_range" id="holder_income_range"
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                <option value="">Select Range</option>
                <option value="below_50m">Below Rp 50M</option>
                <option value="50m_100m">Rp 50M – 100M</option>
                <option value="100m_300m">Rp 100M – 300M</option>
                <option value="300m_500m">Rp 300M – 500M</option>
                <option value="500m_1b">Rp 500M – 1B</option>
                <option value="above_1b">Above Rp 1B</option>
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Holder's Relationship to the Insured <span class="text-red-500">*</span>
            </label>
            <select name="holder_relationship_to_insured" id="holder_relationship_to_insured"
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                <option value="">Select Relationship</option>
                <option value="orang tua kandung">Orang Tua Kandung</option>
                <option value="suami/istri">Suami/Istri</option>
                <option value="anak kandung">Anak Kandung</option>
                <option value="adik/kakak kandung">Adik/Kakak Kandung</option>
                <option value="nenek/kakek kandung">Nenek/Kakek Kandung</option>
                <option value="cucu/cicit">Cucu/Cicit</option>
                <option value="lainnya">Lainnya</option>
            </select>
        </div>
    </div>
</div>

        <!-- Step 2: Beneficiary Information -->
        <div class="bg-white border border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Beneficiary Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Beneficiary Name</label>
                    <input type="text" name="beneficiary_name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Beneficiary DOB</label>
                    <input type="date" name="beneficiary_dob" required
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Beneficiary Gender</label>
                    <select name="beneficiary_gender" required
                            class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Relationship</label>
                    <select name="beneficiary_relationship" required
                            class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                        <option value="">Select Relationship</option>
                        <option value="adik/kakak kandung">Adik/Kakak Kandung</option>
                        <option value="anak kandung">Anak Kandung</option>
                        <option value="cucu/cicit">Cucu/Cicit</option>
                        <option value="nenek/kakek kandung">Nenek/Kakek Kandung</option>
                        <option value="orang tua kandung">Orang Tua Kandung</option>
                        <option value="suami/istri">Suami/Istri</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Step 3: Financial Goals -->
        <div class="bg-white border border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Financial Goals & Insurance Needs</h2>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Financial Goals (select all that apply)</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="financial_goals[]" value="life" class="mr-2">
                        Life
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="financial_goals[]" value="health" class="mr-2">
                        Health
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="financial_goals[]" value="retirement" class="mr-2">
                        Retirement
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="financial_goals[]" value="education" class="mr-2">
                        Education
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="financial_goals[]" value="critical_illness" class="mr-2">
                        Critical Illness
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="financial_goals[]" value="income_protection" class="mr-2">
                        Income Protection
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="financial_goals[]" value="savings" class="mr-2">
                        Savings/Investment
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="financial_goals[]" value="accidents" class="mr-2">
                        Accidents
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Insurance Period (years)</label>
                    <input type="number" name="insurance_period" min="1" max="50" required
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nominal Received (Rp)</label>
                    <input type="number" name="nominal_received" min="0" required
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <label class="flex items-center">
                    <input type="checkbox" name="has_existing_health_insurance" value="1" class="mr-2">
                    Has existing health insurance
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="high_risk_hobby" value="1" class="mr-2">
                    Has high-risk hobby
                </label>
            </div>

            <div class="mt-6 border-t border-gray-300 pt-6">
                <div class="mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox"
                               id="overseas_medical_plans"
                               name="overseas_medical_plans"
                               value="1"
                               onchange="toggleOverseasRegions(this.checked)"
                               class="mr-3 w-5 h-5">
                        <div>
                            <span class="text-lg font-semibold text-gray-900">Plans for overseas travel/medical purposes</span>
                            <p class="text-sm text-gray-600">Select regions you need coverage for</p>
                        </div>
                    </label>
                </div>

                <!-- Regional Coverage Options (Hidden by default) -->
                <div id="overseas-regions-section" class="hidden ml-8 bg-blue-50 border border-blue-200 p-4 rounded-lg">
                    <p class="text-sm font-semibold text-blue-900 mb-3">Select Coverage Regions:</p>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <label class="flex items-center bg-white p-3 rounded border border-blue-200 hover:border-blue-400 cursor-pointer transition">
                            <input type="checkbox" name="coverage_regions[]" value="asia_exc_hkg_sg_jpn" class="mr-2">
                            <div class="font-semibold text-gray-900">Asia Exc. HKG, SG and JPN</div>
                        </label>

                        <label class="flex items-center bg-white p-3 rounded border border-blue-200 hover:border-blue-400 cursor-pointer transition">
                            <input type="checkbox" name="coverage_regions[]" value="hkg_sg_jpn" class="mr-2">
                            <div class="font-semibold text-gray-900">HKG, SG and JPN</div>
                        </label>

                        <label class="flex items-center bg-white p-3 rounded border border-blue-200 hover:border-blue-400 cursor-pointer transition">
                            <input type="checkbox" name="coverage_regions[]" value="europe" class="mr-2">
                            <div class="font-semibold text-gray-900">Europe</div>
                        </label>

                        <label class="flex items-center bg-white p-3 rounded border border-blue-200 hover:border-blue-400 cursor-pointer transition">
                            <input type="checkbox" name="coverage_regions[]" value="north_america" class="mr-2">
                            <div class="font-semibold text-gray-900">North America</div>
                        </label>

                        <label class="flex items-center bg-white p-3 rounded border border-blue-200 hover:border-blue-400 cursor-pointer transition">
                            <input type="checkbox" name="coverage_regions[]" value="south_america" class="mr-2">
                            <div class="font-semibold text-gray-900">South America</div>
                        </label>

                        <label class="flex items-center bg-white p-3 rounded border border-blue-200 hover:border-blue-400 cursor-pointer transition">
                            <input type="checkbox" name="coverage_regions[]" value="africa" class="mr-2">
                            <div class="font-semibold text-gray-900">Africa</div>
                        </label>

                        <label class="flex items-center bg-white p-3 rounded border border-blue-200 hover:border-blue-400 cursor-pointer transition">
                            <input type="checkbox" name="coverage_regions[]" value="oceania" class="mr-2">
                            <div class="font-semibold text-gray-900">Oceania</div>
                        </label>
                    </div>
                </div>

        <!-- Step 4: Health Information -->
        <div class="bg-white border border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Health Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Height (cm)</label>
                    <input type="number" name="height" min="100" max="250" required
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Weight (kg)</label>
                    <input type="number" name="weight" min="30" max="200" required
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600">
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <label class="flex items-center">
                    <input type="checkbox" name="weight_change_last_year" value="1" class="mr-2">
                    Weight change last year
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="smoked_last_year" value="1" class="mr-2">
                    Smoked last year
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="hospitalization_last_5_years" value="1" class="mr-2">
                    Hospitalization (5 yrs)
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="lab_tests_last_5_years" value="1" class="mr-2">
                    Lab tests (5 yrs)
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="accident_poisoning_last_5_years" value="1" class="mr-2">
                    Accident/poisoning (5 yrs)
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="has_disability" value="1" class="mr-2">
                    Has disability
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="has_serious_illness" value="1" class="mr-2">
                    Has serious illness
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="receiving_treatment" value="1" class="mr-2">
                    Receiving treatment
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="family_medical_history" value="1" class="mr-2">
                    Family medical history
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="is_pregnant" value="1" class="mr-2">
                    Is pregnant (if female)
                </label>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Additional Health Details</label>
                <textarea name="health_details" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600"></textarea>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('consultations.index') }}"
               class="px-6 py-3 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" id="submit-btn"
                    class="bg-gradient-to-r from-yellow-600 to-yellow-500 text-white px-8 py-3 rounded hover:from-yellow-700 hover:to-yellow-600">
                Generate Recommendation
            </button>
        </div>
    </form>
</div>

<script>
function toggleOverseasRegions(isChecked) {
    const regionsSection = document.getElementById('overseas-regions-section');
    if (isChecked) {
        regionsSection.classList.remove('hidden');
    } else {
        regionsSection.classList.add('hidden');
        // Uncheck all region checkboxes
        document.querySelectorAll('input[name="coverage_regions[]"]').forEach(cb => cb.checked = false);
    }
}

function toggleHolderFields(isSame) {
    const section = document.getElementById('holder-fields');
    if (isSame) {
        section.classList.add('hidden');
        // Clear fields
        ['holder_name', 'holder_dob', 'holder_gender', 'holder_income_range', 'holder_relationship_to_insured']
            .forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
    } else {
        section.classList.remove('hidden');
    }
}

const apiToken = document.querySelector('meta[name="api-token"]').content;

async function pollRecommendationStatus(requestId) {
    const errorDiv = document.getElementById('error-message');
    const submitBtn = document.getElementById('submit-btn');

    while (true) {
        const response = await fetch(`/api/recommendations/requests/${requestId}`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${apiToken}`
            }
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            errorDiv.textContent = result.message || 'Failed to check recommendation status';
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Generate Recommendation';
            return;
        }

        const { status, case_id, error_message } = result.data;

        if (status === 'completed') {
            window.location.href = `/consultations/${case_id}`;
            return;
        }

        if (status === 'failed') {
            errorDiv.textContent = error_message || 'CBR processing failed';
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Generate Recommendation';
            return;
        }

        // wait 2 seconds before looping
        await new Promise(resolve => setTimeout(resolve, 2000));
    }
}

document.getElementById('consultation-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submit-btn');
    const errorDiv = document.getElementById('error-message');
    const successDiv = document.getElementById('success-message');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⟳</span> Processing...';

    // Collect form data
    const formData = new FormData(this);
    const data = {};

    // Set all boolean to false by default
    const booleanFields = [
        'overseas_medical_plans', 'has_existing_health_insurance', 'high_risk_hobby',
        'weight_change_last_year', 'smoked_last_year', 'hospitalization_last_5_years',
        'lab_tests_last_5_years', 'accident_poisoning_last_5_years', 'has_disability',
        'has_serious_illness', 'receiving_treatment', 'family_medical_history', 'is_pregnant'
    ];

    booleanFields.forEach(field => {data[field] = false;});
    data.financial_goals = [];
    data.coverage_regions = [];

    // Convert FormData to JSON
    for (let [key, value] of formData.entries()) {
        if (key === 'financial_goals[]') {
            data.financial_goals.push(value);
        } else if (key === 'coverage_regions[]') {
            data.coverage_regions.push(value);
        } else if (booleanFields.includes(key)) {
            data[key] = true;
        } else {
            data[key] = value;
        }
    }

     // Policy holder
    const holderIsSame = document.getElementById('holder_is_insured_checkbox').checked;
    data.holder_is_insured = holderIsSame;

    if (holderIsSame) {
        data.holder_name = data.name;
        data.holder_dob = data.dob;
        data.holder_gender = data.gender;
        data.holder_income_range = data.income_range;
        data.holder_relationship_to_insured = 'diri sendiri';
    }

    data.occupation_id = parseInt(data.occupation_id);
    data.num_dependents = parseInt(data.num_dependents);
    data.insurance_period = parseInt(data.insurance_period);
    data.nominal_received = parseFloat(data.nominal_received);
    data.height = parseFloat(data.height);
    data.weight = parseFloat(data.weight);

    console.log('Submitting data:', data);

    try {
        const response = await fetch('/api/recommendations', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${apiToken}`
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!response.ok) {
            // Validation errors
            if (result.errors) {
                let errorText = '<ul class="list-disc list-inside">';
                for (let field in result.errors) {
                    result.errors[field].forEach(error => {
                        errorText += `<li>${error}</li>`;
                    });
                }
                errorText += '</ul>';
                errorDiv.innerHTML = errorText;
                errorDiv.classList.remove('hidden');
            } else {
                errorDiv.textContent = result.message || 'An error occurred';
                errorDiv.classList.remove('hidden');
            }

            submitBtn.disabled = false;
            submitBtn.textContent = 'Generate Recommendation';
            return;
        }

        // Success, start polling for recommendation status
        submitBtn.textContent = 'Processing recommendation...';
        await pollRecommendationStatus(result.data.request_id);

    } catch (error) {
        console.error('Error:', error);
        errorDiv.textContent = 'Network error. Please try again.';
        errorDiv.classList.remove('hidden');

        submitBtn.disabled = false;
        submitBtn.textContent = 'Generate Recommendation';
    }
});
</script>
@endsection
