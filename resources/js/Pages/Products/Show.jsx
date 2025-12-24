import React, { useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';

export default function Show({ product }) {
    const { flash } = usePage().props;
    const [showInquiryForm, setShowInquiryForm] = useState(false);
    
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        phone: '',
        company: '',
        message: '',
    });

    const handleInquirySubmit = (e) => {
        e.preventDefault();
        post(`/products/${product.id}/inquiry`, {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setShowInquiryForm(false);
            },
        });
    };

    return (
        <>
            <Head title={`${product.unit_id || 'Product'} - PowerGen`} />
            
            {/* Header */}
            <header className="bg-white shadow-sm border-b border-slate-200">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div>
                        <Link 
                            href="/products"
                            className="text-blue-600 hover:text-blue-800 text-sm mb-2 inline-block"
                        >
                            ← Back to Products
                        </Link>
                        <h1 className="text-3xl font-bold text-slate-900">
                            {product.unit_id || `Product #${product.id}`}
                        </h1>
                    </div>
                </div>
            </header>

            {/* Success/Error Messages */}
            {flash?.success && (
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
                    <div className="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">
                        {flash.success}
                    </div>
                </div>
            )}
            {flash?.error && (
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
                    <div className="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
                        {flash.error}
                    </div>
                </div>
            )}

            {/* Main Content */}
            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Product Details */}
                    <div className="lg:col-span-2">
                        <div className="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
                            <div className="flex items-start justify-between mb-6">
                                <div>
                                    <h2 className="text-2xl font-bold text-slate-900 mb-2">
                                        {product.unit_id || `Product #${product.id}`}
                                    </h2>
                                    {product.brand && (
                                        <p className="text-lg text-slate-600">{product.brand}</p>
                                    )}
                                </div>
                                {product.hold_status && (
                                    <span className={`px-3 py-1 rounded text-sm font-medium ${
                                        product.hold_status === 'Hold' 
                                            ? 'bg-red-100 text-red-800' 
                                            : 'bg-green-100 text-green-800'
                                    }`}>
                                        {product.hold_status}
                                    </span>
                                )}
                            </div>

                            {/* Key Specifications */}
                            <div className="grid grid-cols-2 gap-4 mb-6">
                                {product.model_number && (
                                    <div className="border-b border-slate-200 pb-3">
                                        <div className="text-sm text-slate-500 mb-1">Model Number</div>
                                        <div className="font-semibold text-slate-900">{product.model_number}</div>
                                    </div>
                                )}
                                {product.serial_number && (
                                    <div className="border-b border-slate-200 pb-3">
                                        <div className="text-sm text-slate-500 mb-1">Serial Number</div>
                                        <div className="font-semibold text-slate-900 font-mono text-sm">{product.serial_number}</div>
                                    </div>
                                )}
                                {product.voltage && (
                                    <div className="border-b border-slate-200 pb-3">
                                        <div className="text-sm text-slate-500 mb-1">Voltage</div>
                                        <div className="font-semibold text-slate-900">{product.voltage}</div>
                                    </div>
                                )}
                                {product.phase && (
                                    <div className="border-b border-slate-200 pb-3">
                                        <div className="text-sm text-slate-500 mb-1">Phase</div>
                                        <div className="font-semibold text-slate-900">{product.phase}</div>
                                    </div>
                                )}
                                {product.enclosure_type && (
                                    <div className="border-b border-slate-200 pb-3">
                                        <div className="text-sm text-slate-500 mb-1">Enclosure Type</div>
                                        <div className="font-semibold text-slate-900">{product.enclosure_type}</div>
                                    </div>
                                )}
                                {product.controller_series && (
                                    <div className="border-b border-slate-200 pb-3">
                                        <div className="text-sm text-slate-500 mb-1">Controller Series</div>
                                        <div className="font-semibold text-slate-900">{product.controller_series}</div>
                                    </div>
                                )}
                            </div>

                            {/* Pricing */}
                            {(product.total_cost || product.tariff_cost) && (
                                <div className="bg-blue-50 rounded-lg p-4 mb-6">
                                    <h3 className="font-semibold text-slate-900 mb-3">Pricing</h3>
                                    <div className="space-y-2">
                                        {product.total_cost && (
                                            <div className="flex justify-between">
                                                <span className="text-slate-600">Total Cost:</span>
                                                <span className="text-2xl font-bold text-blue-600">
                                                    ${parseFloat(product.total_cost).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                </span>
                                            </div>
                                        )}
                                        {product.tariff_cost && (
                                            <div className="flex justify-between text-sm">
                                                <span className="text-slate-600">Tariff Cost:</span>
                                                <span className="font-semibold text-slate-900">
                                                    ${parseFloat(product.tariff_cost).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                </span>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            )}

                            {/* Additional Details */}
                            <div className="space-y-4">
                                <h3 className="font-semibold text-slate-900 text-lg">Additional Details</h3>
                                
                                <div className="grid grid-cols-2 gap-4 text-sm">
                                    {product.salesman && (
                                        <div>
                                            <span className="text-slate-500">Salesman:</span>
                                            <span className="ml-2 font-medium text-slate-900">{product.salesman}</span>
                                        </div>
                                    )}
                                    {product.hold_branch && (
                                        <div>
                                            <span className="text-slate-500">Hold Branch:</span>
                                            <span className="ml-2 font-medium text-slate-900">{product.hold_branch}</span>
                                        </div>
                                    )}
                                    {product.enclosure && (
                                        <div>
                                            <span className="text-slate-500">Enclosure:</span>
                                            <span className="ml-2 font-medium text-slate-900">{product.enclosure}</span>
                                        </div>
                                    )}
                                    {product.tank && (
                                        <div>
                                            <span className="text-slate-500">Tank:</span>
                                            <span className="ml-2 font-medium text-slate-900">{product.tank}</span>
                                        </div>
                                    )}
                                    {product.breakers && (
                                        <div>
                                            <span className="text-slate-500">Breakers:</span>
                                            <span className="ml-2 font-medium text-slate-900">{product.breakers}</span>
                                        </div>
                                    )}
                                </div>

                                {product.notes && (
                                    <div className="mt-4">
                                        <div className="text-slate-500 text-sm mb-1">Notes</div>
                                        <div className="text-slate-900 whitespace-pre-wrap">{product.notes}</div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Sidebar - Get Quote */}
                    <div className="lg:col-span-1">
                        <div className="bg-white rounded-lg border border-slate-200 shadow-sm p-6 sticky top-6">
                            <h3 className="text-xl font-bold text-slate-900 mb-4">Interested in this product?</h3>
                            <p className="text-slate-600 text-sm mb-6">
                                Get a quote or request more information about this power generation equipment.
                            </p>
                            
                            <button
                                onClick={() => setShowInquiryForm(true)}
                                className="w-full px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition text-lg"
                            >
                                Get Quote
                            </button>

                            <div className="mt-6 pt-6 border-t border-slate-200">
                                <h4 className="font-semibold text-slate-900 mb-2">Need Help?</h4>
                                <p className="text-sm text-slate-600">
                                    Our team is ready to assist you with any questions about our power generation equipment.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            {/* Inquiry Form Modal */}
            {showInquiryForm && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50" onClick={() => setShowInquiryForm(false)}>
                    <div className="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onClick={(e) => e.stopPropagation()}>
                        <div className="p-6 border-b border-slate-200">
                            <div className="flex items-center justify-between">
                                <h2 className="text-2xl font-bold text-slate-900">Request a Quote</h2>
                                <button
                                    onClick={() => setShowInquiryForm(false)}
                                    className="text-slate-400 hover:text-slate-600 text-2xl"
                                >
                                    ×
                                </button>
                            </div>
                            <p className="text-slate-600 mt-2">
                                Product: <strong>{product.unit_id || `Product #${product.id}`}</strong>
                            </p>
                        </div>

                        <form onSubmit={handleInquirySubmit} className="p-6">
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-1">
                                        Name <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                        className="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                    {errors.name && <div className="text-red-500 text-sm mt-1">{errors.name}</div>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-1">
                                        Email <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        required
                                        className="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                    {errors.email && <div className="text-red-500 text-sm mt-1">{errors.email}</div>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-1">
                                        Phone
                                    </label>
                                    <input
                                        type="tel"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                        className="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                    {errors.phone && <div className="text-red-500 text-sm mt-1">{errors.phone}</div>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-1">
                                        Company
                                    </label>
                                    <input
                                        type="text"
                                        value={data.company}
                                        onChange={(e) => setData('company', e.target.value)}
                                        className="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                    {errors.company && <div className="text-red-500 text-sm mt-1">{errors.company}</div>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-1">
                                        Message
                                    </label>
                                    <textarea
                                        value={data.message}
                                        onChange={(e) => setData('message', e.target.value)}
                                        rows={4}
                                        className="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Tell us about your power generation needs..."
                                    />
                                    {errors.message && <div className="text-red-500 text-sm mt-1">{errors.message}</div>}
                                </div>
                            </div>

                            <div className="mt-6 flex gap-3">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50"
                                >
                                    {processing ? 'Submitting...' : 'Submit Inquiry'}
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setShowInquiryForm(false)}
                                    className="px-6 py-3 bg-slate-200 text-slate-700 rounded-lg font-semibold hover:bg-slate-300 transition"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Footer */}
            <footer className="bg-slate-900 text-white mt-16">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div className="text-center">
                        <h3 className="text-xl font-bold mb-2">Power<span className="text-blue-400">Gen</span></h3>
                        <p className="text-slate-400 text-sm">
                            &copy; {new Date().getFullYear()} PowerGen. All rights reserved.
                        </p>
                    </div>
                </div>
            </footer>
        </>
    );
}

