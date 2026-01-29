<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminGalleryController extends Controller
{
  public function index()
  {
    $products = Product::select('id', 'unit_id', 'brand', 'model_number')
      ->orderBy('unit_id')
      ->get();

    $galleries = ProductGallery::with('product:id,unit_id')
      ->orderBy('created_at', 'desc')
      ->paginate(20);

    return view('admin.gallery.index', [
      'products' => $products,
      'galleries' => $galleries,
    ]);
  }

  public function store(Request $request)
  {
    // Comprehensive logging of incoming request
    Log::info('=== Gallery Upload: Request Received ===', [
      'method' => $request->method(),
      'content_type' => $request->header('Content-Type'),
      'content_length' => $request->header('Content-Length'),
      'product_ids' => $request->input('product_ids'),
      'product_ids_count' => count($request->input('product_ids', [])),
      'has_images' => $request->hasFile('images'),
      'has_documents' => $request->hasFile('documents'),
      'has_thumbnail' => $request->hasFile('thumbnail'),
      'all_files_keys' => array_keys($request->allFiles()),
      'all_input_keys' => array_keys($request->all()),
      'images_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
      'documents_count' => $request->hasFile('documents') ? count($request->file('documents')) : 0,
    ]);

    // Log all files in the request
    $allFiles = $request->allFiles();
    Log::info('Gallery upload - All files in request', [
      'all_files_keys' => array_keys($allFiles),
      'all_files_count' => count($allFiles),
      'files_structure' => array_map(function ($files) {
        if (is_array($files)) {
          return array_map(function ($file) {
            if (!$file instanceof \Illuminate\Http\UploadedFile) {
              return 'not_uploaded_file';
            }
            try {
              $isValid = $file->isValid();
              $result = [
                'class' => get_class($file),
                'is_valid' => $isValid,
                'error' => $file->getError(),
              ];

              if ($isValid) {
                try {
                  $result['original_name'] = $file->getClientOriginalName();
                  $result['mime_type'] = $file->getMimeType();
                  $result['size'] = $file->getSize();
                } catch (\Exception $e) {
                  $result['error_getting_properties'] = $e->getMessage();
                }
              } else {
                try {
                  $result['error_message'] = $file->getErrorMessage();
                } catch (\Exception $e) {
                  $result['error_getting_message'] = $e->getMessage();
                }
              }

              return $result;
            } catch (\Exception $e) {
              return ['error' => $e->getMessage(), 'class' => get_class($file)];
            }
          }, $files);
        }
        if (!$files instanceof \Illuminate\Http\UploadedFile) {
          return 'not_uploaded_file';
        }
        try {
          return [
            'class' => get_class($files),
            'original_name' => $files->isValid() ? $files->getClientOriginalName() : 'invalid_file',
          ];
        } catch (\Exception $e) {
          return ['error' => $e->getMessage(), 'class' => get_class($files)];
        }
      }, $allFiles)
    ]);

    // Try multiple ways to access images
    $images = null;
    if ($request->hasFile('images')) {
      $images = $request->file('images');
      Log::info('Gallery upload - Images found via hasFile("images")', ['count' => is_array($images) ? count($images) : 1]);
    } elseif (isset($allFiles['images'])) {
      $images = $allFiles['images'];
      Log::info('Gallery upload - Images found via allFiles()["images"]', ['count' => is_array($images) ? count($images) : 1]);
    } elseif (isset($allFiles['images[]'])) {
      $images = $allFiles['images[]'];
      Log::info('Gallery upload - Images found via allFiles()["images[]"]', ['count' => is_array($images) ? count($images) : 1]);
    }

    if ($images) {
      $imagesArray = is_array($images) ? $images : [$images];
      Log::info('Gallery upload - Images details', [
        'count' => count($imagesArray),
        'files' => array_map(function ($file) {
          if (!$file instanceof \Illuminate\Http\UploadedFile) {
            return ['error' => 'Not an UploadedFile instance', 'type' => gettype($file)];
          }
          try {
            $isValid = $file->isValid();
            $result = [
              'is_valid' => $isValid,
              'error' => $file->getError(),
            ];

            if ($isValid) {
              try {
                $result['original_name'] = $file->getClientOriginalName();
                $result['mime_type'] = $file->getMimeType();
                $result['size'] = $file->getSize();
              } catch (\Exception $e) {
                $result['error_getting_properties'] = $e->getMessage();
              }
            } else {
              try {
                $result['error_message'] = $file->getErrorMessage();
              } catch (\Exception $e) {
                $result['error_getting_message'] = $e->getMessage();
              }
            }

            return $result;
          } catch (\Exception $e) {
            return ['error' => $e->getMessage(), 'class' => get_class($file)];
          }
        }, $imagesArray)
      ]);
    } else {
      Log::warning('Gallery upload - No images found', [
        'hasFile_check' => $request->hasFile('images'),
        'input_exists' => $request->has('images'),
        'all_files_has_images' => isset($allFiles['images']),
        'all_files_has_images_brackets' => isset($allFiles['images[]']),
      ]);
    }

    // Try multiple ways to access documents
    $documents = null;
    if ($request->hasFile('documents')) {
      $documents = $request->file('documents');
      Log::info('Gallery upload - Documents found via hasFile("documents")', ['count' => is_array($documents) ? count($documents) : 1]);
    } elseif (isset($allFiles['documents'])) {
      $documents = $allFiles['documents'];
      Log::info('Gallery upload - Documents found via allFiles()["documents"]', ['count' => is_array($documents) ? count($documents) : 1]);
    } elseif (isset($allFiles['documents[]'])) {
      $documents = $allFiles['documents[]'];
      Log::info('Gallery upload - Documents found via allFiles()["documents[]"]', ['count' => is_array($documents) ? count($documents) : 1]);
    }

    if ($documents) {
      $documentsArray = is_array($documents) ? $documents : [$documents];
      Log::info('Gallery upload - Documents details', [
        'count' => count($documentsArray),
        'files' => array_map(function ($file) {
          if (!$file instanceof \Illuminate\Http\UploadedFile) {
            return ['error' => 'Not an UploadedFile instance', 'type' => gettype($file)];
          }
          try {
            $isValid = $file->isValid();
            $result = [
              'is_valid' => $isValid,
              'error' => $file->getError(),
            ];

            if ($isValid) {
              try {
                $result['original_name'] = $file->getClientOriginalName();
                $result['mime_type'] = $file->getMimeType();
                $result['size'] = $file->getSize();
              } catch (\Exception $e) {
                $result['error_getting_properties'] = $e->getMessage();
              }
            } else {
              try {
                $result['error_message'] = $file->getErrorMessage();
              } catch (\Exception $e) {
                $result['error_getting_message'] = $e->getMessage();
              }
            }

            return $result;
          } catch (\Exception $e) {
            return ['error' => $e->getMessage(), 'class' => get_class($file)];
          }
        }, $documentsArray)
      ]);
    } else {
      Log::warning('Gallery upload - No documents in request', [
        'hasFile_check' => $request->hasFile('documents'),
        'input_exists' => $request->has('documents'),
      ]);
    }

    $thumbnail = $request->hasFile('thumbnail') ? $request->file('thumbnail') : null;

    try {
      // Validate product_ids first
      $validated = $request->validate([
        'product_ids' => 'required|array|min:1',
        'product_ids.*' => 'required|integer|exists:products,id',
      ], [
        'product_ids.required' => 'Please select at least one product.',
        'product_ids.array' => 'Invalid product selection.',
        'product_ids.min' => 'Please select at least one product.',
        'product_ids.*.exists' => 'One or more selected products are invalid.',
      ]);

      // Get images and documents - use the ones we found earlier or try to get them
      if (!$images && isset($allFiles['images'])) {
        $images = $allFiles['images'];
      } elseif (!$images && $request->hasFile('images')) {
        $images = $request->file('images');
      }

      if (!$documents && isset($allFiles['documents'])) {
        $documents = $allFiles['documents'];
      } elseif (!$documents && $request->hasFile('documents')) {
        $documents = $request->file('documents');
      }

      if (!$thumbnail && $request->hasFile('thumbnail')) {
        $thumbnail = $request->file('thumbnail');
      }

      // Filter out invalid files (empty paths, invalid uploads)
      if ($images) {
        $imagesArray = is_array($images) ? $images : [$images];
        $validImages = [];
        foreach ($imagesArray as $file) {
          if ($file instanceof \Illuminate\Http\UploadedFile) {
            try {
              if ($file->isValid() && $file->getPath() !== '' && $file->getPathname() !== '') {
                $validImages[] = $file;
              } else {
                Log::warning('Gallery upload - Invalid image file filtered out', [
                  'error' => $file->getError(),
                  'error_message' => $file->getErrorMessage(),
                  'path' => $file->getPath(),
                  'pathname' => $file->getPathname(),
                ]);
              }
            } catch (\Exception $e) {
              Log::warning('Gallery upload - Exception checking image file', ['error' => $e->getMessage()]);
            }
          }
        }
        $images = !empty($validImages) ? (count($validImages) === 1 ? $validImages[0] : $validImages) : null;
      }

      if ($documents) {
        $documentsArray = is_array($documents) ? $documents : [$documents];
        $validDocuments = [];
        foreach ($documentsArray as $file) {
          if ($file instanceof \Illuminate\Http\UploadedFile) {
            try {
              if ($file->isValid() && $file->getPath() !== '' && $file->getPathname() !== '') {
                $validDocuments[] = $file;
              } else {
                Log::warning('Gallery upload - Invalid document file filtered out', [
                  'error' => $file->getError(),
                  'error_message' => $file->getErrorMessage(),
                  'path' => $file->getPath(),
                  'pathname' => $file->getPathname(),
                ]);
              }
            } catch (\Exception $e) {
              Log::warning('Gallery upload - Exception checking document file', ['error' => $e->getMessage()]);
            }
          }
        }
        $documents = !empty($validDocuments) ? (count($validDocuments) === 1 ? $validDocuments[0] : $validDocuments) : null;
      }

      // Validate thumbnail if present (single image file)
      $hasThumbnail = false;
      if ($thumbnail && $thumbnail instanceof \Illuminate\Http\UploadedFile) {
        try {
          if ($thumbnail->isValid() && $thumbnail->getPath() !== '' && $thumbnail->getPathname() !== '') {
            if ($thumbnail->getMimeType() && str_starts_with($thumbnail->getMimeType(), 'image/') && $thumbnail->getSize() <= 10240 * 1024) {
              $hasThumbnail = true;
            }
          }
        } catch (\Exception $e) {
          // leave hasThumbnail false
        }
      }

      // Check if at least one valid file is provided
      $hasImages = $images !== null;
      $hasDocuments = $documents !== null;

      Log::info('Gallery upload - File validation check after filtering', [
        'has_images' => $hasImages,
        'has_documents' => $hasDocuments,
        'has_thumbnail' => $hasThumbnail,
        'will_fail' => !$hasImages && !$hasDocuments && !$hasThumbnail,
        'images_count' => $hasImages ? (is_array($images) ? count($images) : 1) : 0,
        'documents_count' => $hasDocuments ? (is_array($documents) ? count($documents) : 1) : 0,
      ]);

      if (!$hasImages && !$hasDocuments && !$hasThumbnail) {
        // Check if files were provided but filtered out due to errors
        $fileErrors = [];
        if (isset($allFiles['images'])) {
          $imagesArray = is_array($allFiles['images']) ? $allFiles['images'] : [$allFiles['images']];
          foreach ($imagesArray as $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile && !$file->isValid()) {
              $errorCode = $file->getError();
              $errorMessage = $file->getErrorMessage();
              if ($errorCode === UPLOAD_ERR_INI_SIZE || $errorCode === UPLOAD_ERR_FORM_SIZE) {
                $fileErrors[] = "File '{$file->getClientOriginalName()}' is too large. Maximum file size is 10MB.";
              } elseif ($errorCode === UPLOAD_ERR_PARTIAL) {
                $fileErrors[] = "File '{$file->getClientOriginalName()}' was only partially uploaded.";
              } elseif ($errorCode === UPLOAD_ERR_NO_FILE) {
                $fileErrors[] = "No file was uploaded for '{$file->getClientOriginalName()}'.";
              } else {
                $fileErrors[] = "File '{$file->getClientOriginalName()}' upload failed: {$errorMessage}";
              }
            }
          }
        }
        if (isset($allFiles['documents'])) {
          $documentsArray = is_array($allFiles['documents']) ? $allFiles['documents'] : [$allFiles['documents']];
          foreach ($documentsArray as $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile && !$file->isValid()) {
              $errorCode = $file->getError();
              $errorMessage = $file->getErrorMessage();
              if ($errorCode === UPLOAD_ERR_INI_SIZE || $errorCode === UPLOAD_ERR_FORM_SIZE) {
                $fileErrors[] = "File '{$file->getClientOriginalName()}' is too large. Maximum file size is 10MB.";
              } elseif ($errorCode === UPLOAD_ERR_PARTIAL) {
                $fileErrors[] = "File '{$file->getClientOriginalName()}' was only partially uploaded.";
              } elseif ($errorCode === UPLOAD_ERR_NO_FILE) {
                $fileErrors[] = "No file was uploaded for '{$file->getClientOriginalName()}'.";
              } else {
                $fileErrors[] = "File '{$file->getClientOriginalName()}' upload failed: {$errorMessage}";
              }
            }
          }
        }

        Log::error('Gallery upload - Validation failed: No valid files provided', [
          'request_all' => $request->all(),
          'request_files' => $allFiles,
          'request_input' => $request->input(),
          'file_errors' => $fileErrors,
        ]);

        $errorMessage = !empty($fileErrors)
          ? implode(' ', $fileErrors)
          : 'Please select at least one image, document, or thumbnail to upload.';

        return back()->withErrors(['files' => $errorMessage])->with('error', $errorMessage);
      }

      if ($hasThumbnail) {
        if (!$thumbnail->isValid()) {
          throw new \Exception("Thumbnail file is invalid: " . $thumbnail->getErrorMessage());
        }
        if (!$thumbnail->getMimeType() || !str_starts_with($thumbnail->getMimeType(), 'image/')) {
          throw new \Exception("Thumbnail must be an image file.");
        }
        if ($thumbnail->getSize() > 10240 * 1024) {
          throw new \Exception("Thumbnail exceeds 10MB limit.");
        }
      }

      // Validate files separately if they exist
      if ($images) {
        $imagesArray = is_array($images) ? $images : [$images];
        // Validate each image file
        foreach ($imagesArray as $index => $image) {
          if (!$image instanceof \Illuminate\Http\UploadedFile) {
            throw new \Exception("Invalid image file at index {$index}");
          }
          if (!$image->isValid()) {
            throw new \Exception("Image file '{$image->getClientOriginalName()}' is invalid: " . $image->getErrorMessage());
          }
          if (!$image->getMimeType() || !str_starts_with($image->getMimeType(), 'image/')) {
            throw new \Exception("File '{$image->getClientOriginalName()}' is not a valid image.");
          }
          if ($image->getSize() > 10240 * 1024) {
            throw new \Exception("Image file '{$image->getClientOriginalName()}' exceeds 10MB limit.");
          }
        }
      }

      if ($documents) {
        $documentsArray = is_array($documents) ? $documents : [$documents];
        // Validate each document file
        $allowedMimes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/plain'];
        foreach ($documentsArray as $index => $document) {
          if (!$document instanceof \Illuminate\Http\UploadedFile) {
            throw new \Exception("Invalid document file at index {$index}");
          }
          if (!$document->isValid()) {
            throw new \Exception("Document file '{$document->getClientOriginalName()}' is invalid: " . $document->getErrorMessage());
          }
          if (!in_array($document->getMimeType(), $allowedMimes)) {
            throw new \Exception("Document file '{$document->getClientOriginalName()}' is not an allowed type.");
          }
          if ($document->getSize() > 10240 * 1024) {
            throw new \Exception("Document file '{$document->getClientOriginalName()}' exceeds 10MB limit.");
          }
        }
      }
    } catch (\Illuminate\Validation\ValidationException $e) {
      Log::error('Gallery validation failed', ['errors' => $e->errors()]);
      return back()->withErrors($e->errors())->with('error', 'Validation failed. Please check your inputs.');
    } catch (\Exception $e) {
      Log::error('Gallery upload error', ['error' => $e->getMessage()]);
      return back()->with('error', 'An error occurred: ' . $e->getMessage());
    }

    $uploadedFiles = [];
    $errors = [];

    // Track counters per product and file type during this upload session
    $counters = [];

    // Handle images
    if ($images) {
      $imagesArray = is_array($images) ? $images : [$images];
      foreach ($imagesArray as $imageIndex => $image) {
        foreach ($validated['product_ids'] as $productId) {
          try {
            $product = Product::findOrFail($productId);
            $unitId = $product->unit_id;

            // Initialize counter for this product/image type if not exists
            $key = $productId . '_image';
            if (!isset($counters[$key])) {
              $existingCount = ProductGallery::where('product_id', $productId)
                ->where('file_type', 'image')
                ->count();
              $counters[$key] = $existingCount;
            }

            $counters[$key]++;
            $counter = $counters[$key];
            $extension = $image->getClientOriginalExtension();

            // Sanitize unitId and create safe filename
            $safeUnitId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $unitId);
            $fileName = $safeUnitId . '_' . $counter . '.' . $extension;

            // Ensure directory exists
            $directory = 'images/' . $safeUnitId;
            if (!Storage::disk('public')->exists($directory)) {
              Storage::disk('public')->makeDirectory($directory, 0755, true);
            }

            // Store file in public/images/{unitId}/
            $path = $image->storeAs($directory, $fileName, 'public');

            if (!$path) {
              throw new \Exception("Failed to store image file: {$fileName}");
            }

            // Save to database
            $gallery = ProductGallery::create([
              'product_id' => $productId,
              'file_url' => Storage::url($path),
              'file_type' => 'image',
              'file_name' => $fileName,
            ]);

            $uploadedFiles[] = [
              'product' => $product->unit_id,
              'file' => $fileName,
              'type' => 'image',
            ];
          } catch (\Exception $e) {
            $originalFileName = $image->getClientOriginalName();
            $errorMsg = "{$originalFileName}: " . $e->getMessage();
            $errors["images.{$imageIndex}"] = $errorMsg;
            Log::error('Image upload error', [
              'product_id' => $productId,
              'unit_id' => $unitId ?? 'N/A',
              'file_name' => $originalFileName,
              'error' => $e->getMessage(),
              'trace' => $e->getTraceAsString(),
            ]);
          }
        }
      }
    }

    // Handle documents
    if ($documents) {
      $documentsArray = is_array($documents) ? $documents : [$documents];
      foreach ($documentsArray as $documentIndex => $document) {
        foreach ($validated['product_ids'] as $productId) {
          try {
            $product = Product::findOrFail($productId);
            $unitId = $product->unit_id;

            // Initialize counter for this product/document type if not exists
            $key = $productId . '_document';
            if (!isset($counters[$key])) {
              $existingCount = ProductGallery::where('product_id', $productId)
                ->where('file_type', 'document')
                ->count();
              $counters[$key] = $existingCount;
            }

            $counters[$key]++;
            $counter = $counters[$key];
            $extension = $document->getClientOriginalExtension();

            // Sanitize unitId and create safe filename
            $safeUnitId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $unitId);
            $fileName = $safeUnitId . '_' . $counter . '.' . $extension;

            // Ensure directory exists
            $directory = 'documents/' . $safeUnitId;
            if (!Storage::disk('public')->exists($directory)) {
              Storage::disk('public')->makeDirectory($directory, 0755, true);
            }

            // Store file in public/documents/{unitId}/
            $path = $document->storeAs($directory, $fileName, 'public');

            if (!$path) {
              throw new \Exception("Failed to store document file: {$fileName}");
            }

            // Save to database
            $gallery = ProductGallery::create([
              'product_id' => $productId,
              'file_url' => Storage::url($path),
              'file_type' => 'document',
              'file_name' => $fileName,
            ]);

            $uploadedFiles[] = [
              'product' => $product->unit_id,
              'file' => $fileName,
              'type' => 'document',
            ];
          } catch (\Exception $e) {
            $originalFileName = $document->getClientOriginalName();
            $errorMsg = "{$originalFileName}: " . $e->getMessage();
            $errors["documents.{$documentIndex}"] = $errorMsg;
            Log::error('Document upload error', [
              'product_id' => $productId,
              'unit_id' => $unitId ?? 'N/A',
              'file_name' => $originalFileName,
              'error' => $e->getMessage(),
              'trace' => $e->getTraceAsString(),
            ]);
          }
        }
      }
    }

    // Handle thumbnail (one per product; replace existing thumbnail for each selected product)
    $firstStoredThumbnailPath = null;
    if ($hasThumbnail && $thumbnail) {
      foreach ($validated['product_ids'] as $index => $productId) {
        try {
          $product = Product::findOrFail($productId);
          $unitId = $product->unit_id;

          // Remove existing thumbnail for this product so only one remains
          ProductGallery::where('product_id', $productId)->where('file_type', 'thumbnail')->each(function ($g) {
            $filePath = str_replace('/storage/', '', $g->file_url);
            if (Storage::disk('public')->exists($filePath)) {
              Storage::disk('public')->delete($filePath);
            }
            $g->delete();
          });

          $extension = $thumbnail->getClientOriginalExtension();
          $safeUnitId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $unitId);
          $fileName = $safeUnitId . '_thumbnail.' . $extension;
          $directory = 'thumbnails/' . $safeUnitId;
          if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory, 0755, true);
          }

          if ($index === 0) {
            $path = $thumbnail->storeAs($directory, $fileName, 'public');
            if (!$path) {
              throw new \Exception("Failed to store thumbnail: {$fileName}");
            }
            $firstStoredThumbnailPath = $path;
          } else {
            $path = $directory . '/' . $fileName;
            Storage::disk('public')->copy($firstStoredThumbnailPath, $path);
          }

          ProductGallery::create([
            'product_id' => $productId,
            'file_url' => Storage::url($path),
            'file_type' => 'thumbnail',
            'file_name' => $fileName,
          ]);

          $uploadedFiles[] = [
            'product' => $product->unit_id,
            'file' => $fileName,
            'type' => 'thumbnail',
          ];
        } catch (\Exception $e) {
          $errors["thumbnail.{$productId}"] = $e->getMessage();
          Log::error('Thumbnail upload error', [
            'product_id' => $productId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
          ]);
        }
      }
    }

    if (count($errors) > 0) {
      return back()->withErrors($errors)->with('error', 'Some files failed to upload.');
    }

    $message = 'Successfully uploaded ' . count($uploadedFiles) . ' file(s).';
    return redirect()->route('admin.gallery.index')->with('success', $message);
  }

  public function destroy($id)
  {
    try {
      $gallery = ProductGallery::findOrFail($id);

      // Delete file from storage
      $filePath = str_replace('/storage/', '', $gallery->file_url);
      if (Storage::disk('public')->exists($filePath)) {
        Storage::disk('public')->delete($filePath);
      }

      // Delete database record
      $gallery->delete();

      return redirect()->route('admin.gallery.index')->with('success', 'File deleted successfully.');
    } catch (\Exception $e) {
      Log::error('Gallery delete error', [
        'id' => $id,
        'error' => $e->getMessage(),
      ]);
      return back()->with('error', 'Failed to delete file: ' . $e->getMessage());
    }
  }
}
