<!-- Product Details Card -->
<div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-2">
                {{ $product->unit_id ?? "Product #{$product->id}" }}
            </h2>
            @if ($product->isPublicField('brand') && $product->brand)
                <p class="text-lg text-slate-600">{{ $product->brand }}</p>
            @endif
        </div>
        @if ($product->hold_status && $product->hold_status !== 'Hold')
            <span class="px-3 py-1 rounded text-sm font-medium bg-green-100 text-green-800">
                {{ $product->hold_status }}
            </span>
        @endif
    </div>

    @php
        $publicFields = $product->getPublicFields();
        $hasPublicFields = false;
        
        // Check if any public fields have values
        foreach ($publicFields as $field) {
            if ($product->$field !== null && $product->$field !== '') {
                $hasPublicFields = true;
                break;
            }
        }
    @endphp

    @if ($hasPublicFields)
        <!-- Key Specifications -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            @foreach ($publicFields as $field)
                @if ($product->$field !== null && $product->$field !== '')
                    <div class="border-b border-slate-200 pb-3">
                        <div class="text-sm text-slate-500 mb-1">{{ $product->getFieldLabel($field) }}</div>
                        <div class="font-semibold text-slate-900">
                            @if (in_array($field, ['est_completion_date', 'ship_date']) && $product->$field)
                                {{ $product->$field->format('M d, Y') }}
                            @elseif (in_array($field, ['power', 'engine_speed', 'radiator_design_temp', 'frequency', 'full_load_amps', 'amperage', 'kw']))
                                {{ $field === 'kw' ? (string) (int) round($product->$field, 0) . ' Kw' : number_format($product->$field) }}
                            @elseif ($field === 'tech_spec' && filter_var($product->$field, FILTER_VALIDATE_URL))
                                <a href="{{ $product->$field }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                                    View Tech Spec
                                </a>
                            @else
                                {{ $product->$field }}
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    @if ($product->isPublicField('description') && $product->description)
        <div class="mb-6">
            <h3 class="font-semibold text-slate-900 text-lg mb-3">Description</h3>
            <div class="text-slate-700 whitespace-pre-wrap">{{ $product->description }}</div>
        </div>
    @endif
</div>
