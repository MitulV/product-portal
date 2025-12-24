@extends('admin.layout')

@section('title', 'Admin Dashboard')

@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <a 
        href="{{ route('admin.products.index') }}"
        class="bg-white p-6 rounded-xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer"
    >
        <h3 class="text-sm font-medium text-slate-500 mb-2">Total Products</h3>
        <p class="text-3xl font-bold text-slate-800">{{ $totalProducts }}</p>
    </a>
   
    <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-sm">
        <h3 class="text-sm font-medium text-slate-500 mb-2">System Status</h3>
        <div class="flex items-center gap-2 mt-1">
            <span class="flex h-2.5 w-2.5 rounded-full bg-green-500"></span>
            <span class="text-sm font-medium text-slate-700">Healthy</span>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-100 shadow-sm p-8 text-center min-h-[400px] flex flex-col items-center justify-center">
    <div class="h-16 w-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" class="w-8 h-8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
        </svg>
    </div>
    <h3 class="text-lg font-semibold text-slate-900 mb-2">Welcome to your new Admin Panel</h3>
    <p class="text-slate-500 max-w-sm">
        Manage your inventory, track products, and organize stock from this central dashboard.
    </p>
</div>
@endsection

