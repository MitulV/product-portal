<?php

use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [\App\Http\Controllers\ProductController::class, 'index'])->name('home');
Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [\App\Http\Controllers\ProductController::class, 'show'])->name('products.show');
Route::post('/products/{product}/inquiry', [\App\Http\Controllers\ProductController::class, 'submitInquiry'])->name('products.inquiry');

Route::redirect('/admin', '/admin/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [\App\Http\Controllers\AdminAuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [\App\Http\Controllers\AdminAuthController::class, 'store']);
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::post('/logout', [\App\Http\Controllers\AdminAuthController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function () {
        $totalProducts = \App\Models\Product::count();
        return inertia('Admin/Dashboard', [
            'totalProducts' => $totalProducts
        ]);
    })->name('dashboard');

    Route::post('/products/import', [\App\Http\Controllers\AdminProductController::class, 'import'])->name('products.import');
    Route::resource('products', \App\Http\Controllers\AdminProductController::class)->except(['create', 'store']);
});
