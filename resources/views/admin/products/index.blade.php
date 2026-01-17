@extends('admin.layout')

@section('title', 'Products')

@section('header', 'Products')

@section('content')
    <div x-data="productAdmin()">
        @if (session('import_errors') && is_array(session('import_errors')) && count(session('import_errors')) > 0)
            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="font-semibold text-yellow-900 mb-2">
                    Import Errors ({{ count(session('import_errors')) }}):
                </div>
                <ul class="list-disc list-inside space-y-1 text-sm text-yellow-800 max-h-60 overflow-y-auto">
                    @foreach (session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex-1 text-sm text-blue-800">
                    <strong class="block mb-2 text-blue-900">Excel File Structure Requirements:</strong>
                    <ul class="list-disc list-inside space-y-1 mb-3">
                        <li>The Excel file must contain <strong>4 sheets</strong> named exactly: <code
                                class="bg-blue-100 px-1 rounded">Generators</code>, <code
                                class="bg-blue-100 px-1 rounded">Switch</code>, <code
                                class="bg-blue-100 px-1 rounded">Docking Stations</code>, and <code
                                class="bg-blue-100 px-1 rounded">Other</code></li>
                        <li>Each sheet must have <strong>headers in row 5</strong> (starting from column A)</li>
                        <li>Product data must start from <strong>row 6</strong> (row 5 is for headers only)</li>
                        <li>Each product type has different columns - download the template below to see the exact column
                            structure</li>
                        <li>Leave cells empty if data is not available - all fields are optional except <code
                                class="bg-blue-100 px-1 rounded">Stock ID</code> (Unit ID)</li>
                    </ul>
                    <div class="mt-3 pt-3 border-t border-blue-200">
                        <a href="{{ route('admin.products.download-template') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download Empty Template
                        </a>
                        <span class="ml-3 text-xs text-blue-600">Download a template file with all required columns and
                            sheet names</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h2 class="text-2xl font-bold text-slate-800">Products</h2>

            <div class="flex items-center gap-4">
                <form @submit.prevent="handleExcelUpload" class="flex items-center gap-2">
                    <div class="flex flex-col gap-1">
                        <input type="file" accept=".xlsx,.xls" @change="handleFileUpload($event)"
                            class="text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" />
                        <span x-show="selectedFile" class="text-xs text-slate-500"
                            x-text="selectedFile ? 'Selected: ' + selectedFile.name : ''"></span>
                    </div>
                    <button type="submit" :disabled="processingExcel || !selectedFile"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!processingExcel">Upload Excel</span>
                        <span x-show="processingExcel">Processing...</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="mb-4 flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
            <div class="flex-1 max-w-md">
                <form method="GET" action="{{ route('admin.products.index') }}" class="flex gap-2">
                    <div class="flex-1 relative">
                        <input type="text" name="search" x-model="search"
                            placeholder="Search by Unit ID, Hold Status, Hold Branch, or Salesman..."
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" />
                        @if (isset($filters['search']) && $filters['search'])
                            <span
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded">
                                Active
                            </span>
                        @endif
                    </div>
                    <input type="hidden" name="sort_by" :value="sortBy">
                    <input type="hidden" name="sort_order" :value="sortOrder">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                        Search
                    </button>
                    @if (isset($filters['search']) && $filters['search'])
                        <a href="{{ route('admin.products.index', ['sort_by' => $filters['sort_by'] ?? 'id', 'sort_order' => $filters['sort_order'] ?? 'desc']) }}"
                            class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-300 transition">
                            Clear
                        </a>
                    @endif
                </form>
                @if (isset($filters['search']) && $filters['search'])
                    <div class="mt-2 text-xs text-slate-500">
                        Searching for: <strong>{{ $filters['search'] }}</strong>
                    </div>
                @endif
            </div>

            <!-- Total Count Display -->
            @if ($products->total() > 0)
                <div class="text-sm text-slate-600">
                    <strong>Total Records:</strong> {{ $products->total() }} product(s)
                    @if ($products->count() > 0)
                        <span class="ml-4">
                            Showing {{ ($products->currentPage() - 1) * $products->perPage() + 1 }} to
                            {{ min($products->currentPage() * $products->perPage(), $products->total()) }} of
                            {{ $products->total() }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm overflow-x-auto">
            <table class="w-full text-left min-w-full">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 w-16 sticky left-0 bg-slate-50 z-10">#</th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-slate-100 transition select-none"
                            @click="handleSort('product_type')">
                            <div class="flex items-center gap-2">
                                Product Type
                                <span x-show="sortBy === 'product_type'" class="text-blue-600"
                                    x-text="sortOrder === 'asc' ? '↑' : '↓'"></span>
                            </div>
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-slate-100 transition select-none"
                            @click="handleSort('unit_id')">
                            <div class="flex items-center gap-2">
                                Unit ID
                                <span x-show="sortBy === 'unit_id'" class="text-blue-600"
                                    x-text="sortOrder === 'asc' ? '↑' : '↓'"></span>
                            </div>
                        </th>
                        <th class="px-4 py-3">Hold Status</th>
                        <th class="px-4 py-3">Hold Branch</th>
                        <th class="px-4 py-3">Salesman</th>
                        <th class="px-4 py-3">Opportunity Name</th>
                        <th class="px-4 py-3">Hold Expiration</th>
                        <th class="px-4 py-3">Brand</th>
                        <th class="px-4 py-3">Model Number</th>
                        <th class="px-4 py-3">Est Completion</th>
                        <th class="px-4 py-3">Total Cost</th>
                        <th class="px-4 py-3">Tariff Cost</th>
                        <th class="px-4 py-3">Sales Order #</th>
                        <th class="px-4 py-3">IPAS CPQ #</th>
                        <th class="px-4 py-3">CPS PO #</th>
                        <th class="px-4 py-3">Ship Date</th>
                        <th class="px-4 py-3">Voltage</th>
                        <th class="px-4 py-3">Phase</th>
                        <th class="px-4 py-3">Enclosure</th>
                        <th class="px-4 py-3">Enclosure Type</th>
                        <th class="px-4 py-3">Tank</th>
                        <th class="px-4 py-3">Controller Series</th>
                        <th class="px-4 py-3">Breakers</th>
                        <th class="px-4 py-3">Serial #</th>
                        <th class="px-4 py-3">Notes</th>
                        <th class="px-4 py-3">Tech Spec</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @if ($products->count() > 0)
                        @foreach ($products as $index => $product)
                            @php
                                $rowNumber = ($products->currentPage() - 1) * $products->perPage() + $index + 1;
                            @endphp
                            <tr
                                class="hover:bg-slate-50 transition border-b border-slate-100 last:border-0 text-sm text-slate-700">
                                <td class="px-4 py-3 text-slate-500 font-medium sticky left-0 bg-white z-10">
                                    {{ $rowNumber }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $product->product_type === 'Generators' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $product->product_type === 'Switch' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $product->product_type === 'Docking Stations' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $product->product_type === 'Other' ? 'bg-gray-100 text-gray-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $product->product_type ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium">{{ $product->unit_id ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->hold_status ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->hold_branch ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->salesman ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->opportunity_name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->hold_expiration_date ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->brand ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->model_number ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->est_completion_date ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    {{ $product->total_cost ? '$' . number_format($product->total_cost, 2) : '-' }}</td>
                                <td class="px-4 py-3">
                                    {{ $product->tariff_cost ? '$' . number_format($product->tariff_cost, 2) : '-' }}</td>
                                <td class="px-4 py-3">{{ $product->sales_order_number ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->ipas_cpq_number ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->cps_po_number ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->ship_date ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->voltage ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->phase ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->enclosure ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->enclosure_type ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->tank ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->controller_series ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $product->breakers ?? '-' }}</td>
                                <td class="px-4 py-3 font-mono text-slate-500">{{ $product->serial_number ?? '-' }}</td>
                                <td class="px-4 py-3 max-w-xs truncate" title="{{ $product->notes ?? '' }}">
                                    {{ $product->notes ?? '-' }}</td>
                                <td class="px-4 py-3 max-w-xs truncate" title="{{ $product->tech_spec ?? '' }}">
                                    {{ $product->tech_spec ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                        class="text-blue-600 hover:text-blue-800 text-sm">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="28" class="px-6 py-12 text-center text-slate-400">
                                No products found.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($products->hasPages())
            <div class="mt-6 flex flex-col items-center gap-4">
                <div class="text-sm text-slate-600">
                    Page {{ $products->currentPage() }} of {{ $products->lastPage() }}
                </div>

                <div class="flex items-center gap-2 flex-wrap justify-center">
                    @if ($products->onFirstPage())
                        <span
                            class="px-3 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed">««</span>
                        <span
                            class="px-3 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed">‹</span>
                    @else
                        <a href="{{ $products->url(1) }}"
                            class="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition">««</a>
                        <a href="{{ $products->previousPageUrl() }}"
                            class="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition">‹</a>
                    @endif

                    @foreach ($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                        <a href="{{ $url }}"
                            class="px-4 py-2 rounded border transition {{ $page == $products->currentPage() ? 'bg-blue-600 text-white border-blue-600 font-semibold' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' }}">
                            {{ $page }}
                        </a>
                    @endforeach

                    @if ($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}"
                            class="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition">›</a>
                        <a href="{{ $products->url($products->lastPage()) }}"
                            class="px-3 py-2 rounded border bg-white text-slate-600 border-slate-300 hover:bg-slate-50 transition">»»</a>
                    @else
                        <span
                            class="px-3 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed">›</span>
                        <span
                            class="px-3 py-2 rounded border bg-white text-slate-400 border-slate-200 cursor-not-allowed">»»</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('productAdmin', () => ({
                search: '{{ $filters['search'] ?? '' }}',
                sortBy: '{{ $filters['sort_by'] ?? 'id' }}',
                sortOrder: '{{ $filters['sort_order'] ?? 'desc' }}',
                processingExcel: false,
                selectedFile: null,
                handleFileUpload(event) {
                    this.selectedFile = event.target.files[0];
                },
                async handleExcelUpload(event) {
                    event.preventDefault();
                    if (!this.selectedFile) return;

                    this.processingExcel = true;
                    try {
                        // Wait for XLSX to be available
                        if (typeof XLSX === 'undefined') {
                            await new Promise((resolve) => {
                                const checkXLSX = setInterval(() => {
                                    if (typeof XLSX !== 'undefined') {
                                        clearInterval(checkXLSX);
                                        resolve();
                                    }
                                }, 100);
                            });
                        }
                        const file = this.selectedFile;

                        const reader = new FileReader();
                        reader.onload = (e) => {
                            try {
                                const data = new Uint8Array(e.target.result);
                                const workbook = XLSX.read(data, {
                                    type: 'array'
                                });

                                // Check for required sheets
                                const requiredSheets = ['Generators', 'Switch',
                                    'Docking Stations', 'Other'
                                ];
                                const missingSheets = requiredSheets.filter(sheet => !workbook
                                    .SheetNames.includes(sheet));

                                if (missingSheets.length > 0) {
                                    alert(
                                        `The Excel file must contain all required sheets. Missing: ${missingSheets.join(', ')}`);
                                    this.processingExcel = false;
                                    return;
                                }

                                let allProducts = [];

                                // Helper function to parse a sheet
                                const parseSheet = (sheetName, productType, columnMapping) => {
                                    const worksheet = workbook.Sheets[sheetName];
                                    if (!worksheet) return [];

                                    const jsonData = XLSX.utils.sheet_to_json(worksheet, {
                                        header: 1,
                                        defval: null,
                                        raw: false
                                    });

                                    if (jsonData.length < 6) {
                                        console.warn(
                                            `${sheetName} sheet has insufficient rows. Skipping.`
                                            );
                                        return [];
                                    }

                                    const rows = jsonData.slice(
                                    5); // Data starts from row 6

                                    return rows
                                        .filter((row) => row && row.some(cell => cell !==
                                            null && cell !== ''))
                                        .map((row) => {
                                            const product = {
                                                product_type: productType
                                            };
                                            Object.keys(columnMapping).forEach(key => {
                                                const mapper = columnMapping[
                                                    key];
                                                product[key] = mapper(row);
                                            });
                                            return product;
                                        })
                                        .filter(product => product.unit_id && product
                                            .unit_id.trim() !== '');
                                };

                                const cleanValue = (value) => {
                                    if (value === null || value === undefined || value ===
                                        '') return null;
                                    const str = String(value).trim();
                                    return str === '' ? null : str;
                                };

                                const parseDate = (value) => {
                                    if (!value) return null;
                                    if (typeof value === 'number') {
                                        const excelEpoch = new Date(1899, 11, 30);
                                        const date = new Date(excelEpoch.getTime() + value *
                                            24 * 60 * 60 * 1000);
                                        if (!isNaN(date.getTime())) {
                                            return date.toISOString().split('T')[0];
                                        }
                                    }
                                    if (typeof value === 'string') {
                                        const date = new Date(value);
                                        if (!isNaN(date.getTime())) {
                                            return date.toISOString().split('T')[0];
                                        }
                                    }
                                    return null;
                                };

                                const parseInteger = (value) => {
                                    if (value === null || value === undefined || value ===
                                        '') return null;
                                    const num = parseInt(value);
                                    return isNaN(num) ? null : num;
                                };

                                const parseIntegerAsString = (value) => {
                                    if (value === null || value === undefined || value ===
                                        '') return null;
                                    const num = parseInt(value);
                                    return isNaN(num) ? null : String(num);
                                };

                                const parseNumeric = (value) => {
                                    if (value === null || value === undefined || value ===
                                        '') return null;
                                    const num = parseFloat(value);
                                    return isNaN(num) ? null : num;
                                };

                                // Parse Generators sheet
                                const generatorsMapping = {
                                    hold_status: (row) => cleanValue(row[0]),
                                    hold_branch: (row) => parseIntegerAsString(row[1]),
                                    salesman: (row) => cleanValue(row[2]),
                                    opportunity_name: (row) => cleanValue(row[3]),
                                    brand: (row) => cleanValue(row[4]),
                                    model_number: (row) => cleanValue(row[5]),
                                    location: (row) => cleanValue(row[6]),
                                    ipas_cpq_number: (row) => cleanValue(row[7]),
                                    cps_po_number: (row) => cleanValue(row[8]),
                                    enclosure: (row) => cleanValue(row[9]),
                                    enclosure_type: (row) => cleanValue(row[10]),
                                    tank: (row) => cleanValue(row[11]),
                                    controller_series: (row) => cleanValue(row[12]),
                                    breakers: (row) => cleanValue(row[13]),
                                    notes: (row) => cleanValue(row[14]),
                                    application_group: (row) => cleanValue(row[15]),
                                    engine_model: (row) => cleanValue(row[16]),
                                    unit_specification: (row) => cleanValue(row[17]),
                                    ibc_certification: (row) => cleanValue(row[18]),
                                    exhaust_emissions: (row) => cleanValue(row[19]),
                                    temp_rise: (row) => cleanValue(row[20]),
                                    description: (row) => cleanValue(row[21]),
                                    fuel_type: (row) => cleanValue(row[22]),
                                    voltage: (row) => parseIntegerAsString(row[23]),
                                    phase: (row) => parseIntegerAsString(row[24]),
                                    serial_number: (row) => cleanValue(row[25]),
                                    unit_id: (row) => cleanValue(row[26]),
                                    power: (row) => parseInteger(row[27]),
                                    engine_speed: (row) => parseInteger(row[28]),
                                    radiator_design_temp: (row) => parseInteger(row[29]),
                                    frequency: (row) => parseInteger(row[30]),
                                    full_load_amps: (row) => parseInteger(row[31]),
                                    tech_spec: (row) => cleanValue(row[32]),
                                    date_hold_added: (row) => parseDate(row[33]),
                                    hold_expiration_date: (row) => parseDate(row[34]),
                                    est_completion_date: (row) => parseDate(row[35]),
                                    ship_date: (row) => parseDate(row[36]),
                                    total_cost: (row) => parseNumeric(row[37]),
                                    retail_cost: (row) => parseNumeric(row[38]),
                                    tariff_cost: (row) => parseNumeric(row[39]),
                                    sales_order_number: (row) => parseIntegerAsString(row[
                                        40]),
                                };
                                allProducts = allProducts.concat(parseSheet('Generators',
                                    'Generators', generatorsMapping));

                                // Parse Switch sheet
                                const switchMapping = {
                                    hold_status: (row) => cleanValue(row[0]),
                                    hold_branch: (row) => parseIntegerAsString(row[1]),
                                    salesman: (row) => cleanValue(row[2]),
                                    hold_expiration_date: (row) => parseDate(row[3]),
                                    location: (row) => cleanValue(row[4]),
                                    brand: (row) => cleanValue(row[5]),
                                    transition_type: (row) => cleanValue(row[6]),
                                    enclosure_type: (row) => cleanValue(row[7]),
                                    bypass_isolation: (row) => cleanValue(row[8]),
                                    service_entrance_rated: (row) => cleanValue(row[9]),
                                    contactor_type: (row) => cleanValue(row[10]),
                                    controller_model: (row) => cleanValue(row[11]),
                                    communications_type: (row) => cleanValue(row[12]),
                                    accessories: (row) => cleanValue(row[13]),
                                    catalog_number: (row) => cleanValue(row[14]),
                                    serial_number: (row) => cleanValue(row[15]),
                                    quote_number: (row) => cleanValue(row[16]),
                                    number_of_poles: (row) => cleanValue(row[17]),
                                    description: (row) => cleanValue(row[18]),
                                    amperage: (row) => parseInteger(row[19]),
                                    voltage: (row) => parseIntegerAsString(row[20]),
                                    phase: (row) => parseIntegerAsString(row[21]),
                                    unit_id: (row) => cleanValue(row[22]),
                                    date_hold_added: (row) => parseDate(row[23]),
                                    est_completion_date: (row) => parseDate(row[24]),
                                    retail_cost: (row) => parseNumeric(row[25]),
                                    total_cost: (row) => parseNumeric(row[26]),
                                };
                                allProducts = allProducts.concat(parseSheet('Switch', 'Switch',
                                    switchMapping));

                                // Parse Docking Stations sheet
                                const dockingStationsMapping = {
                                    hold_status: (row) => cleanValue(row[0]),
                                    hold_branch: (row) => parseIntegerAsString(row[1]),
                                    salesman: (row) => cleanValue(row[2]),
                                    hold_expiration_date: (row) => parseDate(row[3]),
                                    location: (row) => cleanValue(row[4]),
                                    brand: (row) => cleanValue(row[5]),
                                    enclosure_type: (row) => cleanValue(row[6]),
                                    contactor_type: (row) => cleanValue(row[7]),
                                    accessories: (row) => cleanValue(row[8]),
                                    catalog_number: (row) => cleanValue(row[9]),
                                    serial_number: (row) => cleanValue(row[10]),
                                    quote_number: (row) => cleanValue(row[11]),
                                    circuit_breaker_type: (row) => cleanValue(row[12]),
                                    description: (row) => cleanValue(row[13]),
                                    amperage: (row) => parseInteger(row[14]),
                                    voltage: (row) => parseIntegerAsString(row[15]),
                                    phase: (row) => parseIntegerAsString(row[16]),
                                    unit_id: (row) => cleanValue(row[17]),
                                    date_hold_added: (row) => parseDate(row[18]),
                                    est_completion_date: (row) => parseDate(row[19]),
                                    retail_cost: (row) => parseNumeric(row[20]),
                                    total_cost: (row) => parseNumeric(row[21]),
                                };
                                allProducts = allProducts.concat(parseSheet('Docking Stations',
                                    'Docking Stations', dockingStationsMapping));

                                // Parse Other sheet
                                const otherMapping = {
                                    hold_status: (row) => cleanValue(row[0]),
                                    hold_branch: (row) => parseIntegerAsString(row[1]),
                                    salesman: (row) => cleanValue(row[2]),
                                    location: (row) => cleanValue(row[3]),
                                    brand: (row) => cleanValue(row[4]),
                                    serial_number: (row) => cleanValue(row[5]),
                                    description: (row) => cleanValue(row[6]),
                                    unit_id: (row) => cleanValue(row[7]),
                                    hold_expiration_date: (row) => parseDate(row[8]),
                                    date_hold_added: (row) => parseDate(row[9]),
                                    retail_cost: (row) => parseNumeric(row[10]),
                                    total_cost: (row) => parseNumeric(row[11]),
                                };
                                allProducts = allProducts.concat(parseSheet('Other', 'Other',
                                    otherMapping));

                                if (allProducts.length === 0) {
                                    alert(
                                        'No valid products found in any sheet of the Excel file.');
                                    this.processingExcel = false;
                                    return;
                                }

                                console.log('Total products to import:', allProducts.length);
                                console.log('Products by type:', {
                                    Generators: allProducts.filter(p => p
                                        .product_type === 'Generators').length,
                                    Switch: allProducts.filter(p => p.product_type ===
                                        'Switch').length,
                                    'Docking Stations': allProducts.filter(p => p
                                            .product_type === 'Docking Stations')
                                        .length,
                                    Other: allProducts.filter(p => p.product_type ===
                                        'Other').length,
                                });

                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = '/admin/products/import';

                                const tokenInput = document.createElement('input');
                                tokenInput.type = 'hidden';
                                tokenInput.name = '_token';
                                tokenInput.value = document.querySelector(
                                    'meta[name="csrf-token"]').content;
                                form.appendChild(tokenInput);

                                const productsInput = document.createElement('input');
                                productsInput.type = 'hidden';
                                productsInput.name = 'products';
                                productsInput.value = JSON.stringify(allProducts);
                                form.appendChild(productsInput);

                                document.body.appendChild(form);
                                form.submit();
                            } catch (error) {
                                alert('Error parsing Excel file: ' + error.message);
                                this.processingExcel = false;
                            }
                        };
                        reader.onerror = () => {
                            alert('Error reading file.');
                            this.processingExcel = false;
                        };
                        reader.readAsArrayBuffer(this.selectedFile);
                    } catch (error) {
                        alert('Error loading XLSX library: ' + error.message);
                        this.processingExcel = false;
                    }
                },
                handleSort(column) {
                    this.sortBy = column;
                    this.sortOrder = (this.sortBy === column && this.sortOrder === 'asc') ? 'desc' :
                        'asc';
                    window.location.href = '/admin/products?search=' + encodeURIComponent(this.search) +
                        '&sort_by=' + column + '&sort_order=' + this.sortOrder;
                }
            }));
        });
    </script>
@endsection
