<!-- Header -->
<header class="bg-white shadow-sm border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div>
            <a href="{{ route('products.index') }}"
                class="text-blue-600 hover:text-blue-800 text-sm mb-2 inline-block">
                ← Back to Products
            </a>
            <h1 class="text-3xl font-bold text-slate-900">
                {{ $product->unit_id ?? "Product #{$product->id}" }}
            </h1>
        </div>
    </div>
</header>

