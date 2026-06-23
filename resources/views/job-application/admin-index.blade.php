@extends('layouts.app')

@section('title', 'Applications')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-8">

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-4 md:mb-0">Job Applications</h1>

            <form action="{{ route('admin.job-application.admin.index') }}" method="GET"
                class="flex w-full md:w-1/3 shadow-sm rounded-md">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search name, email, or phone..."
                    class="w-full rounded-l-md border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 px-4 py-2 border">
                <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded-r-md hover:bg-blue-700 transition font-semibold">
                    Search
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Applicant</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Contact Info</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Education</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status</th>
                            <th scope="col" colspan="2"
                                class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($applications as $app)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ $app->full_name }}</div>
                                    <div class="text-xs text-gray-500">Applied: {{ $app->created_at->format('M d, Y') }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $app->email }}</div>
                                    <div class="text-sm text-gray-500">{{ $app->phone }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="capitalize">{{ str_replace('_', ' ', $app->education_level) }}</span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        // Assign colors based on status
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'reviewed' => 'bg-blue-100 text-blue-800',
                                            'accepted' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                        ];
                                        $colorClass = $statusColors[$app->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full {{ $colorClass }} capitalize">
                                        {{ $app->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-3">

                                        <form action="{{ route('admin.job-application.admin.delete', $app->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this application? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900 font-semibold bg-red-50 px-3 py-2 rounded transition">
                                                Delete
                                            </button>
                                        </form>

                                        <a href="{{ route('admin.job-application.admin.show', $app->id) }}"
                                            class="text-blue-600 hover:text-blue-900 font-semibold bg-blue-50 px-3 py-2 rounded transition">
                                            View Details &rarr;
                                        </a>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-lg font-medium text-gray-900">No applications found.</p>
                                    @if(request('search'))
                                        <p class="mt-1">Try adjusting your search term or <a
                                                href="{{ route('job-application.admin.index') }}"
                                                class="text-blue-600 hover:underline">clear the search</a>.</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($applications->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $applications->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection