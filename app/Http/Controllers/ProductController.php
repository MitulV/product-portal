<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = Product::query();

        // Only show available products (exclude products on hold)
        $baseQuery->where(function($q) {
            $q->where('hold_status', '!=', 'Hold')
              ->orWhereNull('hold_status');
        });

        $query = clone $baseQuery;

        // Handle voltage filter
        if ($request->has('voltage') && $request->voltage) {
            $query->where('voltage', $request->voltage);
        }

        // Handle search (voltage is handled separately via filter)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('unit_id', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model_number', 'like', "%{$search}%");
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

        // Get unique voltage values for filter dropdown
        $availableVoltages = Product::query()
            ->where(function($q) {
                $q->where('hold_status', '!=', 'Hold')
                  ->orWhereNull('hold_status');
            })
            ->whereNotNull('voltage')
            ->where('voltage', '!=', '')
            ->select('voltage')
            ->distinct()
            ->orderBy('voltage')
            ->pluck('voltage')
            ->filter()
            ->values()
            ->toArray();

        return view('products.index', [
            'products' => $products,
            'availableVoltages' => $availableVoltages,
            'filters' => [
                'search' => $request->get('search', ''),
                'voltage' => $request->get('voltage', ''),
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ]
        ]);
    }

    public function show(Product $product)
    {
        $product->load('galleries');
        
        return view('products.show', [
            'product' => $product
        ]);
    }

    public function inquiry(Product $product)
    {
        return view('products.inquiry', [
            'product' => $product
        ]);
    }

    public function submitInquiry(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        try {
            // Send email notification
            $toEmail = env('INQUIRY_EMAIL', config('mail.from.address'));
            
            Mail::send([], [], function ($message) use ($validated, $product, $toEmail) {
                $message->to($toEmail)
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
                        <div class="value">' . htmlspecialchars($data['phone'] ?? 'Not provided') . '</div>
                    </div>
                    <div class="field">
                        <div class="label">Company:</div>
                        <div class="value">' . htmlspecialchars($data['company'] ?? 'Not provided') . '</div>
                    </div>
                    <div class="field">
                        <div class="label">Message:</div>
                        <div class="value">' . nl2br(htmlspecialchars($data['message'] ?? 'No message provided')) . '</div>
                    </div>
                    
                    <div class="product-info">
                        <h3>Product Details</h3>
                        <div class="field">
                            <div class="label">Unit ID:</div>
                            <div class="value">' . htmlspecialchars($product->unit_id ?? 'N/A') . '</div>
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

