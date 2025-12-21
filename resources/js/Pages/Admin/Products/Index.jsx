import React, { useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import * as XLSX from 'xlsx';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function Index({ products, filters = {} }) {
    const { flash } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm({
        file: null,
    });
    const [processingExcel, setProcessingExcel] = useState(false);
    const [search, setSearch] = useState(filters?.search || '');
    const [sortBy, setSortBy] = useState(filters?.sort_by || 'id');
    const [sortOrder, setSortOrder] = useState(filters?.sort_order || 'desc');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/admin/products', {
            search: search,
            sort_by: sortBy,
            sort_order: sortOrder,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSort = (column) => {
        const newSortOrder = sortBy === column && sortOrder === 'asc' ? 'desc' : 'asc';
        setSortBy(column);
        setSortOrder(newSortOrder);
        router.get('/admin/products', {
            search: search,
            sort_by: column,
            sort_order: newSortOrder,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearSearch = () => {
        setSearch('');
        router.get('/admin/products', {
            sort_by: sortBy,
            sort_order: sortOrder,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };


    const parseExcelFile = (file) => {
        return new Promise((resolve, reject) => {
            console.log('📄 Starting Excel file parsing...');
            console.log('📋 File Info:', {
                name: file.name,
                size: file.size,
                type: file.type,
                lastModified: new Date(file.lastModified).toLocaleString()
            });

            const reader = new FileReader();
            
            reader.onload = (e) => {
                try {
                    console.log('✅ File read successfully, parsing workbook...');
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    
                    console.log('📊 Workbook Info:', {
                        sheetNames: workbook.SheetNames,
                        sheetCount: workbook.SheetNames.length
                    });
                    
                    // Check if "Generators" sheet exists
                    if (!workbook.SheetNames.includes('Generators')) {
                        console.error('❌ "Generators" sheet not found. Available sheets:', workbook.SheetNames);
                        reject(new Error('The Excel file must contain a "Generators" sheet.'));
                        return;
                    }
                    
                    console.log('✅ Found "Generators" sheet, reading data...');
                    const worksheet = workbook.Sheets['Generators'];
                    const jsonData = XLSX.utils.sheet_to_json(worksheet, { 
                        header: 1,
                        defval: null,
                        raw: false
                    });
                    
                    console.log('📈 Raw Excel Data:', {
                        totalRows: jsonData.length,
                        firstFewRows: jsonData.slice(0, 10),
                        allData: jsonData
                    });
                    
                    // Headers are at row 5 (index 4), data starts at row 6 (index 5)
                    if (jsonData.length < 6) {
                        console.error('❌ Insufficient rows. Expected at least 6 rows, got:', jsonData.length);
                        reject(new Error('The Excel file must have at least 6 rows (headers at row 5, data at row 6).'));
                        return;
                    }
                    
                    // Extract headers from row 5 (index 4)
                    const headers = jsonData[4] || [];
                    console.log('📑 Headers (Row 5):', headers);
                    console.log('📑 Headers with indices:', headers.map((h, i) => `${String.fromCharCode(65 + i)}5: ${h}`));
                    
                    // Extract data starting from row 6 (index 5)
                    const rows = jsonData.slice(5);
                    console.log('📝 Data Rows (starting from row 6):', {
                        rowCount: rows.length,
                        firstRow: rows[0],
                        firstFewRows: rows.slice(0, 5),
                        allRows: rows
                    });
                    
                    // Helper function to clean cell values
                    const cleanValue = (value) => {
                        if (value === null || value === undefined || value === '') {
                            return null;
                        }
                        const str = String(value).trim();
                        return str === '' ? null : str;
                    };

                    // Helper function to parse dates (handles Excel date serial numbers and date strings)
                    const parseDate = (value) => {
                        if (!value) return null;
                        
                        // If it's a number (Excel date serial), convert it
                        if (typeof value === 'number') {
                            // Excel date serial: days since January 1, 1900
                            const excelEpoch = new Date(1899, 11, 30);
                            const date = new Date(excelEpoch.getTime() + value * 24 * 60 * 60 * 1000);
                            if (!isNaN(date.getTime())) {
                                return date.toISOString().split('T')[0]; // Return YYYY-MM-DD format
                            }
                        }
                        
                        // Try parsing as date string
                        if (typeof value === 'string') {
                            const date = new Date(value);
                            if (!isNaN(date.getTime())) {
                                return date.toISOString().split('T')[0];
                            }
                        }
                        
                        return null;
                    };

                    // Map rows to product objects
                    console.log('🔄 Processing rows into product objects...');
                    const products = rows
                        .filter((row, index) => {
                            const hasData = row && row.some(cell => cell !== null && cell !== '');
                            if (!hasData) {
                                console.log(`⏭️  Skipping empty row ${index + 6} (index ${index})`);
                            }
                            return hasData;
                        })
                        .map((row, index) => {
                            console.log(`📦 Processing row ${index + 6} (index ${index}):`, row);
                            
                            // Map columns A-Y (indices 0-24) to product fields
                            const product = {
                                hold_status: cleanValue(row[0]),
                                hold_branch: cleanValue(row[1]),
                                salesman: cleanValue(row[2]),
                                opportunity_name: cleanValue(row[3]),
                                hold_expiration_date: parseDate(row[4]),
                                brand: cleanValue(row[5]),
                                model_number: cleanValue(row[6]),
                                est_completion_date: parseDate(row[7]),
                                total_cost: row[8] ? (parseFloat(row[8]) || null) : null,
                                tariff_cost: row[9] ? (parseFloat(row[9]) || null) : null,
                                sales_order_number: cleanValue(row[10]),
                                ipas_cpq_number: cleanValue(row[11]),
                                cps_po_number: cleanValue(row[12]),
                                ship_date: parseDate(row[13]),
                                voltage: cleanValue(row[14]),
                                phase: cleanValue(row[15]),
                                enclosure: cleanValue(row[16]),
                                enclosure_type: cleanValue(row[17]),
                                tank: cleanValue(row[18]),
                                controller_series: cleanValue(row[19]),
                                breakers: cleanValue(row[20]),
                                serial_number: cleanValue(row[21]),
                                unit_id: cleanValue(row[22]),
                                notes: cleanValue(row[23]),
                                tech_spec: cleanValue(row[24]),
                            };
                            
                            console.log(`✅ Parsed product from row ${index + 6}:`, product);
                            return product;
                        })
                        .filter(product => {
                            // Skip rows where unit_id is empty (unit_id is required and unique)
                            const hasUnitId = product.unit_id && product.unit_id.trim() !== '';
                            if (!hasUnitId) {
                                console.log('⏭️  Filtering out product without unit_id (required):', product);
                            }
                            return hasUnitId;
                        });
                    
                    console.log('🎉 Excel parsing complete!');
                    console.log('📊 Final Products Summary:', {
                        totalProducts: products.length,
                        products: products,
                        productsWithUnitId: products.filter(p => p.unit_id).length,
                        productsWithSerialNumber: products.filter(p => p.serial_number).length,
                    });
                    
                    resolve(products);
                } catch (error) {
                    reject(new Error('Error parsing Excel file: ' + error.message));
                }
            };
            
            reader.onerror = () => {
                reject(new Error('Error reading file.'));
            };
            
            reader.readAsArrayBuffer(file);
        });
    };

    const handleFileUpload = async (e) => {
        e.preventDefault();
        
        if (!data.file) {
            return;
        }

        setProcessingExcel(true);

        try {
            console.log('🚀 Starting file upload process...');
            console.log('📎 Selected file:', data.file);
            
            // Parse Excel file in frontend
            const productsData = await parseExcelFile(data.file);
            
            console.log('✅ Parsing complete. Products to import:', productsData);
            console.log('📦 Products count:', productsData.length);
            
            if (productsData.length === 0) {
                console.warn('⚠️  No valid products found in the Excel file.');
                alert('No valid products found in the Excel file.');
                setProcessingExcel(false);
                return;
            }

            console.log('📤 Preparing to send data to backend...');
            console.log('📋 Data to send:', {
                productsCount: productsData.length,
                products: productsData
            });

            // Send JSON data to backend - set products in form data
            setData('products', productsData);
            
            // Use router.post directly to send the data
            router.post('/admin/products/import', {
                products: productsData
            }, {
                preserveScroll: true,
                onSuccess: (page) => {
                    console.log('✅ Upload successful!');
                    console.log('📄 Response page:', page);
                    reset('file');
                    setData('products', []);
                    setProcessingExcel(false);
                    // Reset file input
                    const fileInput = document.querySelector('input[type="file"]');
                    if (fileInput) {
                        fileInput.value = '';
                    }
                },
                onError: (errors) => {
                    console.error('❌ Upload error occurred:');
                    console.error('🔴 Error details:', errors);
                    console.error('🔴 Full error object:', JSON.stringify(errors, null, 2));
                    setProcessingExcel(false);
                },
                onFinish: () => {
                    console.log('🏁 Upload process finished');
                    setProcessingExcel(false);
                },
            });
        } catch (error) {
            alert('Error processing file: ' + error.message);
            setProcessingExcel(false);
        }
    }

    return (
        <AdminLayout>
            <Head title="Products" />
            
            {/* Success/Error Messages */}
            {flash?.success && (
                <div className="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {flash.success}
                </div>
            )}
            {flash?.error && (
                <div className="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    {flash.error}
                </div>
            )}
            {flash?.import_errors && Array.isArray(flash.import_errors) && flash.import_errors.length > 0 && (
                <div className="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div className="font-semibold text-yellow-900 mb-2">
                        Import Errors ({flash.import_errors.length}):
                    </div>
                    <ul className="list-disc list-inside space-y-1 text-sm text-yellow-800 max-h-60 overflow-y-auto">
                        {flash.import_errors.map((error, index) => (
                            <li key={index}>{error}</li>
                        ))}
                    </ul>
                </div>
            )}
            {errors?.file && (
                <div className="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    <strong>File Error:</strong> {Array.isArray(errors.file) ? errors.file.join(', ') : errors.file}
                </div>
            )}
            {Object.keys(errors).length > 0 && !errors.file && (
                <div className="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    <strong>Validation Errors:</strong>
                    <ul className="list-disc list-inside mt-2">
                        {Object.entries(errors).map(([key, value]) => (
                            <li key={key}>
                                <strong>{key}:</strong> {Array.isArray(value) ? value.join(', ') : value}
                            </li>
                        ))}
                    </ul>
                </div>
            )}
            
            <div className="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                <strong>Excel Format:</strong> The file must contain a "Generators" sheet with headers in row 5 (A5 to Y5) and data starting from row 6.
            </div>

            <div className="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <h2 className="text-2xl font-bold text-slate-800">Products</h2>
                
                <div className="flex items-center gap-4">
                    <form onSubmit={handleFileUpload} className="flex items-center gap-2">
                        <div className="flex flex-col gap-1">
                            <input 
                                type="file" 
                                accept=".xlsx,.xls"
                                onChange={e => setData('file', e.target.files[0])}
                                className="text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200"
                            />
                            {data.file && (
                                <span className="text-xs text-slate-500">
                                    Selected: {data.file.name}
                                </span>
                            )}
                        </div>
                        <button 
                            type="submit" 
                            disabled={processing || processingExcel || !data.file}
                            className="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {processingExcel ? 'Processing...' : processing ? 'Uploading...' : 'Upload Excel'}
                        </button>
                    </form>
                </div>
            </div>

            {/* Search and Filters */}
            <div className="mb-4 flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                <div className="flex-1 max-w-md">
                    <form onSubmit={handleSearch} className="flex gap-2">
                        <div className="flex-1 relative">
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search by Unit ID, Hold Status, Hold Branch, or Salesman..."
                                className="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            />
                            {filters?.search && (
                                <span className="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded">
                                    Active
                                </span>
                            )}
                        </div>
                        <button
                            type="submit"
                            className="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition"
                        >
                            Search
                        </button>
                        {filters?.search && (
                            <button
                                type="button"
                                onClick={clearSearch}
                                className="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-300 transition"
                            >
                                Clear
                            </button>
                        )}
                    </form>
                    {filters?.search && (
                        <div className="mt-2 text-xs text-slate-500">
                            Searching for: <strong>{filters.search}</strong>
                        </div>
                    )}
                </div>
                
                {/* Total Count Display */}
                {products && (
                    <div className="text-sm text-slate-600">
                        <strong>Total Records:</strong> {products.total || 0} product(s)
                        {products.data && products.data.length > 0 && (
                            <span className="ml-4">
                                Showing {((products.current_page - 1) * products.per_page) + 1} to {Math.min(products.current_page * products.per_page, products.total)} of {products.total}
                            </span>
                        )}
                    </div>
                )}
            </div>

            <div className="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm overflow-x-auto">
                <table className="w-full text-left min-w-full">
                    <thead className="bg-slate-50 text-slate-500 text-xs uppercase font-semibold">
                        <tr>
                            <th className="px-4 py-3 w-16 sticky left-0 bg-slate-50 z-10">#</th>
                            <th 
                                className="px-4 py-3 cursor-pointer hover:bg-slate-100 transition select-none"
                                onClick={() => handleSort('unit_id')}
                            >
                                <div className="flex items-center gap-2">
                                    Unit ID
                                    {sortBy === 'unit_id' && (
                                        <span className="text-blue-600">
                                            {sortOrder === 'asc' ? '↑' : '↓'}
                                        </span>
                                    )}
                                </div>
                            </th>
                            <th className="px-4 py-3">Hold Status</th>
                            <th className="px-4 py-3">Hold Branch</th>
                            <th className="px-4 py-3">Salesman</th>
                            <th className="px-4 py-3">Opportunity Name</th>
                            <th className="px-4 py-3">Hold Expiration</th>
                            <th className="px-4 py-3">Brand</th>
                            <th className="px-4 py-3">Model Number</th>
                            <th className="px-4 py-3">Est Completion</th>
                            <th className="px-4 py-3">Total Cost</th>
                            <th className="px-4 py-3">Tariff Cost</th>
                            <th className="px-4 py-3">Sales Order #</th>
                            <th className="px-4 py-3">IPAS CPQ #</th>
                            <th className="px-4 py-3">CPS PO #</th>
                            <th className="px-4 py-3">Ship Date</th>
                            <th className="px-4 py-3">Voltage</th>
                            <th className="px-4 py-3">Phase</th>
                            <th className="px-4 py-3">Enclosure</th>
                            <th className="px-4 py-3">Enclosure Type</th>
                            <th className="px-4 py-3">Tank</th>
                            <th className="px-4 py-3">Controller Series</th>
                            <th className="px-4 py-3">Breakers</th>
                            <th className="px-4 py-3">Serial #</th>
                            <th className="px-4 py-3">Notes</th>
                            <th className="px-4 py-3">Tech Spec</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {products.data.length > 0 ? (
                            products.data.map((product, index) => {
                                // Calculate row number based on current page
                                const rowNumber = ((products.current_page - 1) * products.per_page) + index + 1;
                                return (
                                    <tr key={product.id} className="hover:bg-slate-50 transition border-b border-slate-100 last:border-0 text-sm text-slate-700">
                                        <td className="px-4 py-3 text-slate-500 font-medium sticky left-0 bg-white z-10">{rowNumber}</td>
                                        <td className="px-4 py-3 font-medium">{product.unit_id || '-'}</td>
                                        <td className="px-4 py-3">{product.hold_status || '-'}</td>
                                        <td className="px-4 py-3">{product.hold_branch || '-'}</td>
                                        <td className="px-4 py-3">{product.salesman || '-'}</td>
                                        <td className="px-4 py-3">{product.opportunity_name || '-'}</td>
                                        <td className="px-4 py-3">{product.hold_expiration_date || '-'}</td>
                                        <td className="px-4 py-3">{product.brand || '-'}</td>
                                        <td className="px-4 py-3">{product.model_number || '-'}</td>
                                        <td className="px-4 py-3">{product.est_completion_date || '-'}</td>
                                        <td className="px-4 py-3">{product.total_cost ? `$${parseFloat(product.total_cost).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : '-'}</td>
                                        <td className="px-4 py-3">{product.tariff_cost ? `$${parseFloat(product.tariff_cost).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : '-'}</td>
                                        <td className="px-4 py-3">{product.sales_order_number || '-'}</td>
                                        <td className="px-4 py-3">{product.ipas_cpq_number || '-'}</td>
                                        <td className="px-4 py-3">{product.cps_po_number || '-'}</td>
                                        <td className="px-4 py-3">{product.ship_date || '-'}</td>
                                        <td className="px-4 py-3">{product.voltage || '-'}</td>
                                        <td className="px-4 py-3">{product.phase || '-'}</td>
                                        <td className="px-4 py-3">{product.enclosure || '-'}</td>
                                        <td className="px-4 py-3">{product.enclosure_type || '-'}</td>
                                        <td className="px-4 py-3">{product.tank || '-'}</td>
                                        <td className="px-4 py-3">{product.controller_series || '-'}</td>
                                        <td className="px-4 py-3">{product.breakers || '-'}</td>
                                        <td className="px-4 py-3 font-mono text-slate-500">{product.serial_number || '-'}</td>
                                        <td className="px-4 py-3 max-w-xs truncate" title={product.notes || ''}>{product.notes || '-'}</td>
                                        <td className="px-4 py-3 max-w-xs truncate" title={product.tech_spec || ''}>{product.tech_spec || '-'}</td>
                                    </tr>
                                );
                            })
                        ) : (
                            <tr>
                                <td colSpan="26" className="px-6 py-12 text-center text-slate-400">
                                    No products found.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            {/* Enhanced Pagination */}
            {products && products.links && products.links.length > 3 && products.last_page > 1 && (
                <div className="mt-6 flex flex-col items-center gap-4">
                    {/* Pagination Info */}
                    <div className="text-sm text-slate-600">
                        Page {products.current_page || 1} of {products.last_page || 1}
                    </div>
                    
                    {/* Pagination Controls */}
                    <div className="flex items-center gap-2 flex-wrap justify-center">
                        {/* First Page */}
                        {products.links[0] && products.links[0].url ? (
                            <Link
                                href={products.links[0].url}
                                className="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition"
                                title="First Page"
                            >
                                ««
                            </Link>
                        ) : (
                            <span className="px-3 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed">
                                ««
                            </span>
                        )}

                        {/* Previous Page */}
                        {products.links[0] && products.links[0].url ? (
                            <Link
                                href={products.links[0].url}
                                className="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition"
                                title="Previous Page"
                            >
                                ‹
                            </Link>
                        ) : (
                            <span className="px-3 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed">
                                ‹
                            </span>
                        )}

                        {/* Page Numbers */}
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

                        {/* Next Page */}
                        {products.links[products.links.length - 1] && products.links[products.links.length - 1].url ? (
                            <Link
                                href={products.links[products.links.length - 1].url}
                                className="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition"
                                title="Next Page"
                            >
                                ›
                            </Link>
                        ) : (
                            <span className="px-3 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed">
                                ›
                            </span>
                        )}

                        {/* Last Page */}
                        {products.links[products.links.length - 1] && products.links[products.links.length - 1].url ? (
                            <Link
                                href={products.links[products.links.length - 1].url}
                                className="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition"
                                title="Last Page"
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

        </AdminLayout>
    );
}
