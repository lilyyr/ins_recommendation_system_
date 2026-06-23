@extends('layouts.app')

@section('title', 'Create Product')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Create New Product</h1>
        <p class="text-gray-600">Add a new insurance product to the system</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-8">
        <form method="POST" action="{{ route('admin.products.store') }}">
            @csrf

            <!-- Name -->
            <div class="mb-6">
                <label for="name" class="block text-gray-700 text-sm font-semibold mb-2">
                    Product Name <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    placeholder="e.g., Life Protection Plus"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('name') border-red-500 @enderror"
                >
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-gray-700 text-sm font-semibold mb-2">
                    Description
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    placeholder="Describe the product features and benefits..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('description') border-red-500 @enderror"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Categories -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    Categories <span class="text-red-500">*</span>
                </label>
                <p class="text-sm text-gray-600 mb-3">Select all that apply</p>

                <div class="grid md:grid-cols-2 gap-3">
                    @php
                        $categoryOptions = [
                            'life' => 'Life Insurance',
                            'health' => 'Health Insurance',
                            'critical_illness' => 'Critical Illness',
                            'family_protection' => 'Family Protection',
                            'education' => 'Education',
                            'retirement' => 'Retirement',
                            'savings' => 'Savings',
                            'investment' => 'Investment',
                            'income_protection' => 'Income Protection',
                            'disability' => 'Disability',
                            'medical' => 'Medical',
                            'wealth_protection' => 'Wealth Protection'
                        ];
                    @endphp

                    @foreach($categoryOptions as $key => $label)
                        <label class="flex items-center p-3 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer">
                            <input
                                type="checkbox"
                                name="categories[]"
                                value="{{ $key }}"
                                {{ in_array($key, old('categories', [])) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200"
                            >
                            <span class="ml-2 text-gray-700 text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('categories')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Base Premium -->
            <div class="mb-6">
                <label for="base_premium" class="block text-gray-700 text-sm font-semibold mb-2">
                    Base Premium (Rp) <span class="text-red-500">*</span>
                </label>
                <input
                    type="number"
                    id="base_premium"
                    name="base_premium"
                    value="{{ old('base_premium') }}"
                    min="0"
                    step="1000"
                    required
                    placeholder="e.g., 5000000"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('base_premium') border-red-500 @enderror"
                >
                <p class="text-xs text-gray-500 mt-1">Annual premium amount in Indonesian Rupiah</p>
                @error('base_premium')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Active Status -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        name="active"
                        value="1"
                        {{ old('active', true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200"
                    >
                    <span class="ml-2 text-gray-700">Product is active (available for recommendations)</span>
                </label>
            </div>

            <!-- Buttons -->
            <div class="flex justify-between">
                <a href="{{ route('admin.products.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 transition">
                    Cancel
                </a>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition">
                    Create Product
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
