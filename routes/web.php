<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BranchMenuController;
use App\Http\Controllers\Admin\BulkPriceController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CmsSettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExcelController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\FranchiseController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\QrMenuController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SEO & System Routes
|--------------------------------------------------------------------------
*/
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');

/*
|--------------------------------------------------------------------------
| Public Corporate Platform Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('public.home');
Route::get('/hakkimizda', [PublicController::class, 'about'])->name('public.about');
Route::get('/urunlerimiz', [PublicController::class, 'products'])->name('public.products');
Route::get('/subelerimiz', [PublicController::class, 'branches'])->name('public.branches');
Route::get('/subelerimiz/{branch:slug}', [PublicController::class, 'branchShow'])->name('public.branch.show');
Route::get('/franchise', [PublicController::class, 'franchise'])->name('public.franchise');
Route::post('/franchise', [PublicController::class, 'storeFranchise'])->name('public.franchise.store');
Route::get('/basinda-biz', [PublicController::class, 'press'])->name('public.press');
Route::get('/iletisim', [PublicController::class, 'contact'])->name('public.contact');

/*
|--------------------------------------------------------------------------
| QR Menu & Customer Feedback Routes
|--------------------------------------------------------------------------
*/
Route::get('/qrmenu/{branch:slug}', [QrMenuController::class, 'show'])->name('qrmenu.show');
Route::get('/qrmenu/{branch:slug}/m/{token}', [QrMenuController::class, 'showTable'])->name('qrmenu.table');
Route::post('/qrmenu/{branch:slug}/geri-bildirim', [QrMenuController::class, 'storeFeedback'])->name('qrmenu.feedback.store');

