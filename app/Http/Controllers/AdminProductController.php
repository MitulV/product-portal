<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;

class AdminProductController extends Controller
{
  public function import(Request $request)
  {
    try {
      // Validate that products data is provided
      $validated = $request->validate([
        'products' => 'required|array|min:1',
        'products.*.unit_id' => 'nullable|string|max:255',
        'products.*.serial_number' => 'nullable|string|max:255',
        'products.*.brand' => 'nullable|string|max:255',
        'products.*.model_number' => 'nullable|string|max:255',
        // Add other fields as needed
      ], [
        'products.required' => 'No products data received.',
        'products.array' => 'Products data must be an array.',
        'products.min' => 'At least one product is required.',
      ]);

      $productsData = $request->input('products');
      
      // Clear all existing products before importing new ones
      $deletedCount = Product::count();
      Product::truncate();
      \Log::info('Cleared all existing products', ['count' => $deletedCount]);
      
      $created = 0;
      $skipped = 0;
      $errors = [];

      foreach ($productsData as $index => $productData) {
        try {
          // Skip if unit_id is empty (unit_id is required and unique)
          if (empty($productData['unit_id']) || trim($productData['unit_id']) === '') {
            $skipped++;
            \Log::info('Skipping row without unit_id', ['index' => $index + 1]);
            continue;
          }

          // Clean and prepare data
          $productData = array_filter($productData, function($value) {
            return $value !== null && $value !== '';
          });

          // Ensure unit_id is still present after filtering
          if (!isset($productData['unit_id']) || trim($productData['unit_id']) === '') {
            $skipped++;
            continue;
          }

          // Transform date strings to proper format
          $dateFields = ['hold_expiration_date', 'est_completion_date', 'ship_date'];
          foreach ($dateFields as $field) {
            if (isset($productData[$field]) && $productData[$field]) {
              try {
                $productData[$field] = \Carbon\Carbon::parse($productData[$field])->format('Y-m-d');
              } catch (\Exception $e) {
                // If date parsing fails, set to null
                $productData[$field] = null;
              }
            }
          }

          // Transform numeric fields
          if (isset($productData['total_cost'])) {
            $productData['total_cost'] = is_numeric($productData['total_cost']) ? (float) $productData['total_cost'] : null;
          }
          if (isset($productData['tariff_cost'])) {
            $productData['tariff_cost'] = is_numeric($productData['tariff_cost']) ? (float) $productData['tariff_cost'] : null;
          }

          // Create new product
          Product::create($productData);
          $created++;
          \Log::info('Created new product', ['unit_id' => trim($productData['unit_id'])]);
        } catch (\Illuminate\Database\QueryException $e) {
          // Handle unique constraint violations (shouldn't happen after truncate, but just in case)
          if ($e->getCode() == 23000) {
            $errors[] = "Row " . ($index + 1) . ": Unit ID '{$productData['unit_id']}' already exists.";
          } else {
            $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
          }
          \Log::warning('Failed to create product', [
            'index' => $index + 1,
            'unit_id' => $productData['unit_id'] ?? 'N/A',
            'error' => $e->getMessage(),
          ]);
        } catch (\Exception $e) {
          $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
          \Log::warning('Failed to create product', [
            'index' => $index + 1,
            'unit_id' => $productData['unit_id'] ?? 'N/A',
            'error' => $e->getMessage(),
          ]);
        }
      }

      if ($created === 0) {
        return back()->with('error', 'No products were created. Please check your data.');
      }

      $message = "Successfully refreshed data source. ";
      $message .= "Imported {$created} product(s).";
      if ($skipped > 0) {
        $message .= " {$skipped} row(s) skipped (missing unit_id).";
      }
      if (count($errors) > 0) {
        $message .= " " . count($errors) . " error(s) occurred.";
        // Store detailed errors in session
        return redirect()->route('admin.products.index')
          ->with('success', $message)
          ->with('import_errors', $errors);
      }

      return redirect()->route('admin.products.index')->with('success', $message);
    } catch (\Illuminate\Validation\ValidationException $e) {
      // Return detailed validation errors
      $errorMessages = [];
      foreach ($e->errors() as $field => $messages) {
        $errorMessages = array_merge($errorMessages, $messages);
      }
      $errorMessage = 'Validation failed: ' . implode(' ', $errorMessages);
      \Log::warning('Validation failed', ['errors' => $e->errors()]);
      return back()->withErrors($e->errors())->with('error', $errorMessage);
    } catch (\Exception $e) {
      \Log::error('Product import error: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
      ]);

      $errorMessage = 'Error importing products: ' . $e->getMessage();
      return back()->with('error', $errorMessage);
    }
  }

  public function index(Request $request)
  {
    $query = Product::query();

    // Handle search
    if ($request->has('search') && $request->search) {
      $search = $request->search;
      $query->where(function($q) use ($search) {
        $q->where('unit_id', 'like', "%{$search}%")
          ->orWhere('hold_status', 'like', "%{$search}%")
          ->orWhere('hold_branch', 'like', "%{$search}%")
          ->orWhere('salesman', 'like', "%{$search}%");
      });
    }

    // Handle sorting
    $sortBy = $request->get('sort_by', 'id');
    $sortOrder = $request->get('sort_order', 'desc');
    
    // Validate sort column
    $allowedSortColumns = ['id', 'unit_id', 'hold_status', 'hold_branch', 'salesman', 'created_at'];
    if (!in_array($sortBy, $allowedSortColumns)) {
      $sortBy = 'id';
    }
    
    // Validate sort order
    if (!in_array($sortOrder, ['asc', 'desc'])) {
      $sortOrder = 'desc';
    }

    $query->orderBy($sortBy, $sortOrder);

    $products = $query->paginate(10)->appends($request->query());

    return Inertia::render('Admin/Products/Index', [
      'products' => $products,
      'filters' => [
        'search' => $request->get('search', ''),
        'sort_by' => $sortBy,
        'sort_order' => $sortOrder,
      ]
    ]);
  }


  public function edit(Product $product)
  {
    return Inertia::render('Admin/Products/Edit', [
      'product' => $product
    ]);
  }

  public function update(Request $request, Product $product)
  {
    $validated = $request->validate([
      'unit_id' => 'required|string|max:255',
      'brand' => 'nullable|string|max:255',
      // ... strict validation recommended for prod
    ]);

    $product->update($request->all());

    return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
  }

  public function destroy(Product $product)
  {
    $product->delete();
    return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
  }
}
