import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function Edit({ product }) {
    const { data, setData, put, processing, errors } = useForm({
        unit_id: product.unit_id || '',
        brand: product.brand || '',
        model_number: product.model_number || '',
        serial_number: product.serial_number || '',
        voltage: product.voltage || '',
        enclosure: product.enclosure || '',
        notes: product.notes || '',
    });

    const submit = (e) => {
        e.preventDefault();
        put(`/admin/products/${product.id}`);
    };

    return (
        <AdminLayout>
            <Head title="Edit Product" />
            
            <div className="max-w-3xl mx-auto">
                 <div className="flex items-center gap-4 mb-6">
                    <Link href="/admin/products" className="text-slate-500 hover:text-slate-700">← Back</Link>
                    <h2 className="text-2xl font-bold text-slate-800">Edit Product: {product.unit_id}</h2>
                </div>

                <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {/* Unit ID */}
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Unit ID <span className="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    value={data.unit_id}
                                    onChange={e => setData('unit_id', e.target.value)}
                                    className="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                                />
                                {errors.unit_id && <div className="text-red-500 text-sm mt-1">{errors.unit_id}</div>}
                            </div>

                            {/* Brand */}
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Brand</label>
                                <input 
                                    type="text" 
                                    value={data.brand}
                                    onChange={e => setData('brand', e.target.value)}
                                    className="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>

                            {/* Model Number */}
                             <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Model Number</label>
                                <input 
                                    type="text" 
                                    value={data.model_number}
                                    onChange={e => setData('model_number', e.target.value)}
                                    className="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>

                            {/* Serial Number */}
                             <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Serial Number</label>
                                <input 
                                    type="text" 
                                    value={data.serial_number}
                                    onChange={e => setData('serial_number', e.target.value)}
                                    className="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            
                            {/* Voltage */}
                             <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Voltage</label>
                                <input 
                                    type="text" 
                                    value={data.voltage}
                                    onChange={e => setData('voltage', e.target.value)}
                                    className="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>

                             {/* Enclosure */}
                             <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Enclosure</label>
                                <input 
                                    type="text" 
                                    value={data.enclosure}
                                    onChange={e => setData('enclosure', e.target.value)}
                                    className="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                        </div>

                        {/* Notes */}
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                            <textarea
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                className="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 min-h-[100px]"
                            ></textarea>
                        </div>

                        <div className="flex justify-end pt-4 border-t border-slate-100">
                             <Link href="/admin/products" className="px-4 py-2 text-slate-600 hover:text-slate-800 mr-4">Cancel</Link>
                             <button 
                                type="submit" 
                                disabled={processing}
                                className="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition disabled:opacity-50"
                            >
                                {processing ? 'Saving...' : 'Save Changes'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
