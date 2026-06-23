@extends('layouts.app')

@section('title', 'My Recommendations')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Insurance Recommendations</h1>
            <p class="text-gray-600">View your personalized insurance recommendations</p>
        </div>

        @if($consultations->count() > 0)
            <div class="grid gap-6">
                @foreach($consultations as $consultation)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="bg-gold-500 px-6 py-4 text-white">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-xl font-bold">{{ $consultation->product->name }}</h3>
                                    <p class="text-blue-100 text-sm">Recommended on
                                        {{ $consultation->created_at?->format('M d, Y') ?? 'Unknown Date' }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-3xl font-bold">
                                        {{ round(($consultation->euclidean_score + $consultation->weighted_euclidean_score + $consultation->random_forest_score) / 3 * 100, 1) }}%
                                    </div>
                                    <div class="text-blue-200 text-sm">Match Score</div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="grid md:grid-cols-2 gap-6">
                                <!-- Product Details -->
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-3">Product Details</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Base Premium:</span>
                                            <span class="font-semibold">Rp
                                                {{ number_format($consultation->product->base_premium, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Insurance Period:</span>
                                            <span class="font-semibold">{{ $consultation->insurance_period }} years</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Your Information -->
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-3">Your Information</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">BMI:</span>
                                            <span class="font-semibold">{{ $consultation->bmi }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Health Risk Score:</span>
                                            <span class="font-semibold">{{ $consultation->health_risk_score }} / 25</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Agent:</span>
                                            <span class="font-semibold">{{ $consultation->agent->name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Goals -->
                            <div class="mt-6">
                                <h4 class="font-semibold text-gray-900 mb-3">Your Financial Goals</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($consultation->financial_goals as $goal)
                                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                            {{ ucwords(str_replace('_', ' ', $goal)) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            <!-- Product Description -->
                            @if($consultation->product->description)
                                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                                    <h4 class="font-semibold text-gray-900 mb-2">About This Product</h4>
                                    <p class="text-sm text-gray-700">{{ $consultation->product->description }}</p>
                                </div>
                            @endif

                            <!-- Contact Agent -->
                            <div class="mt-6 text-center">
                                <p class="text-sm text-gray-600 mb-2">
                                    Have questions about this recommendation?
                                </p>
                                <p class="text-sm">
                                    Contact your agent: <span
                                        class="font-semibold text-blue-600">{{ $consultation->agent->email }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($consultations->hasPages())
                <div class="mt-6">
                    {{ $consultations->links() }}
                </div>
            @endif
        @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="text-6xl mb-4">📋</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Recommendations Yet</h3>
                <p class="text-gray-600 mb-6">
                    You don't have any insurance recommendations yet. Contact your agent to schedule a consultation.
                </p>
            </div>
        @endif
    </div>
@endsection