<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CatalogOrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicCatalogController;
use App\Http\Controllers\TenantSettingsController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\ReportController;

// ==========================================
// AUTHENTICATION & REGISTRATION ROUTES
// ==========================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/check-slug', [RegisterController::class, 'checkSlug']);

// ==========================================
// 1. PUBLIC ROUTES (Katalog Publik)
// ==========================================
// Rute ini dilindungi middleware untuk memastikan tenant/toko aktif
Route::middleware([\App\Http\Middleware\VerifyActiveTenant::class])->group(function () {
    Route::get('/public/catalog/{slug}', [PublicCatalogController::class, 'show']);
    Route::post('/public/catalog/{slug}/order', [CatalogOrderController::class, 'store'])->middleware('throttle:5,1');
    Route::post('/public/catalog/{slug}/calculate-shipping', [ShippingController::class, 'calculate'])->middleware('throttle:10,1');

    // Dapatkan info detail toko berdasarkan slug
    Route::get('/toko/{slug}', [CatalogController::class, 'getTenantInfo']);

    // Dapatkan daftar produk toko berdasarkan slug
    Route::get('/toko/{slug}/products', [CatalogController::class, 'getProducts']);
});

// ==========================================
// 2. PROTECTED ROUTES (Dashboard Tenant)
// ==========================================
// Rute ini membutuhkan login (Sanctum) dan verifikasi tenant aktif
Route::middleware(['auth:sanctum', \App\Http\Middleware\VerifyActiveTenant::class])->group(function () {
    // Statistik Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/notifications', [DashboardController::class, 'notifications']);

    // Analitik Katalog (dengan filter rentang tanggal)
    Route::get('/dashboard/catalog-analytics', [DashboardController::class, 'catalogAnalytics']);

    // CRUD Produk Dashboard (Otomatis difilter oleh trait BelongsToTenant)
    Route::apiResource('/products', ProductController::class);

    // Manajemen Pesanan (Orders)
    Route::apiResource('/orders', OrderController::class)->only(['index', 'show', 'update']);
    Route::post('/orders/{order}/payments', [PaymentController::class, 'store']);

    // Manajemen Piutang (Receivables)
    Route::get('/receivables', [\App\Http\Controllers\ReceivableController::class, 'index']);
    Route::get('/reports/sales', [ReportController::class, 'sales']);
    Route::get('/reports/daily-turnover', [ReportController::class, 'dailyTurnover']);

    // Pengaturan Toko (Tenant Settings)
    Route::get('/tenant/settings', [TenantSettingsController::class, 'show']);
    Route::put('/tenant/settings', [TenantSettingsController::class, 'update']);

    // QR Code Generator
    Route::get('/catalog/qr', [TenantSettingsController::class, 'getQrCode']);

    // File Upload (Logo & Banner)
    Route::post('/upload/logo', [UploadController::class, 'uploadLogo']);
    Route::post('/upload/banner', [UploadController::class, 'uploadBanner']);
    Route::post('/upload/qris', [UploadController::class, 'uploadQris']);
    Route::post('/upload/product-image', [UploadController::class, 'uploadProductImage']);
});