/*
|--------------------------------------------------------------------------
| Unified Management Portal Authentication (/yonetim)
|--------------------------------------------------------------------------
*/
Route::prefix('yonetim')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/cikis', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    Route::get('/sifremi-unuttum', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/sifremi-unuttum', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/sifre-sifirla/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/sifre-sifirla', [AuthController::class, 'resetPassword'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Protected Headquarters & Branch Management Routes (/yonetim/...)
|--------------------------------------------------------------------------
*/
Route::prefix('yonetim')
    ->name('admin.')
    ->middleware(['auth', 'active_user'])
    ->group(function () {
        Route::get('/panel', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profil', [AuthController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profil', [AuthController::class, 'updateProfile'])->name('profile.update');

        // Central Branch Management (HQ / Super Admin)
        Route::middleware('permission:branches.manage')->group(function () {
            Route::get('/subeler', [BranchController::class, 'index'])->name('branches.index');
            Route::get('/subeler/yeni', [BranchController::class, 'create'])->name('branches.create');
            Route::post('/subeler', [BranchController::class, 'store'])->name('branches.store');
            Route::get('/subeler/{branch:slug}/duzenle', [BranchController::class, 'edit'])->name('branches.edit');
            Route::put('/subeler/{branch:slug}', [BranchController::class, 'update'])->name('branches.update');
        });

        // Central Category Management
        Route::middleware('permission:categories.manage')->group(function () {
            Route::get('/kategoriler', [CategoryController::class, 'index'])->name('categories.index');
            Route::post('/kategoriler', [CategoryController::class, 'store'])->name('categories.store');
            Route::post('/kategoriler/sirala', [CategoryController::class, 'reorder'])->name('categories.reorder');
            Route::put('/kategoriler/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('/kategoriler/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        });

        // Central Product Pool Management (HQ Only)
        Route::middleware('permission:products.manage')->group(function () {
            Route::get('/urunler', [ProductController::class, 'index'])->name('products.index');
            Route::get('/urunler/yeni', [ProductController::class, 'create'])->name('products.create');
            Route::post('/urunler', [ProductController::class, 'store'])->name('products.store');
            Route::get('/urunler/{product}/duzenle', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('/urunler/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::delete('/urunler/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        });

        // Central Bulk Price Management
        Route::middleware('permission:prices.bulk_update')->group(function () {
            Route::get('/fiyat-yonetimi', [BulkPriceController::class, 'index'])->name('pricing.index');
            Route::post('/fiyat-yonetimi/onizleme', [BulkPriceController::class, 'preview'])->name('pricing.preview');
            Route::post('/fiyat-yonetimi/uygula', [BulkPriceController::class, 'apply'])->name('pricing.apply');
        });

        // Excel Import / Export Center
        Route::middleware('permission:excel.import,excel.export')->group(function () {
            Route::get('/excel', [ExcelController::class, 'index'])->name('excel.index');
            Route::get('/excel/sablon-indir', [ExcelController::class, 'downloadTemplate'])
                ->middleware('permission:excel.import')
                ->name('excel.template');
            Route::post('/excel/onizleme', [ExcelController::class, 'previewImport'])
                ->middleware('permission:excel.import')
                ->name('excel.preview');
            Route::post('/excel/uygula', [ExcelController::class, 'commitImport'])
                ->middleware('permission:excel.import')
                ->name('excel.commit');
            Route::get('/excel/urun-havuzu-indir', [ExcelController::class, 'exportProducts'])
                ->middleware('permission:excel.export')
                ->name('excel.export.products');
            Route::get('/excel/sube-fiyatlari-indir', [ExcelController::class, 'exportBranchPrices'])
                ->middleware('permission:excel.export')
                ->name('excel.export.branch_prices');
        });

        // Branch-Scoped Operations (Enforces both permission AND branch_scope isolation)
        Route::prefix('/sube/{branch:slug}')
            ->middleware('branch_scope')
            ->group(function () {
                // Branch Menu Selection, Sold-Out & Branch Pricing/Discount
                Route::middleware('permission:branch_menu.manage,prices.branch_update')->group(function () {
                    Route::get('/menu', [BranchMenuController::class, 'index'])->name('branch.menu');
                    Route::put('/menu/{product}', [BranchMenuController::class, 'updateItem'])->name('branch.menu.update');
                });

                // Branch Tables & Table Card Generator (SVG, PNG, PDF)
                Route::middleware('permission:tables.manage')->group(function () {
                    Route::get('/masalar', [TableController::class, 'index'])->name('branch.tables');
                    Route::post('/masalar', [TableController::class, 'store'])->name('branch.tables.store');
                    Route::put('/masalar/{table}', [TableController::class, 'update'])->name('branch.tables.update');
                    Route::delete('/masalar/{table}', [TableController::class, 'destroy'])->name('branch.tables.destroy');
                    Route::get('/masalar/toplu-pdf', [TableController::class, 'downloadBatchPdf'])->name('branch.tables.batch_pdf');
                    Route::get('/masalar/{table}/kart/{format}', [TableController::class, 'downloadCard'])->name('branch.tables.card');
                });

                // Branch Working Hours, Special Days & Contact Info
                Route::middleware('permission:branch_settings.manage,branches.manage')->group(function () {
                    Route::get('/ayarlar', [BranchController::class, 'editOwnBranch'])->name('branch.settings');
                    Route::put('/ayarlar', [BranchController::class, 'updateOwnBranch'])->name('branch.settings.update');
                    Route::post('/ayarlar/ozel-gun', [BranchController::class, 'storeSpecialDay'])->name('branch.special_days.store');
                    Route::delete('/ayarlar/ozel-gun/{specialDay}', [BranchController::class, 'destroySpecialDay'])->name('branch.special_days.destroy');
                });
            });

        // Customer Feedbacks (Branch-scoped inside controller)
        Route::middleware('permission:feedbacks.view')->group(function () {
            Route::get('/geri-bildirimler', [FeedbackController::class, 'index'])->name('feedbacks.index');
            Route::get('/geri-bildirimler/excel', [FeedbackController::class, 'export'])->name('feedbacks.export');
            Route::put('/geri-bildirimler/{feedback}', [FeedbackController::class, 'update'])
                ->middleware('permission:feedbacks.manage')
                ->name('feedbacks.update');
        });

        // Franchise Applications (Sales / Franchise Unit & Super Admin)
        Route::middleware('permission:franchises.view')->group(function () {
            Route::get('/franchise-basvurulari', [FranchiseController::class, 'index'])->name('franchises.index');
            Route::get('/franchise-basvurulari/excel', [FranchiseController::class, 'export'])->name('franchises.export');
            Route::put('/franchise-basvurulari/{application}', [FranchiseController::class, 'update'])
                ->middleware('permission:franchises.manage')
                ->name('franchises.update');
        });

        // Site, Theme, CMS & SEO Management (Super Admin)
        Route::middleware('permission:settings.manage')->group(function () {
            Route::get('/icerik-yonetimi', [CmsSettingController::class, 'index'])->name('settings.index');
            Route::put('/icerik-yonetimi/genel', [CmsSettingController::class, 'updateSettings'])->name('settings.update');
            Route::put('/icerik-yonetimi/seo/{seoPage}', [CmsSettingController::class, 'updateSeo'])->name('settings.seo.update');
            Route::post('/icerik-yonetimi/basin', [CmsSettingController::class, 'storePressItem'])->name('settings.press.store');
            Route::delete('/icerik-yonetimi/basin/{pressItem}', [CmsSettingController::class, 'destroyPressItem'])->name('settings.press.destroy');
        });

        // User & Multi-Role / Branch Assignment Management (Super Admin)
        Route::middleware('permission:users.manage')->group(function () {
            Route::get('/kullanicilar', [UserRoleController::class, 'usersIndex'])->name('users.index');
            Route::post('/kullanicilar', [UserRoleController::class, 'storeUser'])->name('users.store');
            Route::put('/kullanicilar/{user}', [UserRoleController::class, 'updateUser'])->name('users.update');
        });

        Route::middleware('permission:roles.manage')->group(function () {
            Route::get('/roller', [UserRoleController::class, 'rolesIndex'])->name('roles.index');
            Route::post('/roller', [UserRoleController::class, 'storeRole'])->name('roles.store');
            Route::put('/roller/{role}', [UserRoleController::class, 'updateRole'])->name('roles.update');
        });

        // Immutable Audit Logs (Super Admin Only)
        Route::middleware('permission:audit_logs.view')->group(function () {
            Route::get('/denetim-kayitlari', [AuditLogController::class, 'index'])->name('audit_logs.index');
            Route::get('/denetim-kayitlari/excel', [AuditLogController::class, 'export'])->name('audit_logs.export');
        });
    });
