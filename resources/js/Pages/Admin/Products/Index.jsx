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
            // ============================================
            // STAGE 1: FILE READING
            // ============================================
            console.group('📄 Excel File Import - Starting');
            console.log('📋 File Information:', {
                name: file.name,
                size: `${(file.size / 1024).toFixed(2)} KB`,
                type: file.type,
                lastModified: new Date(file.lastModified).toLocaleString()
            });

            const reader = new FileReader();
            
            reader.onload = (e) => {
                try {
            // ============================================
            // STAGE 2: WORKBOOK PARSING
            // ============================================
            console.group('📊 Workbook Parsing');
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            
            console.log('Available Sheets:', workbook.SheetNames);
            console.log('Total Sheets:', workbook.SheetNames.length);
            
            // Check if "Generators" sheet exists
            if (!workbook.SheetNames.includes('Generators')) {
                console.error('❌ "Generators" sheet not found!');
                console.error('Available sheets:', workbook.SheetNames);
                console.groupEnd();
                console.groupEnd();
                reject(new Error('The Excel file must contain a "Generators" sheet.'));
                return;
            }
            
            console.log('✅ Found "Generators" sheet');
            console.groupEnd();
            // ============================================
            // STAGE 3: DATA EXTRACTION
            // ============================================
            console.group('📈 Data Extraction');
            const worksheet = workbook.Sheets['Generators'];
            const jsonData = XLSX.utils.sheet_to_json(worksheet, { 
                header: 1,
                defval: null,
                raw: false
            });
            
            console.log(`Total rows in Excel: ${jsonData.length}`);
            
            // Headers are at row 1 (index 0), data starts at row 2 (index 1)
            if (jsonData.length < 2) {
                console.error('❌ Insufficient rows!');
                console.error(`Expected: At least 2 rows (headers at row 1, data at row 2)`);
                console.error(`Found: ${jsonData.length} rows`);
                console.groupEnd();
                console.groupEnd();
                reject(new Error('The Excel file must have at least 2 rows (headers at row 1, data at row 2).'));
                return;
            }
            
            // Extract headers from row 1 (index 0)
            const headers = jsonData[0] || [];
            console.log('📑 Header Row (Row 1):');
            console.table(headers.map((h, i) => ({
                Column: String.fromCharCode(65 + i),
                Index: i,
                Header: h || '(empty)'
            })));
            
            // Dynamically find Stock ID column index from headers
            const stockIdColumnIndex = headers.findIndex(h => {
                const header = String(h || '').toLowerCase().trim();
                return header === 'stock id' || header === 'stockid' || header === 'unit id' || header === 'unitid';
            });
            
            if (stockIdColumnIndex === -1) {
                console.warn('⚠️  Stock ID column not found in headers!');
                console.warn('Searched for: "Stock ID", "StockID", "Unit ID", "UnitID"');
                console.warn('Using default index: 26 (Column AA)');
                console.warn('Available headers:', headers.filter(h => h).join(', '));
            } else {
                console.log(`✅ Stock ID column found:`);
                console.log(`   Column: ${String.fromCharCode(65 + stockIdColumnIndex)}`);
                console.log(`   Index: ${stockIdColumnIndex}`);
                console.log(`   Header: "${headers[stockIdColumnIndex]}"`);
            }
            console.groupEnd();
            
            // Helper function to clean cell values
            const cleanValue = (value) => {
                if (value === null || value === undefined || value === '') {
                    return null;
                }
                const str = String(value).trim();
                return str === '' ? null : str;
            };
            
            // ============================================
            // STAGE 4: STOCK ID ANALYSIS
            // ============================================
            console.group('🔍 Stock ID Analysis');
            const rows = jsonData.slice(1); // Data starts from row 2 (index 1)
            console.log(`Total data rows (starting from row 2): ${rows.length}`);
            
            const stockIdIndex = stockIdColumnIndex !== -1 ? stockIdColumnIndex : 26;
            const stockIdAnalysis = rows.slice(0, 10).map((row, idx) => {
                const stockIdValue = row[stockIdIndex];
                const cleaned = cleanValue(stockIdValue);
                const isValid = !!(cleaned && cleaned.trim() !== '');
                return {
                    'Row #': idx + 2, // Row 2 in Excel (0-based index 1, so idx + 2)
                    'Stock ID Value': stockIdValue ?? '(null/empty)',
                    'Type': typeof stockIdValue,
                    'Cleaned': cleaned ?? '(null)',
                    'Valid': isValid ? '✅ Yes' : '❌ No'
                };
            });
            
            console.table(stockIdAnalysis);
            console.groupEnd();
            
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

            // ============================================
            // STAGE 5: ROW PROCESSING
            // ============================================
            console.group('🔄 Row Processing');
            const emptyRows = [];
            const rowsWithData = [];
            
            const validRows = rows.filter((row, index) => {
                const hasData = row && row.some(cell => cell !== null && cell !== '');
                if (!hasData) {
                    emptyRows.push(index + 2); // Row 2 in Excel
                } else {
                    rowsWithData.push(index + 2); // Row 2 in Excel
                }
                return hasData;
            });
            
            console.log(`Rows with data: ${rowsWithData.length}`);
            if (emptyRows.length > 0) {
                console.warn(`Empty rows skipped: ${emptyRows.length} (rows: ${emptyRows.slice(0, 10).join(', ')}${emptyRows.length > 10 ? '...' : ''})`);
            }
            console.groupEnd();
            
            // ============================================
            // STAGE 6: PRODUCT MAPPING
            // ============================================
            console.group('📦 Product Mapping');
            const products = validRows
                        .map((row, originalIndex) => {
                            const rowNumber = rowsWithData[originalIndex];
                            const stockIdIndex = stockIdColumnIndex !== -1 ? stockIdColumnIndex : 26;
                            
                            // Map columns based on actual Excel structure (headers in row 1)
                            // Column order: Hold, Hold Branch, Salesman, Opportunity Name, Brand, Model, Location,
                            // IPAS/CPQ #, CPS PO#, Enclosure, Enclosure Type, Tank, Controller Series, Breaker(s),
                            // Notes, Application Group, Engine Model, Unit Specification, IBC Certification,
                            // Exhaust Emissions, Temp Rise, Description, Fuel Type, Voltage, Phase, Serial Number,
                            // Stock ID (dynamically detected or index 26), Power, Engine Speed, Radiator Design Temp, Frequency,
                            // Full Load Amps, Tech Spec, Date Hold Added, Hold Expiration, Est Completion Date,
                            // Ship Date, Total Cost, Retail Cost, Tariff, Sales Order #
                            const product = {
                                product_type: 'Generators',
                                hold_status: cleanValue(row[0]),
                                hold_branch: cleanValue(row[1]),
                                salesman: cleanValue(row[2]),
                                opportunity_name: cleanValue(row[3]),
                                brand: cleanValue(row[4]),
                                model_number: cleanValue(row[5]),
                                location: cleanValue(row[6]),
                                ipas_cpq_number: cleanValue(row[7]),
                                cps_po_number: cleanValue(row[8]),
                                enclosure: cleanValue(row[9]),
                                enclosure_type: cleanValue(row[10]),
                                tank: cleanValue(row[11]),
                                controller_series: cleanValue(row[12]),
                                breakers: cleanValue(row[13]),
                                notes: cleanValue(row[14]),
                                application_group: cleanValue(row[15]),
                                engine_model: cleanValue(row[16]),
                                unit_specification: cleanValue(row[17]),
                                ibc_certification: cleanValue(row[18]),
                                exhaust_emissions: cleanValue(row[19]),
                                temp_rise: cleanValue(row[20]),
                                description: cleanValue(row[21]),
                                fuel_type: cleanValue(row[22]),
                                voltage: cleanValue(row[23]),
                                phase: cleanValue(row[24]),
                                serial_number: cleanValue(row[25]),
                                unit_id: cleanValue(row[stockIdIndex]), // Stock ID - dynamically detected or default to 26
                                power: row[27] ? (parseInt(row[27]) || null) : null,
                                engine_speed: row[28] ? (parseInt(row[28]) || null) : null,
                                radiator_design_temp: row[29] ? (parseInt(row[29]) || null) : null,
                                frequency: row[30] ? (parseInt(row[30]) || null) : null,
                                full_load_amps: row[31] ? (parseInt(row[31]) || null) : null,
                                tech_spec: cleanValue(row[32]),
                                date_hold_added: parseDate(row[33]),
                                hold_expiration_date: parseDate(row[34]),
                                est_completion_date: parseDate(row[35]),
                                ship_date: parseDate(row[36]),
                                total_cost: row[37] ? (parseFloat(row[37]) || null) : null,
                                retail_cost: row[38] ? (parseFloat(row[38]) || null) : null,
                                tariff_cost: row[39] ? (parseFloat(row[39]) || null) : null,
                                sales_order_number: cleanValue(row[40]),
                            };
                            
                            // Add original row number for tracking
                            product._originalRowNumber = rowNumber;
                            return product;
                        })
                        .filter(product => {
                            const hasUnitId = product.unit_id && product.unit_id.trim() !== '';
                            return hasUnitId;
                        });
            
            const skippedProducts = products.filter(p => !(p.unit_id && p.unit_id.trim() !== ''));
            const validProducts = products.filter(p => p.unit_id && p.unit_id.trim() !== '');
            
            console.log(`Products mapped: ${products.length}`);
            console.log(`Valid products (with Stock ID): ${validProducts.length}`);
            if (skippedProducts.length > 0) {
                console.error(`❌ Products without Stock ID: ${skippedProducts.length}`);
                console.table(skippedProducts.map(p => ({
                    'Row #': p._originalRowNumber,
                    'Brand': p.brand || '(empty)',
                    'Model': p.model_number || '(empty)',
                    'Serial': p.serial_number || '(empty)',
                    'Stock ID': p.unit_id || '(MISSING)'
                })));
            }
            console.groupEnd();
                    
            // ============================================
            // STAGE 7: FINAL SUMMARY
            // ============================================
            console.group('📊 Import Summary');
            
            // Remove tracking field before resolving
            const cleanedProducts = products.map(p => {
                const { _originalRowNumber, ...product } = p;
                return product;
            });
            
            const skippedCount = rows.length - cleanedProducts.length;
            const emptyRowCount = emptyRows.length;
            const missingStockIdCount = skippedCount - emptyRowCount;
            
            console.log('📈 Statistics:');
            console.table({
                'Total Excel Rows': jsonData.length,
                'Header Row': 'Row 1',
                'Data Rows': rows.length,
                'Empty Rows': emptyRowCount,
                'Rows with Data': rowsWithData.length,
                'Products Mapped': products.length,
                'Valid Products (with Stock ID)': cleanedProducts.length,
                'Skipped (missing Stock ID)': missingStockIdCount,
                'Total Skipped': skippedCount
            });
            
            if (cleanedProducts.length > 0) {
                console.log('✅ Valid Products:');
                console.table(cleanedProducts.map((p, idx) => ({
                    '#': idx + 1,
                    'Stock ID': p.unit_id,
                    'Brand': p.brand || '(empty)',
                    'Model': p.model_number || '(empty)',
                    'Serial': p.serial_number || '(empty)'
                })));
            }
            
            if (skippedCount > 0) {
                console.warn(`⚠️  WARNING: ${skippedCount} row(s) were skipped:`);
                if (emptyRowCount > 0) {
                    console.warn(`   - ${emptyRowCount} empty row(s)`);
                }
                if (missingStockIdCount > 0) {
                    console.error(`   - ${missingStockIdCount} row(s) missing Stock ID`);
                }
            }
            
            console.groupEnd(); // Import Summary
            console.groupEnd(); // Excel File Import
            
            // Return both products and metadata
            resolve({
                products: cleanedProducts,
                skippedCount: skippedCount,
                totalRows: rows.length,
                emptyRows: emptyRowCount,
                missingStockId: missingStockIdCount
            });
                } catch (error) {
                    console.error('❌ Error parsing Excel file:', error);
                    console.error('Error details:', {
                        message: error.message,
                        stack: error.stack,
                        name: error.name
                    });
                    console.groupEnd(); // Close any open groups
                    reject(new Error('Error parsing Excel file: ' + error.message));
                }
            };
            
            reader.onerror = (error) => {
                console.error('❌ File reading error:', error);
                console.groupEnd(); // Close any open groups
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
            
            // ============================================
            // UPLOAD: Parse Excel file
            // ============================================
            console.group('📤 Upload Preparation');
            const parseResult = await parseExcelFile(data.file);
            const productsData = parseResult.products || parseResult; // Handle both old and new format
            
            console.log(`✅ Parsing complete. Products ready to import: ${productsData.length}`);
            
            // Show warning if products were skipped
            if (parseResult.skippedCount && parseResult.skippedCount > 0) {
                const message = `${parseResult.skippedCount} product(s) were skipped because they are missing Stock ID (required field). Only ${productsData.length} product(s) will be imported.`;
                console.warn('⚠️', message);
                if (parseResult.missingStockId > 0) {
                    alert(`Warning: ${parseResult.missingStockId} product(s) were skipped because they are missing Stock ID (required field).\n\nOnly ${productsData.length} product(s) will be imported.\n\nPlease check your Excel file - all products must have a value in the Stock ID column.`);
                }
            }
            
            if (productsData.length === 0) {
                console.error('❌ No valid products found in the Excel file.');
                console.groupEnd();
                alert('No valid products found in the Excel file. All products must have a Stock ID value.');
                setProcessingExcel(false);
                return;
            }

            console.log('📋 Products by type:', {
                Generators: productsData.filter(p => p.product_type === 'Generators').length,
                Switch: productsData.filter(p => p.product_type === 'Switch').length,
                'Docking Stations': productsData.filter(p => p.product_type === 'Docking Stations').length,
                Other: productsData.filter(p => p.product_type === 'Other').length
            });
            console.groupEnd();

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
                <strong>Excel Format:</strong> The file must contain a "Generators" sheet with headers in row 1 (A1 onwards) and data starting from row 2.
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
