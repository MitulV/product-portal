<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProductController extends Controller
{
  public function index(Request $request)
  {
    $baseQuery = Product::query();

    // Only show available products (exclude "Hold", "On hold", and "Sold" statuses)
    $baseQuery->where(function ($q) {
      $q->whereNull('hold_status')
        ->orWhere('hold_status', '')
        ->orWhereRaw('LOWER(TRIM(hold_status)) NOT IN (?, ?, ?)', ['hold', 'on hold', 'sold']);
    });

    $query = clone $baseQuery;

    // Filters: narrow results after search (e.g. 500kw → filter by Brand, Voltage, Phase)
    if ($request->filled('voltage')) {
      $query->where('voltage', $request->voltage);
    }
    if ($request->filled('brand')) {
      $query->where('brand', $request->brand);
    }
    if ($request->filled('phase')) {
      $query->where('phase', $request->phase);
    }

    // Search: title, voltage, tank, brand, enclosure, phase, model, unit_id; kW only when query contains "kw"
    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('unit_id', 'like', "%{$search}%")
          ->orWhere('brand', 'like', "%{$search}%")
          ->orWhere('model_number', 'like', "%{$search}%")
          ->orWhere('title', 'like', "%{$search}%")
          ->orWhere('voltage', 'like', "%{$search}%")
          ->orWhere('tank', 'like', "%{$search}%")
          ->orWhere('enclosure', 'like', "%{$search}%")
          ->orWhere('phase', 'like', "%{$search}%");
        // Only match kW when user includes "kw" in query (e.g. 100kw, 100 kW, 100KW)
        if (preg_match('/kw/i', $search) && preg_match('/(\d+(?:\.\d+)?)/', $search, $m)) {
          $kwNum = (int) round((float) $m[1], 0);
          $q->orWhereRaw('ROUND(kw, 0) = ?', [$kwNum]);
        }
      });
    }

    // Handle sorting
    $sortBy = $request->get('sort_by', 'id');
    $sortOrder = $request->get('sort_order', 'desc');

    $allowedSortColumns = ['id', 'unit_id', 'brand', 'model_number', 'created_at'];
    if (!in_array($sortBy, $allowedSortColumns)) {
      $sortBy = 'id';
    }

    if (!in_array($sortOrder, ['asc', 'desc'])) {
      $sortOrder = 'desc';
    }

    $query->orderBy($sortBy, $sortOrder);

    $products = $query->with('thumbnail')->paginate(12)->appends($request->query());

    // Filter dropdown options (from available products only)
    $baseForFilters = Product::query()->where(function ($q) {
      $q->whereNull('hold_status')
        ->orWhere('hold_status', '')
        ->orWhereRaw('LOWER(TRIM(hold_status)) NOT IN (?, ?, ?)', ['hold', 'on hold', 'sold']);
    });
    $availableVoltages = (clone $baseForFilters)->whereNotNull('voltage')->where('voltage', '!=', '')
      ->select('voltage')->distinct()->orderBy('voltage')->pluck('voltage')->filter()->values()->toArray();
    $availableBrands = (clone $baseForFilters)->whereNotNull('brand')->where('brand', '!=', '')
      ->select('brand')->distinct()->orderBy('brand')->pluck('brand')->filter()->values()->toArray();
    $availablePhases = (clone $baseForFilters)->whereNotNull('phase')->where('phase', '!=', '')
      ->select('phase')->distinct()->orderBy('phase')->pluck('phase')->filter()->values()->toArray();

    return view('products.index', [
      'products' => $products,
      'availableVoltages' => $availableVoltages,
      'availableBrands' => $availableBrands,
      'availablePhases' => $availablePhases,
      'filters' => [
        'search' => $request->get('search', ''),
        'voltage' => $request->get('voltage', ''),
        'brand' => $request->get('brand', ''),
        'phase' => $request->get('phase', ''),
        'sort_by' => $sortBy,
        'sort_order' => $sortOrder,
      ]
    ]);
  }

  public function show(Product $product)
  {
    // Hide on-hold and sold units from client: treat as not found
    $holdStatus = $product->hold_status ? strtolower(trim($product->hold_status)) : '';
    if (in_array($holdStatus, ['hold', 'on hold', 'sold'], true)) {
      abort(404);
    }

    // Redirect to canonical pretty URL when visiting /products/18 (no slug) and product has slug data
    $params = $product->showRouteParameters();
    if (isset($params['slug']) && request()->path() === 'products/' . $product->id) {
      return redirect()->route('products.show', $params, 301);
    }

    $product->load('galleries');

    return view('products.show', [
      'product' => $product
    ]);
  }

  public function inquiry(Product $product)
  {
    $holdStatus = $product->hold_status ? strtolower(trim($product->hold_status)) : '';
    if (in_array($holdStatus, ['hold', 'on hold', 'sold'], true)) {
      abort(404);
    }
    return view('products.inquiry', [
      'product' => $product
    ]);
  }

  public function submitInquiry(Request $request, Product $product)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|max:255',
      'phone' => 'required|string|max:255',
      'company' => 'required|string|max:255',
      'message' => 'nullable|string|max:2000',
    ], [
      'name.required' => 'Name is required.',
      'email.required' => 'Email is required.',
      'phone.required' => 'Phone is required.',
      'company.required' => 'Company is required.',
    ]);

    try {
      $toEmails = ['nlandon@curtisps.com', 'jfalcone@curtisps.com'];

      Mail::send([], [], function ($message) use ($validated, $product, $toEmails) {
        $message->to($toEmails)
          ->subject('New Product Inquiry - ' . ($product->unit_id ?? 'Product #' . $product->id))
          ->html($this->buildEmailTemplate($validated, $product));
      });

      return redirect()->route('products.inquiry', $product)->with('success', 'Thank you for your inquiry! Our team will reach out to you shortly.');
    } catch (\Exception $e) {
      \Log::error('Failed to send inquiry email: ' . $e->getMessage());
      return redirect()->route('products.inquiry', $product)->with('error', 'There was an error submitting your inquiry. Please try again or contact us directly.');
    }
  }

  private function buildEmailTemplate($data, $product)
  {
    $html = '<!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #1e40af; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9fafb; padding: 20px; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #1e40af; }
                .value { margin-top: 5px; }
                .product-info { background-color: white; padding: 15px; border-left: 4px solid #1e40af; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>New Product Inquiry</h2>
                </div>
                <div class="content">
                    <h3>Customer Information</h3>
                    <div class="field">
                        <div class="label">Name:</div>
                        <div class="value">' . htmlspecialchars($data['name']) . '</div>
                    </div>
                    <div class="field">
                        <div class="label">Email:</div>
                        <div class="value">' . htmlspecialchars($data['email']) . '</div>
                    </div>
                    <div class="field">
                        <div class="label">Phone:</div>
                        <div class="value">' . htmlspecialchars($data['phone'] ?? '') . '</div>
                    </div>
                    <div class="field">
                        <div class="label">Company:</div>
                        <div class="value">' . htmlspecialchars($data['company'] ?? '') . '</div>
                    </div>
                    <div class="field">
                        <div class="label">Message:</div>
                        <div class="value">' . nl2br(htmlspecialchars($data['message'] ?? 'No message provided')) . '</div>
                    </div>
                    
                    <div class="product-info">
                        <h3>Product / Stock Details</h3>
                        <div class="field">
                            <div class="label">Product:</div>
                            <div class="value">' . htmlspecialchars($product->card_title ?? 'N/A') . '</div>
                        </div>
                        <div class="field">
                            <div class="label">Unit ID (Stock ID):</div>
                            <div class="value">' . htmlspecialchars($product->unit_id ?? 'N/A') . '</div>
                        </div>
                        <div class="field">
                            <div class="label">Product Type:</div>
                            <div class="value">' . htmlspecialchars($product->product_type ?? 'N/A') . '</div>
                        </div>
                        <div class="field">
                            <div class="label">Brand:</div>
                            <div class="value">' . htmlspecialchars($product->brand ?? 'N/A') . '</div>
                        </div>
                        <div class="field">
                            <div class="label">Model Number:</div>
                            <div class="value">' . htmlspecialchars($product->model_number ?? 'N/A') . '</div>
                        </div>
                        <div class="field">
                            <div class="label">Serial Number:</div>
                            <div class="value">' . htmlspecialchars($product->serial_number ?? 'N/A') . '</div>
                        </div>
                        <div class="field">
                            <div class="label">Voltage:</div>
                            <div class="value">' . htmlspecialchars($product->voltage ?? 'N/A') . '</div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>';

    return $html;
  }
}
