@extends('app')

@section('title', $product->card_title . ' - PowerGen')

@section('content')
    <div>
        @include('products.partials.navigation')
        @include('products.partials.header')
        @include('products.partials.messages')

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Product Details -->
                <div class="lg:col-span-2 space-y-6">
                    @php
                        $images = $product->galleries->where('file_type', 'image');
                        $documents = $product->galleries->where('file_type', 'document');
                    @endphp

                    @include('products.partials.image-slider', ['images' => $images])
                    @include('products.partials.product-details')
                    @include('products.partials.documents', ['documents' => $documents])
                </div>

                @include('products.partials.sidebar')
            </div>
        </main>

        @include('products.partials.footer')
    </div>
@endsection
