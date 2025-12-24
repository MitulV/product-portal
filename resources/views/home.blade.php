@extends('app')

@section('title', 'Coming Soon')

@section('content')
<div class="min-h-screen bg-slate-900 text-white overflow-hidden relative selection:bg-indigo-500 selection:text-white">
    <!-- Background Gradients -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-blue-600/20 blur-[120px] mix-blend-screen animate-pulse"></div>
        <div class="absolute top-[20%] -right-[10%] w-[40%] h-[40%] rounded-full bg-indigo-600/20 blur-[100px] mix-blend-screen animate-pulse delay-1000"></div>
        <div class="absolute -bottom-[20%] left-[20%] w-[60%] h-[60%] rounded-full bg-violet-600/10 blur-[120px] mix-blend-screen"></div>
    </div>

    <!-- Content Container -->
    <div class="relative z-10 min-h-screen flex flex-col items-center justify-center p-6">
        
        <!-- Glass Card -->
        <div class="w-full max-w-4xl p-1 bg-gradient-to-br from-white/10 to-white/0 rounded-3xl backdrop-blur-2xl border border-white/10 shadow-2xl">
            <div class="bg-slate-950/50 rounded-[22px] p-12 md:p-20 text-center relative overflow-hidden group">
               
               <!-- Hover effect glow -->
               <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-1000 pointer-events-none"></div>

                <a href="/admin" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white transition-all duration-300 mb-8 backdrop-blur-md">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    System Operational
                </a>

                <h1 class="text-5xl md:text-7xl font-light tracking-tight text-transparent bg-clip-text bg-gradient-to-b from-white to-white/60 mb-6 drop-shadow-sm">
                    Power Generation <br />
                    <span class="font-semibold text-white">Stock Inventory</span>
                </h1>

                <p class="text-lg md:text-xl text-slate-400 max-w-2xl mx-auto leading-relaxed mb-12">
                    We are building a state-of-the-art inventory management portal. 
                    <br class="hidden md:block" />
                    Experience the future of stock tracking very soon.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <button disabled class="px-8 py-4 rounded-xl bg-white text-slate-900 font-semibold text-lg opacity-50 cursor-not-allowed">
                        Access Portal
                    </button>
                    <a href="/admin/login" class="px-8 py-4 rounded-xl bg-white/5 border border-white/10 text-white font-semibold text-lg hover:bg-white/10 transition-all active:scale-95">
                        Admin Login
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="absolute bottom-6 text-slate-500 text-sm font-medium">
            &copy; {{ date('Y') }} PowerGen. All rights reserved.
        </footer>
    </div>
</div>
@endsection

