import React, { useState, useRef, useEffect } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function GalleryIndex({ products, galleries }) {
    const { flash } = usePage().props;
    const [selectedProducts, setSelectedProducts] = useState([]);
    const [imageFiles, setImageFiles] = useState([]);
    const [documentFiles, setDocumentFiles] = useState([]);
    const [searchQuery, setSearchQuery] = useState('');
    const [isOpen, setIsOpen] = useState(false);
    const dropdownRef = useRef(null);
    
    // Client-side validation errors
    const [validationErrors, setValidationErrors] = useState({});
    const [serverErrors, setServerErrors] = useState({});

    const { data, setData, post, processing, errors, reset } = useForm({
        product_ids: [],
        images: [],
        documents: [],
    });

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    const handleProductToggle = (productId) => {
        const id = parseInt(productId);
        setSelectedProducts(prev => {
            if (prev.includes(id)) {
                return prev.filter(pid => pid !== id);
            } else {
                return [...prev, id];
            }
        });
        // Clear product_ids error when user selects a product
        if (validationErrors.product_ids || serverErrors.product_ids) {
            setValidationErrors(prev => {
                const newErrors = { ...prev };
                delete newErrors.product_ids;
                return newErrors;
            });
            setServerErrors(prev => {
                const newErrors = { ...prev };
                delete newErrors.product_ids;
                return newErrors;
            });
        }
    };

    const removeProduct = (productId) => {
        setSelectedProducts(prev => prev.filter(id => id !== productId));
        // Clear product_ids error when user removes a product
        if (validationErrors.product_ids || serverErrors.product_ids) {
            setValidationErrors(prev => {
                const newErrors = { ...prev };
                delete newErrors.product_ids;
                return newErrors;
            });
            setServerErrors(prev => {
                const newErrors = { ...prev };
                delete newErrors.product_ids;
                return newErrors;
            });
        }
    };

    const filteredProducts = products.filter(product => {
        const searchLower = searchQuery.toLowerCase();
        const unitId = (product.unit_id || '').toLowerCase();
        const brand = (product.brand || '').toLowerCase();
        const model = (product.model_number || '').toLowerCase();
        return unitId.includes(searchLower) || brand.includes(searchLower) || model.includes(searchLower);
    });

    const validateImageFile = (file) => {
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!allowedTypes.includes(file.type)) {
            return 'Invalid image type. Allowed: JPEG, PNG, GIF, WebP';
        }
        if (file.size > maxSize) {
            return 'Image size must not exceed 10MB';
        }
        return null;
    };

    const validateDocumentFile = (file) => {
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain'
        ];
        const allowedExtensions = ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.txt'];
        
        const fileName = file.name.toLowerCase();
        const hasValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext));
        
        if (!hasValidExtension && !allowedTypes.includes(file.type)) {
            return 'Invalid document type. Allowed: PDF, DOC, DOCX, XLS, XLSX, TXT';
        }
        if (file.size > maxSize) {
            return 'Document size must not exceed 10MB';
        }
        return null;
    };

    const handleImageChange = (e) => {
        const files = Array.from(e.target.files);
        const errors = {};
        
        files.forEach((file, index) => {
            const error = validateImageFile(file);
            if (error) {
                errors[`images.${index}`] = error;
            }
        });

        if (Object.keys(errors).length > 0) {
            setValidationErrors(prev => ({ ...prev, ...errors }));
            e.target.value = ''; // Clear the input
            return;
        }

        // Clear any previous image errors
        setValidationErrors(prev => {
            const newErrors = { ...prev };
            Object.keys(newErrors).forEach(key => {
                if (key.startsWith('images.')) {
                    delete newErrors[key];
                }
            });
            return newErrors;
        });

        setImageFiles(files);
        // Set files in form data - Inertia will handle them
        setData('images', files);
    };

    const handleDocumentChange = (e) => {
        const files = Array.from(e.target.files);
        const errors = {};
        
        files.forEach((file, index) => {
            const error = validateDocumentFile(file);
            if (error) {
                errors[`documents.${index}`] = error;
            }
        });

        if (Object.keys(errors).length > 0) {
            setValidationErrors(prev => ({ ...prev, ...errors }));
            e.target.value = ''; // Clear the input
            return;
        }

        // Clear any previous document errors
        setValidationErrors(prev => {
            const newErrors = { ...prev };
            Object.keys(newErrors).forEach(key => {
                if (key.startsWith('documents.')) {
                    delete newErrors[key];
                }
            });
            return newErrors;
        });

        setDocumentFiles(files);
        // Set files in form data - Inertia will handle them
        setData('documents', files);
    };

    const validateForm = () => {
        const errors = {};

        // Validate products
        if (selectedProducts.length === 0) {
            errors.product_ids = 'Please select at least one product.';
        }

        // Validate files
        if (imageFiles.length === 0 && documentFiles.length === 0) {
            errors.files = 'Please select at least one image or document to upload.';
        }

        // Validate individual files
        imageFiles.forEach((file, index) => {
            const error = validateImageFile(file);
            if (error) {
                errors[`images.${index}`] = error;
            }
        });

        documentFiles.forEach((file, index) => {
            const error = validateDocumentFile(file);
            if (error) {
                errors[`documents.${index}`] = error;
            }
        });

        return errors;
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        
        console.log('Form submitted!', {
            selectedProducts,
            imageFiles: imageFiles.length,
            documentFiles: documentFiles.length,
            processing,
        });

        // Clear previous errors
        setValidationErrors({});
        setServerErrors({});

        // Client-side validation
        const clientErrors = validateForm();
        if (Object.keys(clientErrors).length > 0) {
            console.log('Client validation errors:', clientErrors);
            setValidationErrors(clientErrors);
            return;
        }

        // Ensure product_ids are integers
        const productIds = selectedProducts.map(id => parseInt(id));

        console.log('Preparing upload...', {
            productIds,
            imagesCount: imageFiles.length,
            documentsCount: documentFiles.length,
            imageFiles: imageFiles,
            documentFiles: documentFiles,
        });

        // Set form data - use useForm's post method which handles files and CSRF correctly
        // Set all data at once
        setData({
            product_ids: productIds,
            images: imageFiles,
            documents: documentFiles,
        });

        console.log('Form data set, calling post...');
        console.log('Files being sent:', {
            images: imageFiles.map(f => ({ name: f.name, type: f.type, size: f.size })),
            documents: documentFiles.map(f => ({ name: f.name, type: f.type, size: f.size })),
        });

        // Use useForm's post method - it handles files and CSRF automatically
        // We need to ensure the data is current, so we'll use a callback
        // Actually, let's use the form's current data by calling post directly
        // The form data should be set by now (setData is synchronous for the form state)
        post('/admin/gallery', {
            preserveScroll: true,
            forceFormData: true,
            onStart: () => {
                console.log('Upload started...');
            },
            onSuccess: () => {
                console.log('Upload successful!');
                setSelectedProducts([]);
                setImageFiles([]);
                setDocumentFiles([]);
                setSearchQuery('');
                setValidationErrors({});
                setServerErrors({});
                reset();
                const fileInputs = document.querySelectorAll('input[type="file"]');
                fileInputs.forEach(input => input.value = '');
            },
            onError: (errors) => {
                console.error('Upload errors:', errors);
                setServerErrors(errors || {});
            },
            onFinish: () => {
                console.log('Upload finished');
            },
        });
    };

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to delete this file?')) {
            router.delete(`/admin/gallery/${id}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <AdminLayout>
            <Head title="Gallery - Admin" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-slate-900">Gallery</h1>
                        <p className="text-slate-600 mt-1">Upload images and documents for products</p>
                    </div>
                </div>

                {/* Flash Messages */}
                {flash?.success && (
                    <div className="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
                        {flash.error}
                    </div>
                )}

                {/* Upload Form */}
                <div className="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
                    <h2 className="text-lg font-semibold text-slate-900 mb-4">Upload Files</h2>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Product Selection */}
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-2">
                                Select Product(s) <span className="text-red-500">*</span>
                            </label>
                            
                            {/* Product Selection Errors */}
                            {(validationErrors.product_ids || serverErrors.product_ids) && (
                                <div className="mb-2 text-sm text-red-600">
                                    {validationErrors.product_ids || 
                                     (Array.isArray(serverErrors.product_ids) 
                                        ? serverErrors.product_ids.join(', ') 
                                        : serverErrors.product_ids)}
                                </div>
                            )}
                            
                            {/* Selected Products Tags */}
                            {selectedProducts.length > 0 && (
                                <div className="flex flex-wrap gap-2 mb-2">
                                    {selectedProducts.map(productId => {
                                        const product = products.find(p => p.id === productId);
                                        if (!product) return null;
                                        return (
                                            <span
                                                key={productId}
                                                className="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-lg text-sm"
                                            >
                                                {product.unit_id}
                                                <button
                                                    type="button"
                                                    onClick={() => removeProduct(productId)}
                                                    className="hover:text-blue-900 font-bold"
                                                >
                                                    ×
                                                </button>
                                            </span>
                                        );
                                    })}
                                </div>
                            )}

                            {/* Searchable Dropdown */}
                            <div className="relative" ref={dropdownRef}>
                                <div
                                    onClick={() => setIsOpen(!isOpen)}
                                    className="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer bg-white flex items-center justify-between min-h-[42px]"
                                >
                                    <input
                                        type="text"
                                        placeholder={selectedProducts.length > 0 ? `${selectedProducts.length} product(s) selected` : "Search and select products..."}
                                        value={searchQuery}
                                        onChange={(e) => {
                                            setSearchQuery(e.target.value);
                                            setIsOpen(true);
                                        }}
                                        onFocus={() => setIsOpen(true)}
                                        className="flex-1 outline-none bg-transparent"
                                        onClick={(e) => e.stopPropagation()}
                                    />
                                    <svg
                                        className={`w-5 h-5 text-slate-400 transition-transform ${isOpen ? 'rotate-180' : ''}`}
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>

                                {isOpen && (
                                    <div className="absolute z-50 w-full mt-1 bg-white border border-slate-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                                        {filteredProducts.length === 0 ? (
                                            <div className="px-4 py-3 text-sm text-slate-500 text-center">
                                                No products found
                                            </div>
                                        ) : (
                                            filteredProducts.map((product) => {
                                                const isSelected = selectedProducts.includes(product.id);
                                                const displayText = `${product.unit_id}${product.brand ? ` - ${product.brand}` : ''}${product.model_number ? ` (${product.model_number})` : ''}`;
                                                
                                                return (
                                                    <div
                                                        key={product.id}
                                                        onClick={() => handleProductToggle(product.id)}
                                                        className={`px-4 py-2 cursor-pointer hover:bg-slate-50 flex items-center gap-2 ${
                                                            isSelected ? 'bg-blue-50' : ''
                                                        }`}
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            checked={isSelected}
                                                            onChange={() => handleProductToggle(product.id)}
                                                            className="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                                                            onClick={(e) => e.stopPropagation()}
                                                        />
                                                        <span className="text-sm text-slate-900">{displayText}</span>
                                                    </div>
                                                );
                                            })
                                        )}
                                    </div>
                                )}
                            </div>
                            {selectedProducts.length === 0 && (
                                <p className="text-xs text-red-500 mt-1">Please select at least one product</p>
                            )}
                        </div>

                        {/* Image Upload Section */}
                        <div className="border-t border-slate-200 pt-6">
                            <h3 className="text-md font-semibold text-slate-900 mb-3">Images</h3>
                            <div className="space-y-3">
                                <input
                                    type="file"
                                    accept="image/*"
                                    multiple
                                    onChange={handleImageChange}
                                    className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                                        (validationErrors.images || serverErrors.images) ? 'border-red-300' : 'border-slate-300'
                                    }`}
                                />
                                
                                {/* Image File Errors */}
                                {imageFiles.map((file, index) => {
                                    const error = validationErrors[`images.${index}`] || serverErrors[`images.${index}`];
                                    if (!error) return null;
                                    return (
                                        <div key={index} className="text-sm text-red-600">
                                            {file.name}: {Array.isArray(error) ? error.join(', ') : error}
                                        </div>
                                    );
                                })}
                                
                                {/* General Image Errors */}
                                {(validationErrors.images || serverErrors.images) && !imageFiles.some((_, i) => validationErrors[`images.${i}`] || serverErrors[`images.${i}`]) && (
                                    <div className="text-sm text-red-600">
                                        {Array.isArray(validationErrors.images || serverErrors.images) 
                                            ? (validationErrors.images || serverErrors.images).join(', ')
                                            : (validationErrors.images || serverErrors.images)}
                                    </div>
                                )}
                                
                                {imageFiles.length > 0 && (
                                    <div className="text-sm text-slate-600">
                                        {imageFiles.length} image(s) selected
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Document Upload Section */}
                        <div className="border-t border-slate-200 pt-6">
                            <h3 className="text-md font-semibold text-slate-900 mb-3">Documents</h3>
                            <div className="space-y-3">
                                <input
                                    type="file"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.txt"
                                    multiple
                                    onChange={handleDocumentChange}
                                    className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                                        (validationErrors.documents || serverErrors.documents) ? 'border-red-300' : 'border-slate-300'
                                    }`}
                                />
                                <p className="text-xs text-slate-500">
                                    Accepted formats: PDF, DOC, DOCX, XLS, XLSX, TXT
                                </p>
                                
                                {/* Document File Errors */}
                                {documentFiles.map((file, index) => {
                                    const error = validationErrors[`documents.${index}`] || serverErrors[`documents.${index}`];
                                    if (!error) return null;
                                    return (
                                        <div key={index} className="text-sm text-red-600">
                                            {file.name}: {Array.isArray(error) ? error.join(', ') : error}
                                        </div>
                                    );
                                })}
                                
                                {/* General Document Errors */}
                                {(validationErrors.documents || serverErrors.documents) && !documentFiles.some((_, i) => validationErrors[`documents.${i}`] || serverErrors[`documents.${i}`]) && (
                                    <div className="text-sm text-red-600">
                                        {Array.isArray(validationErrors.documents || serverErrors.documents) 
                                            ? (validationErrors.documents || serverErrors.documents).join(', ')
                                            : (validationErrors.documents || serverErrors.documents)}
                                    </div>
                                )}
                                
                                {documentFiles.length > 0 && (
                                    <div className="text-sm text-slate-600">
                                        {documentFiles.length} document(s) selected
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* General Form Errors */}
                        {(validationErrors.files || serverErrors.message) && (
                            <div className="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 text-sm">
                                {validationErrors.files || serverErrors.message}
                            </div>
                        )}

                        {/* General Form Errors */}
                        {(validationErrors.files || serverErrors.message) && (
                            <div className="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 text-sm">
                                {validationErrors.files || serverErrors.message}
                            </div>
                        )}

                        {/* Submit Button */}
                        <div className="border-t border-slate-200 pt-6">
                            <button
                                type="submit"
                                disabled={processing}
                                onClick={(e) => {
                                    console.log('Button clicked!', {
                                        processing,
                                        selectedProducts: selectedProducts.length,
                                        imageFiles: imageFiles.length,
                                        documentFiles: documentFiles.length,
                                    });
                                }}
                                className="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {processing ? 'Uploading...' : 'Upload Files'}
                            </button>
                            {selectedProducts.length === 0 && (
                                <p className="text-sm text-red-600 mt-2">Please select at least one product</p>
                            )}
                            {selectedProducts.length > 0 && imageFiles.length === 0 && documentFiles.length === 0 && (
                                <p className="text-sm text-red-600 mt-2">Please select at least one file to upload</p>
                            )}
                        </div>
                    </form>
                </div>

                {/* Gallery List */}
                <div className="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
                    <h2 className="text-lg font-semibold text-slate-900 mb-4">Uploaded Files</h2>

                    {galleries.data.length === 0 ? (
                        <p className="text-slate-500 text-center py-8">No files uploaded yet.</p>
                    ) : (
                        <div className="space-y-4">
                            {galleries.data.map((gallery) => (
                                <div
                                    key={gallery.id}
                                    className="flex items-center justify-between p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition"
                                >
                                    <div className="flex items-center gap-4 flex-1">
                                        {gallery.file_type === 'image' ? (
                                            <div className="w-16 h-16 rounded-lg overflow-hidden bg-slate-100 flex items-center justify-center">
                                                <img
                                                    src={gallery.file_url}
                                                    alt={gallery.file_name}
                                                    className="w-full h-full object-cover"
                                                    onError={(e) => {
                                                        e.target.style.display = 'none';
                                                        e.target.parentElement.innerHTML = '<span class="text-slate-400 text-xs">Image</span>';
                                                    }}
                                                />
                                            </div>
                                        ) : (
                                            <div className="w-16 h-16 rounded-lg bg-slate-100 flex items-center justify-center">
                                                <span className="text-slate-400 text-xs">📄</span>
                                            </div>
                                        )}
                                        <div className="flex-1">
                                            <div className="font-medium text-slate-900">
                                                {gallery.product?.unit_id || 'Unknown Product'}
                                            </div>
                                            <div className="text-sm text-slate-600">{gallery.file_name}</div>
                                            <div className="text-xs text-slate-500 mt-1">
                                                {gallery.file_type === 'image' ? 'Image' : 'Document'} • 
                                                Uploaded {new Date(gallery.created_at).toLocaleDateString()}
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <a
                                            href={gallery.file_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="px-4 py-2 text-sm bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition"
                                        >
                                            View
                                        </a>
                                        <button
                                            onClick={() => handleDelete(gallery.id)}
                                            className="px-4 py-2 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}

                    {/* Pagination */}
                    {galleries.links && galleries.links.length > 3 && (
                        <div className="mt-6 flex items-center justify-between">
                            <div className="text-sm text-slate-600">
                                Showing {galleries.from} to {galleries.to} of {galleries.total} files
                            </div>
                            <div className="flex gap-2">
                                {galleries.links.map((link, index) => (
                                    <Link
                                        key={index}
                                        href={link.url || '#'}
                                        className={`px-3 py-2 rounded-lg text-sm font-medium ${
                                            link.active
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                                        } ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}

