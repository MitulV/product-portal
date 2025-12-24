@extends('admin.layout')

@section('title', 'Gallery - Admin')

@section('header', 'Gallery')

@section('content')
    <div x-data="galleryAdmin()">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Gallery</h1>
                    <p class="text-slate-600 mt-1">Upload images and documents for products</p>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Upload Files</h2>

                <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data"
                    @submit="handleSubmit" class="space-y-6">
                    @csrf
                    <!-- Product Selection -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Select Product(s) <span class="text-red-500">*</span>
                        </label>

                        <!-- Product Selection Errors -->
                        <div x-show="validationErrors.product_ids || serverErrors.product_ids"
                            class="mb-2 text-sm text-red-600">
                            <span
                                x-text="validationErrors.product_ids || (Array.isArray(serverErrors.product_ids) ? serverErrors.product_ids.join(', ') : serverErrors.product_ids)"></span>
                        </div>

                        <!-- Selected Products Tags -->
                        <div x-show="selectedProducts.length > 0" class="flex flex-wrap gap-2 mb-2">
                            <template x-for="productId in selectedProducts" :key="productId">
                                <span
                                    class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-lg text-sm">
                                    <span x-text="products.find(p => p.id === productId)?.unit_id || 'Unknown'"></span>
                                    <button type="button" @click="removeProduct(productId)"
                                        class="hover:text-blue-900 font-bold">
                                        ×
                                    </button>
                                </span>
                            </template>
                        </div>

                        <!-- Searchable Dropdown -->
                        <div class="relative">
                            <div @click="isOpen = !isOpen"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer bg-white flex items-center justify-between min-h-[42px]">
                                <input type="text" placeholder="Search and select products..." x-model="searchQuery"
                                    @input="isOpen = true" @focus="isOpen = true" class="flex-1 outline-none bg-transparent"
                                    @click.stop />
                                <svg :class="{ 'rotate-180': isOpen }" class="w-5 h-5 text-slate-400 transition-transform"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            <div x-show="isOpen" x-cloak @click.away="isOpen = false"
                                class="absolute z-50 w-full mt-1 bg-white border border-slate-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                                <template x-if="filteredProducts.length === 0">
                                    <div class="px-4 py-3 text-sm text-slate-500 text-center">
                                        No products found
                                    </div>
                                </template>
                                <template x-for="product in filteredProducts" :key="product.id">
                                    <div @click="toggleProduct(product.id)"
                                        :class="selectedProducts.includes(product.id) ? 'bg-blue-50' : ''"
                                        class="px-4 py-2 cursor-pointer hover:bg-slate-50 flex items-center gap-2">
                                        <input type="checkbox" :checked="selectedProducts.includes(product.id)"
                                            @change="toggleProduct(product.id)"
                                            class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                                            @click.stop />
                                        <span class="text-sm text-slate-900"
                                            x-text="product.unit_id + (product.brand ? ' - ' + product.brand : '') + (product.model_number ? ' (' + product.model_number + ')' : '')"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload Section -->
                    <div class="border-t border-slate-200 pt-6">
                        <h3 class="text-md font-semibold text-slate-900 mb-3">Images</h3>
                        <div class="space-y-3">
                            <input type="file" accept="image/*" multiple @change="handleImageChange" name="images[]"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent border-slate-300" />
                            <p class="text-xs text-slate-500">
                                Maximum file size: 10MB per file
                            </p>

                            <div x-show="imageFiles.length > 0" class="text-sm text-slate-600 space-y-1">
                                <div x-text="imageFiles.length + ' image(s) selected'"></div>
                                <template x-for="(file, index) in imageFiles" :key="index">
                                    <div class="text-xs"
                                        :class="file.size > 10 * 1024 * 1024 ? 'text-red-600 font-semibold' : 'text-slate-500'">
                                        <span
                                            x-text="file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)'"></span>
                                        <span x-show="file.size > 10 * 1024 * 1024"> - File too large! Maximum is
                                            10MB.</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Document Upload Section -->
                    <div class="border-t border-slate-200 pt-6">
                        <h3 class="text-md font-semibold text-slate-900 mb-3">Documents</h3>
                        <div class="space-y-3">
                            <input type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt" multiple
                                @change="handleDocumentChange" name="documents[]"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent border-slate-300" />
                            <p class="text-xs text-slate-500">
                                Accepted formats: PDF, DOC, DOCX, XLS, XLSX, TXT. Maximum file size: 10MB per file
                            </p>

                            <div x-show="documentFiles.length > 0" class="text-sm text-slate-600 space-y-1">
                                <div x-text="documentFiles.length + ' document(s) selected'"></div>
                                <template x-for="(file, index) in documentFiles" :key="index">
                                    <div class="text-xs"
                                        :class="file.size > 10 * 1024 * 1024 ? 'text-red-600 font-semibold' : 'text-slate-500'">
                                        <span
                                            x-text="file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)'"></span>
                                        <span x-show="file.size > 10 * 1024 * 1024"> - File too large! Maximum is
                                            10MB.</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- General Form Errors -->
                    <div x-show="validationErrors.files || serverErrors.message"
                        class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 text-sm">
                        <span x-text="validationErrors.files || serverErrors.message"></span>
                    </div>

                    <!-- Submit Button -->
                    <div class="border-t border-slate-200 pt-6">
                        <button type="submit" :disabled="processing"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!processing">Upload Files</span>
                            <span x-show="processing">Uploading...</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Gallery List -->
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Uploaded Files</h2>

                @if ($galleries->count() === 0)
                    <p class="text-slate-500 text-center py-8">No files uploaded yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($galleries as $gallery)
                            <div
                                class="flex items-center justify-between p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                                <div class="flex items-center gap-4 flex-1">
                                    @if ($gallery->file_type === 'image')
                                        <div
                                            class="w-16 h-16 rounded-lg overflow-hidden bg-slate-100 flex items-center justify-center">
                                            <img src="{{ $gallery->file_url }}" alt="{{ $gallery->file_name }}"
                                                class="w-full h-full object-cover"
                                                onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\'text-slate-400 text-xs\'>Image</span>';" />
                                        </div>
                                    @else
                                        @php
                                            $extension = strtolower(pathinfo($gallery->file_name, PATHINFO_EXTENSION));
                                        @endphp
                                        <div
                                            class="w-16 h-16 rounded-lg flex items-center justify-center relative overflow-hidden
                                            @if ($extension === 'pdf') bg-red-50 border-2 border-red-200
                                            @elseif(in_array($extension, ['doc', 'docx'])) bg-blue-50 border-2 border-blue-200
                                            @elseif(in_array($extension, ['xls', 'xlsx'])) bg-green-50 border-2 border-green-200
                                            @else bg-slate-50 border-2 border-slate-200 @endif">
                                            @if ($extension === 'pdf')
                                                <!-- PDF Icon -->
                                                <svg class="w-10 h-10 text-red-600" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                                                    <path d="M8,10H16V12H8V10M8,13H13V15H8V13M8,16H16V18H8V16Z" />
                                                </svg>
                                            @elseif(in_array($extension, ['doc', 'docx']))
                                                <!-- Word Document Icon -->
                                                <svg class="w-10 h-10 text-blue-600" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                                                    <path d="M8,10H16V12H8V10M8,13H13V15H8V13M8,16H16V18H8V16Z" />
                                                </svg>
                                            @elseif(in_array($extension, ['xls', 'xlsx']))
                                                <!-- Excel Icon -->
                                                <svg class="w-10 h-10 text-green-600" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                                                    <path d="M8,10H16V12H8V10M8,13H13V15H8V13M8,16H16V18H8V16Z" />
                                                </svg>
                                            @elseif($extension === 'txt')
                                                <!-- Text File Icon -->
                                                <svg class="w-10 h-10 text-slate-600" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                                                    <path d="M8,10H16V12H8V10M8,13H16V15H8V13M8,16H16V18H8V16Z" />
                                                </svg>
                                            @else
                                                <!-- Generic Document Icon -->
                                                <svg class="w-10 h-10 text-slate-500" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                                                    <path d="M8,10H16V12H8V10M8,13H13V15H8V13M8,16H16V18H8V16Z" />
                                                </svg>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <div class="font-medium text-slate-900">
                                            {{ $gallery->product->unit_id ?? 'Unknown Product' }}
                                        </div>
                                        <div class="text-sm text-slate-600">{{ $gallery->file_name }}</div>
                                        <div class="text-xs text-slate-500 mt-1">
                                            {{ $gallery->file_type === 'image' ? 'Image' : 'Document' }} •
                                            Uploaded {{ $gallery->created_at->format('M d, Y') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <a href="{{ $gallery->file_url }}" target="_blank" rel="noopener noreferrer"
                                        class="px-4 py-2 text-sm bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition">
                                        View
                                    </a>
                                    <form method="POST" action="{{ route('admin.gallery.destroy', $gallery->id) }}"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            onclick="return confirm('Are you sure you want to delete this file?')"
                                            class="px-4 py-2 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Pagination -->
                @if ($galleries->hasPages())
                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-slate-600">
                            Showing {{ $galleries->firstItem() }} to {{ $galleries->lastItem() }} of
                            {{ $galleries->total() }} files
                        </div>
                        <div class="flex gap-2">
                            @if ($galleries->onFirstPage())
                                <span
                                    class="px-3 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-400 cursor-not-allowed">Previous</span>
                            @else
                                <a href="{{ $galleries->previousPageUrl() }}"
                                    class="px-3 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">Previous</a>
                            @endif

                            @foreach ($galleries->getUrlRange(max(1, $galleries->currentPage() - 2), min($galleries->lastPage(), $galleries->currentPage() + 2)) as $page => $url)
                                <a href="{{ $url }}"
                                    class="px-3 py-2 rounded-lg text-sm font-medium {{ $page == $galleries->currentPage() ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                    {{ $page }}
                                </a>
                            @endforeach

                            @if ($galleries->hasMorePages())
                                <a href="{{ $galleries->nextPageUrl() }}"
                                    class="px-3 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">Next</a>
                            @else
                                <span
                                    class="px-3 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-400 cursor-not-allowed">Next</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            const allProducts = @json($products);

            Alpine.data('galleryAdmin', () => ({
                products: allProducts,
                selectedProducts: [],
                imageFiles: [],
                documentFiles: [],
                searchQuery: '',
                isOpen: false,
                validationErrors: {},
                serverErrors: {},
                processing: false,
                toggleProduct(productId) {
                    const id = parseInt(productId);
                    if (this.selectedProducts.includes(id)) {
                        this.selectedProducts = this.selectedProducts.filter(pid => pid !== id);
                    } else {
                        this.selectedProducts.push(id);
                    }
                    if (this.validationErrors.product_ids || this.serverErrors.product_ids) {
                        delete this.validationErrors.product_ids;
                        delete this.serverErrors.product_ids;
                    }
                },
                removeProduct(productId) {
                    this.selectedProducts = this.selectedProducts.filter(id => id !== productId);
                    if (this.validationErrors.product_ids || this.serverErrors.product_ids) {
                        delete this.validationErrors.product_ids;
                        delete this.serverErrors.product_ids;
                    }
                },
                get filteredProducts() {
                    const query = this.searchQuery.toLowerCase();
                    return this.products.filter(product => {
                        const unitId = (product.unit_id || '').toLowerCase();
                        const brand = (product.brand || '').toLowerCase();
                        const model = (product.model_number || '').toLowerCase();
                        return unitId.includes(query) || brand.includes(query) || model
                            .includes(query);
                    });
                },
                handleImageChange(event) {
                    console.log('=== Image Input Changed ===');
                    const files = event.target.files;
                    console.log('Image files selected:', {
                        filesCount: files ? files.length : 0,
                        files: files ? Array.from(files).map(f => ({
                            name: f.name,
                            size: f.size,
                            type: f.type
                        })) : []
                    });
                    this.imageFiles = files ? Array.from(files) : [];
                    console.log('Alpine imageFiles updated:', this.imageFiles.length);
                    // Clear file validation errors when files are selected
                    if (this.validationErrors.files) {
                        delete this.validationErrors.files;
                    }
                    if (this.serverErrors.message) {
                        delete this.serverErrors.message;
                    }
                },
                handleDocumentChange(event) {
                    console.log('=== Document Input Changed ===');
                    const files = event.target.files;
                    console.log('Document files selected:', {
                        filesCount: files ? files.length : 0,
                        files: files ? Array.from(files).map(f => ({
                            name: f.name,
                            size: f.size,
                            type: f.type
                        })) : []
                    });
                    this.documentFiles = files ? Array.from(files) : [];
                    console.log('Alpine documentFiles updated:', this.documentFiles.length);
                    // Clear file validation errors when files are selected
                    if (this.validationErrors.files) {
                        delete this.validationErrors.files;
                    }
                    if (this.serverErrors.message) {
                        delete this.serverErrors.message;
                    }
                },
                handleSubmit(event) {
                    console.log('=== Gallery Upload: Form Submit Started ===');
                    this.validationErrors = {};
                    this.serverErrors = {};

                    // Log Alpine.js state
                    console.log('Alpine.js State:', {
                        selectedProducts: this.selectedProducts,
                        selectedProductsCount: this.selectedProducts.length,
                        imageFiles: this.imageFiles,
                        imageFilesCount: this.imageFiles.length,
                        documentFiles: this.documentFiles,
                        documentFilesCount: this.documentFiles.length
                    });

                    if (this.selectedProducts.length === 0) {
                        console.error('Validation failed: No products selected');
                        event.preventDefault();
                        this.validationErrors.product_ids = 'Please select at least one product.';
                        return;
                    }

                    // Check actual file inputs instead of relying on Alpine arrays
                    const form = event.target;
                    const imageInput = form.querySelector('input[name="images[]"]');
                    const documentInput = form.querySelector('input[name="documents[]"]');

                    console.log('File Inputs Found:', {
                        imageInput: imageInput ? 'Found' : 'NOT FOUND',
                        documentInput: documentInput ? 'Found' : 'NOT FOUND',
                        imageInputFiles: imageInput ? (imageInput.files ? imageInput.files
                            .length : 'no files property') : 'N/A',
                        documentInputFiles: documentInput ? (documentInput.files ? documentInput
                            .files.length : 'no files property') : 'N/A'
                    });

                    if (imageInput && imageInput.files) {
                        console.log('Image files details:', {
                            length: imageInput.files.length,
                            files: Array.from(imageInput.files).map(f => ({
                                name: f.name,
                                size: f.size,
                                type: f.type
                            }))
                        });
                    }

                    if (documentInput && documentInput.files) {
                        console.log('Document files details:', {
                            length: documentInput.files.length,
                            files: Array.from(documentInput.files).map(f => ({
                                name: f.name,
                                size: f.size,
                                type: f.type
                            }))
                        });
                    }

                    const hasImages = imageInput && imageInput.files && imageInput.files.length > 0;
                    const hasDocuments = documentInput && documentInput.files && documentInput.files
                        .length > 0;

                    // Check for files that are too large (10MB limit)
                    const maxFileSize = 10 * 1024 * 1024; // 10MB in bytes
                    const oversizedFiles = [];

                    if (hasImages) {
                        Array.from(imageInput.files).forEach(file => {
                            if (file.size > maxFileSize) {
                                oversizedFiles.push(
                                    `Image "${file.name}" (${(file.size / 1024 / 1024).toFixed(2)} MB)`
                                );
                            }
                        });
                    }

                    if (hasDocuments) {
                        Array.from(documentInput.files).forEach(file => {
                            if (file.size > maxFileSize) {
                                oversizedFiles.push(
                                    `Document "${file.name}" (${(file.size / 1024 / 1024).toFixed(2)} MB)`
                                );
                            }
                        });
                    }

                    if (oversizedFiles.length > 0) {
                        console.error('Validation failed: Files too large', oversizedFiles);
                        event.preventDefault();
                        this.validationErrors.files = 'The following files exceed the 10MB limit: ' +
                            oversizedFiles.join(', ') +
                            '. Please compress or resize them before uploading.';
                        return;
                    }

                    console.log('File Validation Check:', {
                        hasImages: hasImages,
                        hasDocuments: hasDocuments,
                        willPreventSubmit: !hasImages && !hasDocuments
                    });

                    if (!hasImages && !hasDocuments) {
                        console.error('Validation failed: No files selected', {
                            imageInputExists: !!imageInput,
                            documentInputExists: !!documentInput,
                            imageInputFilesLength: imageInput ? (imageInput.files ? imageInput
                                .files.length : 'no files') : 'no input',
                            documentInputFilesLength: documentInput ? (documentInput.files ?
                                documentInput.files.length : 'no files') : 'no input'
                        });
                        event.preventDefault();
                        this.validationErrors.files =
                            'Please select at least one image or document to upload.';
                        return;
                    }

                    console.log('Validation passed, proceeding with form submission');
                    this.processing = true;

                    // Add hidden inputs for product_ids
                    this.selectedProducts.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'product_ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });

                    console.log('Form prepared for submission:', {
                        productIds: this.selectedProducts,
                        formAction: form.action,
                        formMethod: form.method,
                        formEnctype: form.enctype
                    });

                    // Log FormData before submission
                    const formData = new FormData(form);
                    console.log('FormData contents:', {
                        productIds: formData.getAll('product_ids[]'),
                        imageFiles: formData.getAll('images[]').length,
                        documentFiles: formData.getAll('documents[]').length,
                        allKeys: Array.from(formData.keys())
                    });

                    // Form will submit normally
                }
            }));
        });
    </script>
@endsection
