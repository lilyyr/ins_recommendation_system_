@extends('layouts.app')

@section('title', 'Feature Weights')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Feature Weights Configuration</h1>
        <p class="text-gray-600">Adjust the importance of each feature in the Weighted Euclidean algorithm</p>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h3 class="font-semibold text-blue-900 mb-2">Feature Weights</h3>
        <p class="text-sm text-blue-800">
            Feature weights determine the importance of each customer attribute when calculating similarity.
            Higher weights (closer to 1.0) make the feature more important when the system uses it to make recommendations.
            All weights should sum to approximately 1.0 for best results.
        </p>
    </div>

    <form method="POST" action="{{ route('admin.weights.update') }}">
        @csrf

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Current Total -->
            <div class="bg-gradient-to-r from-purple-600 to-purple-800 px-6 py-4 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-sm text-purple-200">Current Total Weight</div>
                        <div class="text-3xl font-bold" id="totalWeight">
                            {{ number_format($weights->sum('weight'), 4) }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-purple-200">Target</div>
                        <div class="text-2xl font-bold">1.0000</div>
                    </div>
                </div>
            </div>

            <!-- Demographics Section -->
            <div class="p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Demographics (4 features)</h3>
                <div class="space-y-4">
                    @foreach($weights->slice(0, 4) as $weight)
                        <div class="flex items-center">
                            <input type="hidden" name="weights[{{ $loop->index }}][id]" value="{{ $weight->id }}">

                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    {{ $weight->description }}
                                </label>
                                <input
                                    type="number"
                                    name="weights[{{ $loop->index }}][weight]"
                                    value="{{ $weight->weight }}"
                                    min="0"
                                    max="1"
                                    step="0.01"
                                    class="weight-input w-32 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                    onchange="updateTotal()"
                                >
                            </div>

                            <div class="flex-1 ml-4">
                                <div class="bg-gray-200 rounded-full h-3">
                                    <div class="bg-purple-600 h-3 rounded-full transition-all" style="width: {{ $weight->weight * 100 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Health Metrics Section -->
            <div class="p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Health Metrics (2 features)</h3>
                <div class="space-y-4">
                    @foreach($weights->slice(4, 2) as $weight)
                        <div class="flex items-center">
                            <input type="hidden" name="weights[{{ $loop->index + 4 }}][id]" value="{{ $weight->id }}">

                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    {{ $weight->description }}
                                </label>
                                <input
                                    type="number"
                                    name="weights[{{ $loop->index + 4 }}][weight]"
                                    value="{{ $weight->weight }}"
                                    min="0"
                                    max="1"
                                    step="0.01"
                                    class="weight-input w-32 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                    onchange="updateTotal()"
                                >
                            </div>

                            <div class="flex-1 ml-4">
                                <div class="bg-gray-200 rounded-full h-3">
                                    <div class="bg-purple-600 h-3 rounded-full transition-all" style="width: {{ $weight->weight * 100 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Insurance Needs Section -->
            <div class="p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Insurance Needs (4 features)</h3>
                <div class="space-y-4">
                    @foreach($weights->slice(6, 4) as $weight)
                        <div class="flex items-center">
                            <input type="hidden" name="weights[{{ $loop->index + 6 }}][id]" value="{{ $weight->id }}">

                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    {{ $weight->description }}
                                </label>
                                <input
                                    type="number"
                                    name="weights[{{ $loop->index + 6 }}][weight]"
                                    value="{{ $weight->weight }}"
                                    min="0"
                                    max="1"
                                    step="0.01"
                                    class="weight-input w-32 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                    onchange="updateTotal()"
                                >
                            </div>

                            <div class="flex-1 ml-4">
                                <div class="bg-gray-200 rounded-full h-3">
                                    <div class="bg-purple-600 h-3 rounded-full transition-all" style="width: {{ $weight->weight * 100 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Financial Goals Section -->
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Financial Goals (8 features)</h3>
                <div class="space-y-4">
                    @foreach($weights->slice(10, 8) as $weight)
                        <div class="flex items-center">
                            <input type="hidden" name="weights[{{ $loop->index + 10 }}][id]" value="{{ $weight->id }}">

                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    {{ $weight->description }}
                                </label>
                                <input
                                    type="number"
                                    name="weights[{{ $loop->index + 10 }}][weight]"
                                    value="{{ $weight->weight }}"
                                    min="0"
                                    max="1"
                                    step="0.01"
                                    class="weight-input w-32 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                    onchange="updateTotal()"
                                >
                            </div>

                            <div class="flex-1 ml-4">
                                <div class="bg-gray-200 rounded-full h-3">
                                    <div class="bg-purple-600 h-3 rounded-full transition-all" style="width: {{ $weight->weight * 100 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-between items-center">
            <button
                type="button"
                onclick="resetToDefaults()"
                class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition"
            >
                Reset to Defaults
            </button>

            <button
                type="submit"
                class="bg-purple-600 text-white px-8 py-3 rounded-lg hover:bg-purple-700 transition font-semibold"
            >
                Save Weights
            </button>
        </div>
    </form>

    <!-- Warning if weights don't sum to 1.0 -->
    <div id="weightWarning" class="hidden mt-6 bg-orange-50 border border-orange-200 rounded-lg p-4">
        <p class="text-sm text-orange-800">
            ⚠️ <strong>Warning:</strong> Total weight should be close to 1.0 for optimal results. Current total: <span id="warningTotal"></span>
        </p>
    </div>
</div>

@push('scripts')
<script>
    function updateTotal() {
        const inputs = document.querySelectorAll('.weight-input');
        let total = 0;

        inputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        document.getElementById('totalWeight').textContent = total.toFixed(4);

        // Show warning if total is not close to 1.0
        const warning = document.getElementById('weightWarning');
        if (Math.abs(total - 1.0) > 0.05) {
            warning.classList.remove('hidden');
            document.getElementById('warningTotal').textContent = total.toFixed(4);
        } else {
            warning.classList.add('hidden');
        }
    }

    function resetToDefaults() {
        if (confirm('Reset all weights to default values?')) {
            location.reload();
        }
    }

    updateTotal();
</script>
@endpush
@endsection
