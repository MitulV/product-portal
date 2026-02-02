@extends('app')

@section('title', 'Request a Quote - ' . $product->card_title . ' - PowerGen')

@section('content')
    <div class="min-h-screen bg-slate-50">
        <!-- Top Navigation -->
        <nav class="bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <a href="{{ route('products.index') }}"
                    class="inline-flex items-center text-slate-600 hover:text-slate-900 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Products
                </a>
            </div>
        </nav>

        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-green-800 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-red-800 font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900 mb-2">Request a Quote</h1>
                <p class="text-slate-600">
                    Product: <strong>{{ $product->card_title }}</strong>
                </p>
            </div>

            <!-- Product Summary Card -->
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6 mb-8">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">Product Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if ($product->isPublicField('brand') && $product->brand)
                        <div>
                            <span class="text-sm text-slate-500">Brand</span>
                            <p class="font-medium text-slate-900">{{ $product->brand }}</p>
                        </div>
                    @endif
                    @if ($product->isPublicField('model_number') && $product->model_number)
                        <div>
                            <span class="text-sm text-slate-500">Model Number</span>
                            <p class="font-medium text-slate-900">{{ $product->model_number }}</p>
                        </div>
                    @endif
                    @if ($product->isPublicField('voltage') && $product->voltage)
                        <div>
                            <span class="text-sm text-slate-500">Voltage</span>
                            <p class="font-medium text-slate-900">{{ $product->voltage }}</p>
                        </div>
                    @endif
                    @if ($product->isPublicField('phase') && $product->phase)
                        <div>
                            <span class="text-sm text-slate-500">Phase</span>
                            <p class="font-medium text-slate-900">{{ $product->phase }}</p>
                        </div>
                    @endif
                    @if ($product->isPublicField('enclosure_type') && $product->enclosure_type)
                        <div>
                            <span class="text-sm text-slate-500">Enclosure Type</span>
                            <p class="font-medium text-slate-900">{{ $product->enclosure_type }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Inquiry Form -->
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
                <h2 class="text-xl font-semibold text-slate-900 mb-6">Contact Information</h2>

                <form method="POST" action="{{ route('products.inquiry.submit', $product) }}">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            @error('name')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            @error('email')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">
                                Phone <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            @error('phone')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="company" class="block text-sm font-medium text-slate-700 mb-1">
                                Company <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="company" name="company" value="{{ old('company') }}" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            @error('company')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-slate-700 mb-1">
                                Message
                            </label>
                            <textarea id="message" name="message" rows="5"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Tell us about your power generation needs...">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex gap-4">
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                            Submit Inquiry
                        </button>
                        <a href="{{ route('products.show', $product->showRouteParameters()) }}"
                            class="px-6 py-3 bg-slate-200 text-slate-700 rounded-lg font-semibold hover:bg-slate-300 transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
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
