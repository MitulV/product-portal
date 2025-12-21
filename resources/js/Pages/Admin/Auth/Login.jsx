import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post('/admin/login');
    };

    return (
        <div className="min-h-screen bg-slate-900 text-white overflow-hidden relative selection:bg-indigo-500 selection:text-white flex items-center justify-center p-6">
            <Head title="Admin Login" />
            
             {/* Background Gradients */}
             <div className="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
                <div className="absolute top-[10%] left-[20%] w-[40%] h-[40%] rounded-full bg-blue-600/10 blur-[100px] animate-pulse"></div>
                <div className="absolute bottom-[10%] right-[20%] w-[40%] h-[40%] rounded-full bg-indigo-600/10 blur-[100px] animate-pulse delay-700"></div>
            </div>

            <div className="relative z-10 w-full max-w-md">
                <div className="text-center mb-8">
                     <Link href="/" className="inline-block">
                        <h2 className="text-3xl font-light tracking-tight">
                            Power<span className="font-bold text-blue-500">Gen</span>
                        </h2>
                     </Link>
                </div>

                <div className="bg-slate-950/50 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-2xl">
                    <h3 className="text-xl font-semibold mb-6 text-center text-slate-200">Sign In to your account</h3>

                    <form onSubmit={submit} className="space-y-6">
                        <div>
                            <label className="block text-sm font-medium text-slate-400 mb-2">Email Address</label>
                            <input 
                                type="email" 
                                value={data.email}
                                onChange={e => setData('email', e.target.value)}
                                className="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                placeholder="name@company.com"
                                autoFocus
                            />
                            {errors.email && <div className="text-red-400 text-sm mt-2">{errors.email}</div>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-400 mb-2">Password</label>
                            <input 
                                type="password" 
                                value={data.password}
                                onChange={e => setData('password', e.target.value)}
                                className="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                placeholder="••••••••"
                            />
                             {errors.password && <div className="text-red-400 text-sm mt-2">{errors.password}</div>}
                        </div>

                        <div className="flex items-center justify-between">
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    checked={data.remember}
                                    onChange={e => setData('remember', e.target.checked)}
                                    className="rounded bg-white/10 border-white/20 text-blue-500 focus:ring-offset-slate-900 focus:ring-blue-500"
                                />
                                <span className="text-sm text-slate-400 select-none">Remember me</span>
                            </label>
                            <a href="#" className="text-sm text-blue-400 hover:text-blue-300 transition">Forgot password?</a>
                        </div>

                        <button 
                            type="submit" 
                            disabled={processing}
                            className="w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-3 rounded-lg transition-all shadow-lg shadow-blue-500/20 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {processing ? 'Signing In...' : 'Sign In'}
                        </button>
                    </form>
                </div>
                
                <p className="text-center text-slate-500 text-sm mt-8">
                    &copy; {new Date().getFullYear()} PowerGen Stock Inventory
                </p>
            </div>
        </div>
    );
}
