@extends('layouts.app')

@section('title', 'Manage Products')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manage Products</h1>
            <p class="text-gray-600">Insurance products available in the system</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-semibold">
            + Create New Product
        </a>
    </div>

    <!-- Products Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($products as $product)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-xl font-bold text-gray-900">{{ $product->name }}</h3>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $product->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $product->active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $product->description }}</p>

                    <div class="mb-4">
                        <div class="text-sm text-gray-500 mb-1">Base Premium</div>
                        <div class="text-2xl font-bold text-blue-600">Rp {{ number_format($product->base_premium, 0, ',', '.') }}</div>
                    </div>

                    <div class="mb-4">
                        <div class="text-sm text-gray-500 mb-2">Categories</div>
                        <div class="flex flex-wrap gap-1">
                            @foreach($product->categories as $category)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">{{ $category }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="text-sm text-gray-500 mb-4">
                        Used in {{ $product->cases_count }} consultations
                    </div>

                    <div class="flex space-x-2">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="flex-1 bg-blue-600 text-white text-center px-4 py-2 rounded hover:bg-blue-700 transition text-sm">
                            Edit
                        </a>
                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition text-sm">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12">
                <div class="text-6xl mb-4">🏢</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Products Yet</h3>
                <p class="text-gray-600 mb-4">Create your first insurance product to get started.</p>
                <a href="{{ route('admin.products.create') }}" class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition">
                    Create First Product
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
