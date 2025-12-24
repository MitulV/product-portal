@extends('admin.layout')

@section('title', 'Edit Product')

@section('header', 'Edit Product')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.products.index') }}" class="text-slate-500 hover:text-slate-700">← Back</a>
        <h2 class="text-2xl font-bold text-slate-800">Edit Product: {{ $product->unit_id }}</h2>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Unit ID -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Unit ID <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="unit_id"
                        value="{{ old('unit_id', $product->unit_id) }}"
                        class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        required
                    />
                    @error('unit_id')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Brand -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Brand</label>
                    <input 
                        type="text" 
                        name="brand"
                        value="{{ old('brand', $product->brand) }}"
                        class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>

                <!-- Model Number -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Model Number</label>
                    <input 
                        type="text" 
                        name="model_number"
                        value="{{ old('model_number', $product->model_number) }}"
                        class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>

                <!-- Serial Number -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Serial Number</label>
                    <input 
                        type="text" 
                        name="serial_number"
                        value="{{ old('serial_number', $product->serial_number) }}"
                        class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>
                
                <!-- Voltage -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Voltage</label>
                    <input 
                        type="text" 
                        name="voltage"
                        value="{{ old('voltage', $product->voltage) }}"
                        class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>

                <!-- Enclosure -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Enclosure</label>
                    <input 
                        type="text" 
                        name="enclosure"
                        value="{{ old('enclosure', $product->enclosure) }}"
                        class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                <textarea
                    name="notes"
                    class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 min-h-[100px]"
                >{{ old('notes', $product->notes) }}</textarea>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <a href="{{ route('admin.products.index') }}" class="px-4 py-2 text-slate-600 hover:text-slate-800 mr-4">Cancel</a>
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition"
                >
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

