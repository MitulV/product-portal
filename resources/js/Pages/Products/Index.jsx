import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ products, filters = {}, availableVoltages = [] }) {
    const [search, setSearch] = useState(filters.search || '');
    const [voltage, setVoltage] = useState(filters.voltage || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/products', {
            search: search,
            voltage: voltage || undefined,
            sort_by: filters.sort_by || 'id',
            sort_order: filters.sort_order || 'desc',
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleVoltageChange = (e) => {
        const selectedVoltage = e.target.value;
        setVoltage(selectedVoltage);
        router.get('/products', {
            search: search,
            voltage: selectedVoltage || undefined,
            sort_by: filters.sort_by || 'id',
            sort_order: filters.sort_order || 'desc',
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        setSearch('');
        setVoltage('');
        router.get('/products', {
            sort_by: filters.sort_by || 'id',
            sort_order: filters.sort_order || 'desc',
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <>
            <Head title="Power Generation Equipment - PowerGen" />
            
            {/* Header */}
            <header className="bg-white shadow-sm border-b border-slate-200">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div>
                        <h1 className="text-3xl font-bold text-slate-900">Power Generation Equipment</h1>
                        <p className="text-slate-600 mt-1">Browse our inventory of generators and power systems</p>
                    </div>
                </div>
            </header>

            {/* Search Bar */}
            <div className="bg-slate-50 border-b border-slate-200">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <form onSubmit={handleSearch} className="flex flex-col md:flex-row gap-3">
                        <div className="flex-1 relative">
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search by Unit ID, Brand, or Model..."
                                className="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                            />
                            {filters?.search && (
                                <span className="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded">
                                    Active
                                </span>
                            )}
                        </div>
                        <div className="relative">
                            <select
                                value={voltage}
                                onChange={handleVoltageChange}
                                className="w-full md:w-48 px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent bg-white appearance-none cursor-pointer"
                            >
                                <option value="">All Voltages</option>
                                {availableVoltages.map((volt) => (
                                    <option key={volt} value={volt}>
                                        {volt}
                                    </option>
                                ))}
                            </select>
                            <div className="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg className="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <button
                            type="submit"
                            className="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition whitespace-nowrap"
                        >
                            Search
                        </button>
                        {(filters?.search || filters?.voltage) && (
                            <button
                                type="button"
                                onClick={clearFilters}
                                className="px-6 py-3 bg-slate-200 text-slate-700 rounded-lg font-medium hover:bg-slate-300 transition whitespace-nowrap"
                            >
                                Clear All
                            </button>
                        )}
                    </form>
                    {(filters?.search || filters?.voltage) && (
                        <div className="mt-3 text-xs text-slate-600">
                            Active filters:
                            {filters?.search && <span className="ml-2 font-medium">Search: "{filters.search}"</span>}
                            {filters?.voltage && <span className="ml-2 font-medium">Voltage: {filters.voltage}</span>}
                        </div>
                    )}
                </div>
            </div>

            {/* Main Content */}
            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {/* Products Grid */}
                {products.data && products.data.length > 0 ? (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {products.data.map((product) => (
                            <div 
                                key={product.id}
                                className="bg-white rounded-lg border border-slate-200 shadow-sm hover:shadow-md transition-shadow overflow-hidden"
                            >
                                <div className="p-6">
                                    <div className="flex items-start justify-between mb-4">
                                        <div>
                                            <h3 className="text-lg font-bold text-slate-900 mb-1">
                                                {product.unit_id || `Product #${product.id}`}
                                            </h3>
                                            {product.brand && (
                                                <p className="text-sm text-slate-600">{product.brand}</p>
                                            )}
                                        </div>
                                        {product.hold_status && (
                                            <span className={`px-2 py-1 rounded text-xs font-medium ${
                                                product.hold_status === 'Hold' 
                                                    ? 'bg-red-100 text-red-800' 
                                                    : 'bg-green-100 text-green-800'
                                            }`}>
                                                {product.hold_status}
                                            </span>
                                        )}
                                    </div>

                                    <div className="space-y-2 mb-4">
                                        {product.model_number && (
                                            <div className="flex items-center text-sm text-slate-600">
                                                <span className="font-medium w-24">Model:</span>
                                                <span>{product.model_number}</span>
                                            </div>
                                        )}
                                        {product.voltage && (
                                            <div className="flex items-center text-sm text-slate-600">
                                                <span className="font-medium w-24">Voltage:</span>
                                                <span>{product.voltage}</span>
                                            </div>
                                        )}
                                        {product.serial_number && (
                                            <div className="flex items-center text-sm text-slate-600">
                                                <span className="font-medium w-24">Serial:</span>
                                                <span className="font-mono text-xs">{product.serial_number}</span>
                                            </div>
                                        )}
                                    </div>

                                    {product.total_cost && (
                                        <div className="mb-4">
                                            <span className="text-2xl font-bold text-blue-600">
                                                ${parseFloat(product.total_cost).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                            </span>
                                        </div>
                                    )}

                                    <Link
                                        href={`/products/${product.id}`}
                                        className="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition"
                                    >
                                        View Details
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="bg-white rounded-lg border border-slate-200 p-12 text-center">
                        <p className="text-slate-500 text-lg">No products found.</p>
                        {filters?.search && (
                            <button
                                onClick={clearSearch}
                                className="mt-4 text-blue-600 hover:text-blue-800"
                            >
                                Clear search and show all products
                            </button>
                        )}
                    </div>
                )}

                {/* Pagination */}
                {products && products.links && products.links.length > 3 && products.last_page > 1 && (
                    <div className="mt-8 flex flex-col items-center gap-4">
                        <div className="text-sm text-slate-600">
                            Page {products.current_page || 1} of {products.last_page || 1}
                        </div>
                        
                        <div className="flex items-center gap-2 flex-wrap justify-center">
                            {products.links[0] && products.links[0].url ? (
                                <Link
                                    href={products.links[0].url}
                                    className="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition"
                                >
                                    ««
                                </Link>
                            ) : (
                                <span className="px-3 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed">
                                    ««
                                </span>
                            )}

                            {products.links[0] && products.links[0].url ? (
                                <Link
                                    href={products.links[0].url}
                                    className="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition"
                                >
                                    ‹
                                </Link>
                            ) : (
                                <span className="px-3 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed">
                                    ‹
                                </span>
                            )}

                            {products.links.slice(1, -1).map((link, key) => (
                                link.url ? (
                                    <Link
                                        key={key}
                                        href={link.url}
                                        className={`px-4 py-2 rounded border transition ${
                                            link.active 
                                                ? 'bg-blue-600 text-white border-blue-600 font-semibold' 
                                                : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ) : (
                                    <span
                                        key={key}
                                        className="px-4 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed"
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    ></span>
                                )
                            ))}

                            {products.links[products.links.length - 1] && products.links[products.links.length - 1].url ? (
                                <Link
                                    href={products.links[products.links.length - 1].url}
                                    className="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition"
                                >
                                    ›
                                </Link>
                            ) : (
                                <span className="px-3 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed">
                                    ›
                                </span>
                            )}

                            {products.links[products.links.length - 1] && products.links[products.links.length - 1].url ? (
                                <Link
                                    href={products.links[products.links.length - 1].url}
                                    className="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition"
                                >
                                    »»
                                </Link>
                            ) : (
                                <span className="px-3 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed">
                                    »»
                                </span>
                            )}
                        </div>
                    </div>
                )}
            </main>

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

