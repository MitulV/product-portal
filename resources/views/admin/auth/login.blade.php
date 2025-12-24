@extends('app')

@section('title', 'Admin Login')

@section('content')
<div class="min-h-screen bg-slate-900 text-white overflow-hidden relative selection:bg-indigo-500 selection:text-white flex items-center justify-center p-6">
    <!-- Background Gradients -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
        <div class="absolute top-[10%] left-[20%] w-[40%] h-[40%] rounded-full bg-blue-600/10 blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-[10%] right-[20%] w-[40%] h-[40%] rounded-full bg-indigo-600/10 blur-[100px] animate-pulse delay-700"></div>
    </div>

    <div class="relative z-10 w-full max-w-md">
        <div class="text-center mb-8">
            <a href="/" class="inline-block">
                <h2 class="text-3xl font-light tracking-tight">
                    Power<span class="font-bold text-blue-500">Gen</span>
                </h2>
            </a>
        </div>

        <div class="bg-slate-950/50 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-2xl">
            <h3 class="text-xl font-semibold mb-6 text-center text-slate-200">Sign In to your account</h3>

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Email Address</label>
                    <input 
                        type="email" 
                        name="email"
                        value="{{ old('email') }}"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="name@company.com"
                        autofocus
                        required
                    />
                    @error('email')
                    <div class="text-red-400 text-sm mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Password</label>
                    <input 
                        type="password" 
                        name="password"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="••••••••"
                        required
                    />
                    @error('password')
                    <div class="text-red-400 text-sm mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="remember"
                            class="rounded bg-white/10 border-white/20 text-blue-500 focus:ring-offset-slate-900 focus:ring-blue-500"
                        />
                        <span class="text-sm text-slate-400 select-none">Remember me</span>
                    </label>
                    <a href="#" class="text-sm text-blue-400 hover:text-blue-300 transition">Forgot password?</a>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-3 rounded-lg transition-all shadow-lg shadow-blue-500/20"
                >
                    Sign In
                </button>
            </form>
        </div>
        
        <p class="text-center text-slate-500 text-sm mt-8">
            &copy; {{ date('Y') }} PowerGen Stock Inventory
        </p>
    </div>
</div>
@endsection

