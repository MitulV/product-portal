import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Dashboard({ totalProducts = 0 }) {
    return (
        <AdminLayout>
            <Head title="Admin Dashboard" />
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <Link 
                    href="/admin/products"
                    className="bg-white p-6 rounded-xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                >
                    <h3 className="text-sm font-medium text-slate-500 mb-2">Total Products</h3>
                    <p className="text-3xl font-bold text-slate-800">{totalProducts}</p>
                </Link>
               
                <div className="bg-white p-6 rounded-xl border border-slate-100 shadow-sm">
                    <h3 className="text-sm font-medium text-slate-500 mb-2">System Status</h3>
                    <div className="flex items-center gap-2 mt-1">
                        <span className="flex h-2.5 w-2.5 rounded-full bg-green-500"></span>
                        <span className="text-sm font-medium text-slate-700">Healthy</span>
                    </div>
                </div>
            </div>

            <div className="bg-white rounded-xl border border-slate-100 shadow-sm p-8 text-center min-h-[400px] flex flex-col items-center justify-center">
                <div className="h-16 w-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-8 h-8">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                    </svg>
                </div>
                <h3 className="text-lg font-semibold text-slate-900 mb-2">Welcome to your new Admin Panel</h3>
                <p className="text-slate-500 max-w-sm">
                    Manage your inventory, track products, and organize stock from this central dashboard.
                </p>
            </div>
        </AdminLayout>
    );
}
