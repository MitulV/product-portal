<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;

class AdminProductController extends Controller
{
  public function import(Request $request)
  {
    try {
      // Handle JSON string input from frontend first
      $productsInput = $request->input('products');
      if (is_string($productsInput)) {
        $productsData = json_decode($productsInput, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
          return back()->with('error', 'Invalid products data format.');
        }
        // Replace the input with decoded array for validation
        $request->merge(['products' => $productsData]);
      } else {
        $productsData = $productsInput;
      }

      // Validate that products data is provided (after decoding if needed)
      // Note: We validate all fields so Laravel doesn't strip them from $validated
      $validated = $request->validate([
        'products' => 'required|array|min:1',
        'products.*.product_type' => 'nullable|in:Generators,Switch,Docking Stations,Other',
        'products.*.unit_id' => 'nullable|string|max:255',
        'products.*.serial_number' => 'nullable|string|max:255',
        'products.*.brand' => 'nullable|string|max:255',
        'products.*.model_number' => 'nullable|string|max:255',
        'products.*.hold_status' => 'nullable|string|max:255',
        'products.*.hold_branch' => 'nullable|string|max:255',
        'products.*.salesman' => 'nullable|string|max:255',
        'products.*.opportunity_name' => 'nullable|string|max:255',
        'products.*.location' => 'nullable|string|max:255',
        'products.*.hold_expiration_date' => 'nullable|date',
        'products.*.date_hold_added' => 'nullable|date',
        'products.*.est_completion_date' => 'nullable|date',
        'products.*.total_cost' => 'nullable|numeric',
        'products.*.tariff_cost' => 'nullable|numeric',
        'products.*.retail_cost' => 'nullable|numeric',
        'products.*.sales_order_number' => 'nullable|string|max:255',
        'products.*.ipas_cpq_number' => 'nullable|string|max:255',
        'products.*.cps_po_number' => 'nullable|string|max:255',
        'products.*.ship_date' => 'nullable|date',
        'products.*.voltage' => 'nullable|string|max:255',
        'products.*.phase' => 'nullable|string|max:255',
        'products.*.enclosure' => 'nullable|string|max:255',
        'products.*.enclosure_type' => 'nullable|string|max:255',
        'products.*.tank' => 'nullable|string|max:255',
        'products.*.controller_series' => 'nullable|string|max:255',
        'products.*.breakers' => 'nullable|string|max:255',
        'products.*.notes' => 'nullable|string',
        'products.*.description' => 'nullable|string',
        'products.*.tech_spec' => 'nullable|string',
        // Generators specific
        'products.*.application_group' => 'nullable|string|max:255',
        'products.*.engine_model' => 'nullable|string|max:255',
        'products.*.unit_specification' => 'nullable|string|max:255',
        'products.*.ibc_certification' => 'nullable|string|max:255',
        'products.*.exhaust_emissions' => 'nullable|string|max:255',
        'products.*.temp_rise' => 'nullable|string|max:3',
        'products.*.fuel_type' => 'nullable|string|max:6',
        'products.*.power' => 'nullable|integer',
        'products.*.engine_speed' => 'nullable|integer',
        'products.*.radiator_design_temp' => 'nullable|integer',
        'products.*.frequency' => 'nullable|integer',
        'products.*.full_load_amps' => 'nullable|integer',
        // Switch specific
        'products.*.transition_type' => 'nullable|string|max:8',
        'products.*.bypass_isolation' => 'nullable|string|max:24',
        'products.*.service_entrance_rated' => 'nullable|string|max:255',
        'products.*.contactor_type' => 'nullable|string|max:255',
        'products.*.controller_model' => 'nullable|string|max:255',
        'products.*.communications_type' => 'nullable|string|max:255',
        'products.*.accessories' => 'nullable|string|max:255',
        'products.*.catalog_number' => 'nullable|string|max:255',
        'products.*.quote_number' => 'nullable|string|max:255',
        'products.*.number_of_poles' => 'nullable|string|max:12',
        'products.*.amperage' => 'nullable|integer',
        // Docking Stations specific
        'products.*.circuit_breaker_type' => 'nullable|string|max:255',
      ], [
        'products.required' => 'No products data received.',
        'products.array' => 'Products data must be an array.',
        'products.min' => 'At least one product is required.',
      ]);

      $productsData = $validated['products'];

      // Log first product's raw input for debugging
      if (!empty($productsData)) {
        Log::info('Import: First product raw input data', [
          'product_index' => 0,
          'raw_data' => $productsData[0],
          'total_products' => count($productsData)
        ]);
      }

      // Clear all existing products before importing new ones
      Product::truncate();

      $created = 0;
      $skipped = 0;
      $errors = [];

      foreach ($productsData as $index => $productData) {
        try {
          // Skip if unit_id is empty (unit_id is required and unique)
          if (empty($productData['unit_id']) || trim($productData['unit_id']) === '') {
            $skipped++;
            continue;
          }

          // Don't filter out fields - keep all fields even if null/empty
          // The database columns are nullable, so we can pass null values
          // Only ensure we have unit_id

          // Ensure unit_id is still present after filtering
          if (!isset($productData['unit_id']) || trim($productData['unit_id']) === '') {
            $skipped++;
            continue;
          }

          // Define all fillable fields upfront
          $fillableFields = [
            'product_type',
            'hold_status',
            'hold_branch',
            'salesman',
            'opportunity_name',
            'location',
            'hold_expiration_date',
            'date_hold_added',
            'brand',
            'model_number',
            'est_completion_date',
            'total_cost',
            'tariff_cost',
            'retail_cost',
            'sales_order_number',
            'ipas_cpq_number',
            'cps_po_number',
            'ship_date',
            'voltage',
            'phase',
            'enclosure',
            'enclosure_type',
            'tank',
            'controller_series',
            'breakers',
            'serial_number',
            'unit_id',
            'notes',
            'description',
            'tech_spec',
            // Generators specific
            'application_group',
            'engine_model',
            'unit_specification',
            'ibc_certification',
            'exhaust_emissions',
            'temp_rise',
            'fuel_type',
            'power',
            'engine_speed',
            'radiator_design_temp',
            'frequency',
            'full_load_amps',
            // Switch specific
            'transition_type',
            'bypass_isolation',
            'service_entrance_rated',
            'contactor_type',
            'controller_model',
            'communications_type',
            'accessories',
            'catalog_number',
            'quote_number',
            'number_of_poles',
            'amperage',
            // Docking Stations specific
            'circuit_breaker_type',
          ];

          // Build filtered data array with all fillable fields
          $filteredData = [];
          foreach ($fillableFields as $field) {
            // Get the value from productData, defaulting to null if not set
            $value = $productData[$field] ?? null;

            // Process date fields
            if (in_array($field, ['hold_expiration_date', 'date_hold_added', 'est_completion_date', 'ship_date'])) {
              if ($value !== null && $value !== '') {
                try {
                  $value = \Carbon\Carbon::parse($value)->format('Y-m-d');
                } catch (\Exception $e) {
                  // If date parsing fails, set to null
                  $value = null;
                }
              } else {
                $value = null;
              }
            }
            // Process numeric/decimal fields
            elseif (in_array($field, ['total_cost', 'tariff_cost', 'retail_cost'])) {
              if ($value === '' || $value === null) {
                $value = null;
              } elseif (is_numeric($value)) {
                $value = (float) $value;
              } else {
                $value = null;
              }
            }
            // Process integer fields
            elseif (in_array($field, ['power', 'engine_speed', 'radiator_design_temp', 'frequency', 'full_load_amps', 'amperage', 'hold_branch', 'sales_order_number', 'voltage', 'phase'])) {
              if ($value === '' || $value === null) {
                $value = null;
              } elseif (is_numeric($value)) {
                $value = (int) $value;
              } else {
                $value = null;
              }
            }
            // Process string fields - convert empty strings to null
            else {
              if ($value === '') {
                $value = null;
              }
            }

            // Always set the field, even if null
            $filteredData[$field] = $value;
          }

          // Log filtered data for first product to debug
          if ($index === 0) {
            Log::info('Import: First product filtered data before create', [
              'product_index' => 0,
              'unit_id' => $filteredData['unit_id'] ?? 'MISSING',
              'filtered_data' => $filteredData,
              'fields_count' => count($filteredData),
              'null_fields' => array_keys(array_filter($filteredData, fn($v) => $v === null))
            ]);
          }

          // Create new product with filtered data
          $product = Product::create($filteredData);

          // Log what was actually saved for first product
          if ($index === 0) {
            Log::info('Import: First product after database save', [
              'product_id' => $product->id,
              'unit_id' => $product->unit_id,
              'saved_data' => $product->toArray(),
              'fields_count' => count($product->toArray())
            ]);
          }

          $created++;
        } catch (\Illuminate\Database\QueryException $e) {
          // Handle unique constraint violations (shouldn't happen after truncate, but just in case)
          Log::error('Import: Database query exception', [
            'row_index' => $index + 1,
            'unit_id' => $productData['unit_id'] ?? 'MISSING',
            'error_code' => $e->getCode(),
            'error_message' => $e->getMessage(),
            'sql_state' => $e->errorInfo[0] ?? null,
            'driver_code' => $e->errorInfo[1] ?? null
          ]);
          if ($e->getCode() == 23000) {
            $errors[] = "Row " . ($index + 1) . ": Unit ID '{$productData['unit_id']}' already exists.";
          } else {
            $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
          }
        } catch (\Exception $e) {
          Log::error('Import: General exception', [
            'row_index' => $index + 1,
            'unit_id' => $productData['unit_id'] ?? 'MISSING',
            'error_message' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString()
          ]);
          $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
        }
      }

      // Log import summary
      Log::info('Import: Summary', [
        'total_products_in_input' => count($productsData),
        'created' => $created,
        'skipped' => $skipped,
        'errors_count' => count($errors)
      ]);

      if ($created === 0) {
        Log::warning('Import: No products were created');
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
      return back()->withErrors($e->errors())->with('error', $errorMessage);
    } catch (\Exception $e) {
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
      $query->where(function ($q) use ($search) {
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
    $allowedSortColumns = ['id', 'product_type', 'unit_id', 'hold_status', 'hold_branch', 'salesman', 'created_at'];
    if (!in_array($sortBy, $allowedSortColumns)) {
      $sortBy = 'id';
    }

    // Validate sort order
    if (!in_array($sortOrder, ['asc', 'desc'])) {
      $sortOrder = 'desc';
    }

    $query->orderBy($sortBy, $sortOrder);

    $products = $query->paginate(10)->appends($request->query());

    return view('admin.products.index', [
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
    return view('admin.products.edit', [
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

  public function downloadTemplate()
  {
    // Serve the static template file from public directory
    $filePath = public_path('product-import-template.xlsx');

    if (!file_exists($filePath)) {
      return back()->with('error', 'Template file not found. Please contact administrator.');
    }

    return response()->download($filePath, 'product-import-template.xlsx');
  }
}
