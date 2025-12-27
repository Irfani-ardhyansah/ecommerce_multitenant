<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\AuthController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\CartController;

Route::middleware([
    'api',
    \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
])->prefix('api')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/products', [ProductController::class, 'index']); // Lihat katalog produk
    Route::get('/products/{id}', [ProductController::class, 'show']); // Lihat detail produk

    Route::middleware('auth:tenant', )->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);

        Route::middleware('role:user')->group(function () {
            Route::get('/cart', [CartController::class, 'index']);
            Route::post('/cart', [CartController::class, 'addToCart']);
            Route::post('/checkout', [CartController::class, 'checkout']);
        });

        Route::middleware('role:admin')->group(function () {
            Route::post('/products', [ProductController::class, 'store']);      // Tambah Produk
            Route::put('/products/{id}', [ProductController::class, 'update']); // Edit Produk
            Route::delete('/products/{id}', [ProductController::class, 'destroy']); // Hapus Produk
        });

    });
});