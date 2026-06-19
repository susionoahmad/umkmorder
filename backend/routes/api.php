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
    // Laporan & Ekspor Data
    Route::get('/reports/sales', [ReportController::class, 'sales']);
    Route::get('/reports/daily-turnover', [ReportController::class, 'dailyTurnover']);
    Route::get('/reports/export', [ReportController::class, 'export']);

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

// ==========================================
// Rute untuk Super Admin (SaaS Panel)
// ==========================================
Route::middleware(['auth:sanctum', 'super_admin'])->prefix('admin')->group(function () {
    // Dashboard Stats & Activities
    Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'stats']);
    Route::get('/dashboard/activities', [\App\Http\Controllers\Admin\DashboardController::class, 'activities']);

    // Tenant Management
    Route::get('/tenants', [\App\Http\Controllers\Admin\TenantController::class, 'index']);
    Route::get('/tenants/{tenant}', [\App\Http\Controllers\Admin\TenantController::class, 'show']);
    Route::put('/tenants/{tenant}', [\App\Http\Controllers\Admin\TenantController::class, 'update']);
    Route::post('/tenants/{tenant}/suspend', [\App\Http\Controllers\Admin\TenantController::class, 'suspend']);
    Route::post('/tenants/{tenant}/activate', [\App\Http\Controllers\Admin\TenantController::class, 'activate']);
    Route::post('/tenants/{tenant}/reset-password', [\App\Http\Controllers\Admin\TenantController::class, 'resetPassword']);
    Route::post('/tenants/{tenant}/impersonate', [\App\Http\Controllers\Admin\TenantController::class, 'impersonate']);

    // Subscriptions Management
    Route::get('/subscriptions', [\App\Http\Controllers\Admin\SubscriptionController::class, 'index']);
    Route::post('/subscriptions', [\App\Http\Controllers\Admin\SubscriptionController::class, 'store']);
    Route::put('/subscriptions/{subscription}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'update']);

    // Plans Management
    Route::apiResource('/plans', \App\Http\Controllers\Admin\PlanController::class);

    // Billing Invoices Management
    Route::get('/billing-invoices', [\App\Http\Controllers\Admin\BillingInvoiceController::class, 'index']);
    Route::post('/billing-invoices', [\App\Http\Controllers\Admin\BillingInvoiceController::class, 'store']);
    Route::put('/billing-invoices/{invoice}', [\App\Http\Controllers\Admin\BillingInvoiceController::class, 'update']);

    // Support Directory & Internal Notes
    Route::get('/support/tenants', [\App\Http\Controllers\Admin\SupportController::class, 'tenants']);
    Route::put('/support/tenants/{tenant}/notes', [\App\Http\Controllers\Admin\SupportController::class, 'updateNotes']);

    // Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'show']);
    Route::put('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update']);
});
