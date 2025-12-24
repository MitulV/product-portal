<!-- Product Details Card -->
<div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-2">
                {{ $product->unit_id ?? "Product #{$product->id}" }}
            </h2>
            @if ($product->brand)
                <p class="text-lg text-slate-600">{{ $product->brand }}</p>
            @endif
        </div>
        @if ($product->hold_status)
            <span class="px-3 py-1 rounded text-sm font-medium {{ $product->hold_status === 'Hold' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                {{ $product->hold_status }}
            </span>
        @endif
    </div>

    <!-- Key Specifications -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        @if ($product->model_number)
            <div class="border-b border-slate-200 pb-3">
                <div class="text-sm text-slate-500 mb-1">Model Number</div>
                <div class="font-semibold text-slate-900">{{ $product->model_number }}</div>
            </div>
        @endif
        @if ($product->serial_number)
            <div class="border-b border-slate-200 pb-3">
                <div class="text-sm text-slate-500 mb-1">Serial Number</div>
                <div class="font-semibold text-slate-900 font-mono text-sm">{{ $product->serial_number }}</div>
            </div>
        @endif
        @if ($product->voltage)
            <div class="border-b border-slate-200 pb-3">
                <div class="text-sm text-slate-500 mb-1">Voltage</div>
                <div class="font-semibold text-slate-900">{{ $product->voltage }}</div>
            </div>
        @endif
        @if ($product->phase)
            <div class="border-b border-slate-200 pb-3">
                <div class="text-sm text-slate-500 mb-1">Phase</div>
                <div class="font-semibold text-slate-900">{{ $product->phase }}</div>
            </div>
        @endif
        @if ($product->enclosure_type)
            <div class="border-b border-slate-200 pb-3">
                <div class="text-sm text-slate-500 mb-1">Enclosure Type</div>
                <div class="font-semibold text-slate-900">{{ $product->enclosure_type }}</div>
            </div>
        @endif
        @if ($product->controller_series)
            <div class="border-b border-slate-200 pb-3">
                <div class="text-sm text-slate-500 mb-1">Controller Series</div>
                <div class="font-semibold text-slate-900">{{ $product->controller_series }}</div>
            </div>
        @endif
    </div>

    <!-- Pricing -->
    @if ($product->total_cost || $product->tariff_cost)
        <div class="bg-blue-50 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-slate-900 mb-3">Pricing</h3>
            <div class="space-y-2">
                @if ($product->total_cost)
                    <div class="flex justify-between">
                        <span class="text-slate-600">Total Cost:</span>
                        <span class="text-2xl font-bold text-blue-600">
                            ${{ number_format($product->total_cost, 2) }}
                        </span>
                    </div>
                @endif
                @if ($product->tariff_cost)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Tariff Cost:</span>
                        <span class="font-semibold text-slate-900">
                            ${{ number_format($product->tariff_cost, 2) }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Additional Details -->
    <div class="space-y-4">
        <h3 class="font-semibold text-slate-900 text-lg">Additional Details</h3>
        
        <div class="grid grid-cols-2 gap-4 text-sm">
            @if ($product->salesman)
                <div>
                    <span class="text-slate-500">Salesman:</span>
                    <span class="ml-2 font-medium text-slate-900">{{ $product->salesman }}</span>
                </div>
            @endif
            @if ($product->hold_branch)
                <div>
                    <span class="text-slate-500">Hold Branch:</span>
                    <span class="ml-2 font-medium text-slate-900">{{ $product->hold_branch }}</span>
                </div>
            @endif
            @if ($product->enclosure)
                <div>
                    <span class="text-slate-500">Enclosure:</span>
                    <span class="ml-2 font-medium text-slate-900">{{ $product->enclosure }}</span>
                </div>
            @endif
            @if ($product->tank)
                <div>
                    <span class="text-slate-500">Tank:</span>
                    <span class="ml-2 font-medium text-slate-900">{{ $product->tank }}</span>
                </div>
            @endif
            @if ($product->breakers)
                <div>
                    <span class="text-slate-500">Breakers:</span>
                    <span class="ml-2 font-medium text-slate-900">{{ $product->breakers }}</span>
                </div>
            @endif
        </div>

        @if ($product->notes)
            <div class="mt-4">
                <div class="text-slate-500 text-sm mb-1">Notes</div>
                <div class="text-slate-900 whitespace-pre-wrap">{{ $product->notes }}</div>
            </div>
        @endif
    </div>
</div>

