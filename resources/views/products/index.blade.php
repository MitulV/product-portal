@extends('app')

@section('title', 'Power Generation Equipment - PowerGen')

@section('content')
    <style>
        .filter-select {
            width: 100%;
            padding-left: 16px;
            padding-right: 40px;
        }

        @media (min-width: 768px) {
            .filter-select-brand {
                width: 140px;
            }

            .filter-select-voltage {
                width: 145px;
            }

            .filter-select-phase {
                width: 135px;
            }
        }
    </style>
    <div x-data="{ search: '{{ $filters['search'] ?? '' }}', voltage: '{{ $filters['voltage'] ?? '' }}', brand: '{{ $filters['brand'] ?? '' }}', phase: '{{ $filters['phase'] ?? '' }}' }">
        <!-- Top Navigation -->
        <nav class="bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <a href="/" class="text-2xl font-bold text-slate-900 hover:text-blue-600 transition">
                    Power<span class="text-blue-600">Gen</span>
                </a>
            </div>
        </nav>

        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">Power Generation Equipment</h1>
                    <p class="text-slate-600 mt-1">Browse our inventory of generators and power systems</p>
                </div>
            </div>
        </header>

        <!-- Search Bar -->
        <div class="bg-slate-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <form method="GET" action="{{ route('products.index') }}" class="flex flex-col md:flex-row gap-3">
                    <div class="flex-1 relative">
                        <input type="text" name="search" x-model="search"
                            placeholder="Search by kW, Brand, Model, Voltage, Tank, Enclosure, Phase, Title..."
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" />
                        @if (isset($filters['search']) && $filters['search'])
                            <span
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded">
                                Active
                            </span>
                        @endif
                    </div>
                    <div class="relative">
                        <select name="brand" x-model="brand" onchange="this.form.submit()"
                            class="filter-select filter-select-brand py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent bg-white appearance-none cursor-pointer">
                            <option value="">All Brands</option>
                            @foreach ($availableBrands ?? [] as $b)
                                <option value="{{ $b }}"
                                    {{ isset($filters['brand']) && $filters['brand'] == $b ? 'selected' : '' }}>
                                    {{ $b }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none z-10">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    <div class="relative">
                        <select name="voltage" x-model="voltage" onchange="this.form.submit()"
                            class="filter-select filter-select-voltage py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent bg-white appearance-none cursor-pointer">
                            <option value="">All Voltages</option>
                            @foreach ($availableVoltages as $volt)
                                <option value="{{ $volt }}"
                                    {{ isset($filters['voltage']) && $filters['voltage'] == $volt ? 'selected' : '' }}>
                                    {{ $volt }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none z-10">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    <div class="relative">
                        <select name="phase" x-model="phase" onchange="this.form.submit()"
                            class="filter-select filter-select-phase py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent bg-white appearance-none cursor-pointer">
                            <option value="">All Phases</option>
                            @foreach ($availablePhases ?? [] as $p)
                                <option value="{{ $p }}"
                                    {{ isset($filters['phase']) && $filters['phase'] == $p ? 'selected' : '' }}>
                                    {{ $p }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none z-10">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    <input type="hidden" name="sort_by" value="{{ $filters['sort_by'] ?? 'id' }}">
                    <input type="hidden" name="sort_order" value="{{ $filters['sort_order'] ?? 'desc' }}">
                    <button type="submit"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition whitespace-nowrap">
                        Search
                    </button>
                    @if (
                        (isset($filters['search']) && $filters['search']) ||
                            (isset($filters['voltage']) && $filters['voltage']) ||
                            (isset($filters['brand']) && $filters['brand']) ||
                            (isset($filters['phase']) && $filters['phase']))
                        <a href="{{ route('products.index', ['sort_by' => $filters['sort_by'] ?? 'id', 'sort_order' => $filters['sort_order'] ?? 'desc']) }}"
                            class="px-6 py-3 bg-slate-200 text-slate-700 rounded-lg font-medium hover:bg-slate-300 transition whitespace-nowrap">
                            Clear All
                        </a>
                    @endif
                </form>
                @if (
                    (isset($filters['search']) && $filters['search']) ||
                        (isset($filters['voltage']) && $filters['voltage']) ||
                        (isset($filters['brand']) && $filters['brand']) ||
                        (isset($filters['phase']) && $filters['phase']))
                    <div class="mt-3 text-xs text-slate-600 flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span class="font-medium">Active filters:</span>
                        @if (isset($filters['search']) && $filters['search'])
                            <span>Search: "{{ $filters['search'] }}"</span>
                        @endif
                        @if (isset($filters['brand']) && $filters['brand'])
                            <span>Brand: {{ $filters['brand'] }}</span>
                        @endif
                        @if (isset($filters['voltage']) && $filters['voltage'])
                            <span>Voltage: {{ $filters['voltage'] }}</span>
                        @endif
                        @if (isset($filters['phase']) && $filters['phase'])
                            <span>Phase: {{ $filters['phase'] }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Products Grid -->
            @if ($products->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($products as $product)
                        <div
                            class="bg-white rounded-lg border border-slate-200 shadow-sm hover:shadow-md transition-shadow overflow-hidden flex flex-col">
                            @if ($product->thumbnail && $product->thumbnail->file_url)
                                <a href="{{ route('products.show', $product->showRouteParameters()) }}"
                                    class="block aspect-[16/10] bg-slate-100 overflow-hidden">
                                    <img src="{{ $product->thumbnail->file_url }}"
                                        alt="{{ $product->unit_id ?? 'Product' }}"
                                        class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-200"
                                        loading="lazy" />
                                </a>
                            @endif
                            <div class="p-6 flex flex-col flex-1">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-bold text-slate-900 mb-1">
                                            {{ $product->card_title }}
                                        </h3>
                                    </div>
                                    @if ($product->hold_status)
                                        <span
                                            class="px-2 py-1 rounded text-xs font-medium {{ $product->hold_status === 'Hold' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $product->hold_status }}
                                        </span>
                                    @endif
                                </div>

                                <div class="space-y-2 mb-4 flex-1">
                                    @if ($product->isPublicField('model_number') && $product->model_number)
                                        <div class="flex items-center text-sm text-slate-600">
                                            <span class="font-medium w-24">Model:</span>
                                            <span>{{ $product->model_number }}</span>
                                        </div>
                                    @endif
                                    @if ($product->isPublicField('voltage') && $product->voltage)
                                        <div class="flex items-center text-sm text-slate-600">
                                            <span class="font-medium w-24">Voltage:</span>
                                            <span>{{ $product->voltage }}</span>
                                        </div>
                                    @endif
                                    @if ($product->isPublicField('phase') && $product->phase)
                                        <div class="flex items-center text-sm text-slate-600">
                                            <span class="font-medium w-24">Phase:</span>
                                            <span>{{ $product->phase }}</span>
                                        </div>
                                    @endif
                                    @if ($product->isPublicField('enclosure_type') && $product->enclosure_type)
                                        <div class="flex items-center text-sm text-slate-600">
                                            <span class="font-medium w-24">Enclosure:</span>
                                            <span>{{ $product->enclosure_type }}</span>
                                        </div>
                                    @endif
                                </div>

                                <a href="{{ route('products.show', $product->showRouteParameters()) }}"
                                    class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition mt-auto">
                                    View Details
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-lg border border-slate-200 p-12 text-center">
                    <p class="text-slate-500 text-lg">No products found.</p>
                    @if (isset($filters['search']) && $filters['search'])
                        <a href="{{ route('products.index') }}"
                            class="mt-4 text-blue-600 hover:text-blue-800 inline-block">
                            Clear search and show all products
                        </a>
                    @endif
                </div>
            @endif

            <!-- Pagination -->
            @if ($products->hasPages())
                <div class="mt-8 flex flex-col items-center gap-4">
                    <div class="text-sm text-slate-600">
                        Page {{ $products->currentPage() }} of {{ $products->lastPage() }}
                    </div>

                    <div class="flex items-center gap-2 flex-wrap justify-center">
                        @if ($products->onFirstPage())
                            <span
                                class="px-3 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed">««</span>
                            <span
                                class="px-3 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed">‹</span>
                        @else
                            <a href="{{ $products->url(1) }}"
                                class="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition">««</a>
                            <a href="{{ $products->previousPageUrl() }}"
                                class="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition">‹</a>
                        @endif

                        @foreach ($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}"
                                class="px-4 py-2 rounded border transition {{ $page == $products->currentPage() ? 'bg-blue-600 text-white border-blue-600 font-semibold' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' }}">
                                {{ $page }}
                            </a>
                        @endforeach

                        @if ($products->hasMorePages())
                            <a href="{{ $products->nextPageUrl() }}"
                                class="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition">›</a>
                            <a href="{{ $products->url($products->lastPage()) }}"
                                class="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition">»»</a>
                        @else
                            <span
                                class="px-3 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed">›</span>
                            <span
                                class="px-3 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed">»»</span>
                        @endif
                    </div>
                </div>
            @endif
        </main>

        <!-- Footer -->
        <footer class="bg-slate-900 text-white mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center">
                    <h3 class="text-xl font-bold mb-2">Power<span class="text-blue-400">Gen</span></h3>
                    <p class="text-slate-400 text-sm">
                        &copy; {{ date('Y') }} PowerGen. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
@endsection
