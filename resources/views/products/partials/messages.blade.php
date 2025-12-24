<!-- Success/Error Messages -->
@if (session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">
            {{ session('success') }}
        </div>
    </div>
@endif

@if (session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
            {{ session('error') }}
        </div>
    </div>
@endif

