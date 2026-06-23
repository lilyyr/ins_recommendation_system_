@extends('layouts.app')

@section('title', 'Consultations')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900">Consultations</h1>
            <div class="flex gap-3">
                <a href="{{ route('consultations.create') }}"
                    class="bg-gradient-to-r from-yellow-600 to-yellow-500 text-white px-6 py-3 rounded hover:from-yellow-700 hover:to-yellow-600">
                    New Consultation
                </a>
            </div>

        </div>

        <!-- Search & Sort bar -->
        <div class="bg-white border border-gray-200 p-4 rounded-lg mb-6 flex flex-col sm:flex-row gap-3">

            <!-- Search input -->
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                    </svg>
                </span>
                <input type="text" id="search-input" placeholder="Search by customer name or product..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600 text-sm">
            </div>

            <!-- Sort dropdown -->
            <div class="sm:w-44">
                <select id="sort-select"
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:border-yellow-600 focus:ring-1 focus:ring-yellow-600 text-sm">
                    <option value="latest">Latest</option>
                    <option value="oldest">Oldest</option>
                </select>
            </div>

        </div>

        <!-- Results count (shown after first load) -->
        <p id="results-label" class="hidden text-sm text-gray-500 mb-3"></p>

        <!-- Loading state -->
        <div id="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-yellow-600"></div>
            <p class="mt-4 text-gray-600">Loading consultations...</p>
        </div>

        <!-- Consultations list -->
        <div id="consultations-list" class="hidden space-y-4"></div>

        <!-- Empty state -->
        <div id="empty-state" class="hidden text-center py-12 bg-white border border-gray-200 rounded-lg">
            <p class="text-gray-500 text-lg mb-1">No consultations found.</p>
            <p class="text-sm text-gray-400" id="empty-hint">Try a different search term.</p>
        </div>

        <!-- Pagination -->
        <div id="pagination" class="hidden mt-6"></div>
    </div>

    <script>
        const apiToken = document.querySelector('meta[name="api-token"]').content;

        let debounceTimer = null;

        // Read current control values
        function getParams(page = 1) {
            return {
                page,
                search: document.getElementById('search-input').value.trim(),
                sort: document.getElementById('sort-select').value,
            };
        }

        async function loadConsultations(page = 1) {
            const { search, sort } = getParams(page);

            // Build query string
            const qs = new URLSearchParams({ page, sort });
            if (search) qs.set('search', search);

            // Show loading only on first load
            const list = document.getElementById('consultations-list');
            if (list.classList.contains('hidden')) {
                document.getElementById('loading').classList.remove('hidden');
            } else {
                list.style.opacity = '0.4';
            }

            document.getElementById('empty-state').classList.add('hidden');
            document.getElementById('pagination').classList.add('hidden');

            try {
                const response = await fetch(`/api/recommendations?${qs}`, {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${apiToken}`
                    }
                });

                if (!response.ok) throw new Error('Failed to load consultations');

                const result = await response.json();

                // Hide loading
                document.getElementById('loading').classList.add('hidden');
                list.style.opacity = '1';

                if (result.data.length === 0) {
                    list.classList.add('hidden');
                    const hint = document.getElementById('empty-hint');
                    hint.textContent = search
                        ? `No results for "${search}". Try a different keyword.`
                        : 'No consultations yet. Create your first one!';
                    document.getElementById('empty-state').classList.remove('hidden');
                    document.getElementById('results-label').classList.add('hidden');
                    return;
                }

                displayConsultations(result.data);
                displayPagination(result.pagination);

                const label = document.getElementById('results-label');
                label.textContent = search
                    ? `${result.pagination.total} result(s) for "${search}"`
                    : `${result.pagination.total} consultation(s) total`;
                label.classList.remove('hidden');

            } catch (error) {
                console.error('Error:', error);
                document.getElementById('loading').innerHTML = `
                <div class="text-red-600">
                    <p class="text-lg font-semibold">Error loading consultations</p>
                    <p class="text-sm">${error.message}</p>
                </div>
            `;
            }
        }

        function displayConsultations(consultations) {
            const container = document.getElementById('consultations-list');
            container.classList.remove('hidden');

            container.innerHTML = consultations.map(consultation => `
            <div class="bg-white border border-gray-200 p-6 rounded-lg hover:border-yellow-500 transition">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">${consultation.customer.name}</h3>
                        <p class="text-gray-500 text-sm mt-1">
                            Age: ${consultation.customer.age}
                            &nbsp;·&nbsp;
                            BMI: ${parseFloat(consultation.bmi).toFixed(1)}
                        </p>
                        <p class="text-sm text-gray-500 mt-2">
                            Recommended:
                            <span class="font-semibold text-yellow-600">${consultation.product.name}</span>
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0 ml-4">
                        <p class="text-xs text-gray-400 mb-2">${new Date(consultation.created_at).toLocaleDateString()}</p>
                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded text-sm font-semibold">
                                ${Math.round((parseFloat(consultation.euclidean_score) + parseFloat(consultation.weighted_euclidean_score) + parseFloat(consultation.random_forest_score)) / 3 * 100)}% Match
                        </span>
                    </div>
                </div>
                <div class="mt-4 flex gap-4">
                    <a href="/consultations/${consultation.id}"
                        class="text-yellow-600 hover:text-yellow-700 font-semibold text-sm">
                        View Details →
                    </a>
                    <a href="/consultations/${consultation.id}/process"
                        class="text-gray-500 hover:text-gray-700 text-sm">
                        View Process
                    </a>
                </div>
            </div>
            `).join('');
        }

        function displayPagination(pagination) {
            const container = document.getElementById('pagination');
            container.classList.remove('hidden');

            if (pagination.last_page <= 1) {
                container.classList.add('hidden');
                return;
            }

            container.classList.remove('hidden');

            let pages = '';
            for (let i = 1; i <= pagination.last_page; i++) {
                const active = i === pagination.current_page;
                pages += `
                <button onclick="loadConsultations(${i})"
                        class="px-4 py-2 ${active ? 'bg-yellow-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'} border border-gray-300">
                    ${i}
                </button>
            `;
            }

            container.innerHTML = `<div class="flex gap-1 justify-center">${pages}</div>`;
        }

        // Debounce search input
        document.getElementById('search-input').addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => loadConsultations(1), 350);
        });

        // Instant sort change
        document.getElementById('sort-select').addEventListener('change', () => {
            loadConsultations(1);
        });

        loadConsultations();
    </script>
@endsection
