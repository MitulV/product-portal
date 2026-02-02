<?php

use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [\App\Http\Controllers\ProductController::class, 'index'])->name('home');
Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
// Inquiry before show so /products/14/inquiry is not captured as slug
Route::get('/products/{product}/inquiry', [\App\Http\Controllers\ProductController::class, 'inquiry'])->name('products.inquiry');
// Pretty URL: /products/14 or /products/14/generator-mtu-100kw-480v-lvl3
Route::get('/products/{product}/{slug?}', [\App\Http\Controllers\ProductController::class, 'show'])->name('products.show');
Route::post('/products/{product}/inquiry', [\App\Http\Controllers\ProductController::class, 'submitInquiry'])->name('products.inquiry.submit');

Route::redirect('/admin', '/admin/dashboard');

Route::middleware('guest')->group(function () {
  Route::get('/admin/login', [\App\Http\Controllers\AdminAuthController::class, 'create'])->name('login');
  Route::post('/admin/login', [\App\Http\Controllers\AdminAuthController::class, 'store']);
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

  Route::post('/logout', [\App\Http\Controllers\AdminAuthController::class, 'destroy'])->name('logout');

  Route::get('/dashboard', function () {
    $totalProducts = \App\Models\Product::count();
    return view('admin.dashboard', [
      'totalProducts' => $totalProducts
    ]);
  })->name('dashboard');

  Route::post('/products/import', [\App\Http\Controllers\AdminProductController::class, 'import'])->name('products.import');
  Route::get('/products/download-template', [\App\Http\Controllers\AdminProductController::class, 'downloadTemplate'])->name('products.download-template');
  Route::resource('products', \App\Http\Controllers\AdminProductController::class)->except(['create', 'store']);

  Route::get('/gallery', [\App\Http\Controllers\AdminGalleryController::class, 'index'])->name('gallery.index');
  Route::post('/gallery', [\App\Http\Controllers\AdminGalleryController::class, 'store'])->name('gallery.store');
  Route::delete('/gallery/{id}', [\App\Http\Controllers\AdminGalleryController::class, 'destroy'])->name('gallery.destroy');
});
