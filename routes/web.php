<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommodityController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\SemiFinishedMaterialController;
use App\Http\Controllers\FinishedMaterialController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\AdminRoleController;
use App\Http\Controllers\AdminPermissionController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\UomunitController;
use App\Http\Controllers\BOMController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\AttachmentsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public Routes
Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Auth::routes();

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    //Utility Routes
    Route::get('app/vendor/autocomplete', [VendorController::class, 'autocomplete'])->name('vendors.autocomplete');
    Route::post('app/unit/store', [UomunitController::class, 'store'])->name('uom.store');
    Route::post('app/unit/search', [UomunitController::class, 'search'])->name('uom.search');
    Route::post('app/material-attachment/{attachment}/destroy/', [AttachmentsController::class, 'destroy'])->name('attachment.destroy');
    Route::post('app/material/{material}/export-bom/', [MaterialController::class, 'exportBomRecords'])->name('material.exportBOM');
    Route::post('app/material/{material}/import-bom/', [MaterialController::class, 'importBomRecords'])->name('material.importBOM');
    Route::post('app/material/get-all-materials', [MaterialController::class, 'getMaterials'])->name('materials.get');
    Route::post('app/material/get-details', [MaterialController::class, 'getMaterialDetails'])->name('material.getDetails');

    // Commodity Routes
    Route::prefix('/app/commodities')->group(function () {
        Route::get('', [CommodityController::class, 'index'])->name('commodities');
        Route::get('/add', [CommodityController::class, 'add'])->name('commodities.add');
        Route::post('/store', [CommodityController::class, 'store'])->name('commodities.store');
        Route::post('/save', [CommodityController::class, 'save'])->name('commodities.save');
        Route::get('/bulk', [CommodityController::class, 'bulk'])->name('commodities.bulk');
        Route::post('/bulk-store', [CommodityController::class, 'bulkStore'])->name('commodities.bulkStore');
        Route::get('/{commodity}/edit', [CommodityController::class, 'edit'])->name('commodities.edit');
        Route::post('/{commodity}/update', [CommodityController::class, 'update'])->name('commodities.update');
        Route::delete('/{commodity}', [CommodityController::class, 'destroy'])->name('commodities.destroy');
        Route::post('/search', [CommodityController::class, 'search'])->name('commodities.search');
    });

    // Category Routes
    Route::prefix('/app/categories')->group(function () {
        Route::get('', [CategoryController::class, 'index'])->name('categories');
        Route::get('/add', [CategoryController::class, 'add'])->name('categories.add');
        Route::post('/store', [CategoryController::class, 'store'])->name('categories.store');
        Route::post('/save', [CategoryController::class, 'save'])->name('categories.save');
        Route::get('/bulk', [CategoryController::class, 'bulk'])->name('categories.bulk');
        Route::post('/bulk-store', [CategoryController::class, 'bulkStore'])->name('categories.bulkStore');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::post('/{category}/update', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('/search', [CategoryController::class, 'search'])->name('categories.search');
    });

    // Raw Material Routes
    Route::prefix('/app/raw-materials')->group(function () {
        Route::get('', [RawMaterialController::class, 'index'])->name('raw');
        Route::get('/add', [RawMaterialController::class, 'add'])->name('raw.add');
        Route::post('/store', [RawMaterialController::class, 'store'])->name('raw.store');
        Route::get('/bulk', [RawMaterialController::class, 'bulk'])->name('raw.bulk');
        Route::post('/bulk-store', [RawMaterialController::class, 'bulkStore'])->name('raw.bulkStore');
        Route::get('/{material}/show', [RawMaterialController::class, 'show'])->name('raw.show');
        Route::get('/{material}/edit', [RawMaterialController::class, 'edit'])->name('raw.edit');
        Route::post('/{material}/update', [RawMaterialController::class, 'update'])->name('raw.update');
        Route::delete('/{material}', [RawMaterialController::class, 'destroy'])->name('raw.destroy');
    });

    // Semi Finished Material Routes
    Route::prefix('/app/semi-finished-materials')->group(function () {
        Route::get('', [SemiFinishedMaterialController::class, 'index'])->name('semi');
        Route::post('/get-raw-materials', [SemiFinishedMaterialController::class, 'getRawMaterials'])->name('semi.getRawMaterials');
        Route::get('/{material}/show', [SemiFinishedMaterialController::class, 'show'])->name('semi.show');
        Route::get('/add', [SemiFinishedMaterialController::class, 'create'])->name('semi.add');
        Route::post('/store', [SemiFinishedMaterialController::class, 'store'])->name('semi.store');
        Route::get('/{material}/edit', [SemiFinishedMaterialController::class, 'edit'])->name('semi.edit');
        Route::post('/{material}/update', [SemiFinishedMaterialController::class, 'update'])->name('semi.update');
        Route::delete('/{material}', [SemiFinishedMaterialController::class, 'destroy'])->name('semi.destroy');
    });

    // Finished Material Routes
    Route::prefix('/app/finished-materials')->group(function () {
        Route::get('', [FinishedMaterialController::class, 'index'])->name('finished');
        Route::post('/get-raw-materials', [FinishedMaterialController::class, 'getMaterials'])->name('finished.getMaterials');
        Route::get('/{material}/show', [FinishedMaterialController::class, 'show'])->name('finished.show');
        Route::get('/add', [FinishedMaterialController::class, 'create'])->name('finished.add');
        Route::post('/store', [FinishedMaterialController::class, 'store'])->name('finished.store');
        Route::get('/{material}/edit', [FinishedMaterialController::class, 'edit'])->name('finished.edit');
        Route::post('/{material}/update', [FinishedMaterialController::class, 'update'])->name('finished.update');
        Route::delete('/{material}', [FinishedMaterialController::class, 'destroy'])->name('finished.destroy');
        Route::post('/check-partcode', [FinishedMaterialController::class, 'checkPartcode'])->name('finished.checkPartcode');
        Route::get('/suggest-partcode', [FinishedMaterialController::class, 'suggestPartcode'])->name('finished.suggest.partcode');
    });

    // Bill of materials Routes
    Route::prefix('/app/bill-of-materials')->group(function () {
        Route::get('', [BOMController::class, 'index'])->name('bom');
        Route::post('/get-bom', [BOMController::class, 'getBom'])->name('bom.getBom');
        Route::get('/{bom}/show', [BOMController::class, 'show'])->name('bom.show');
        Route::get('/{bom}/edit', [BOMController::class, 'edit'])->name('bom.edit');
        Route::post('/{bom}/update', [BOMController::class, 'update'])->name('bom.update');
        Route::delete('/{bom}', [BOMController::class, 'destroy'])->name('bom.destroy');
    });

    // Warehouse Routes
    Route::prefix('/app/warehouse')->group(function () {
        Route::get('', [WarehouseController::class, 'index'])->name('wh');
        Route::get('/transactions', [WarehouseController::class, 'transactions'])->name('wh.transactions');
        Route::post('/get-records', [WarehouseController::class, 'fetchRecords'])->name('wh.getWarehouseRecords');
        Route::post('/get-transactions', [WarehouseController::class, 'fetchTransactions'])->name('wh.getWarehouseTransactions');
        Route::get('/issue', [WarehouseController::class, 'transIssue'])->name('wh.transIssue');
        Route::get('/receive', [WarehouseController::class, 'transReceive'])->name('wh.transReceive');
        Route::post('/issue', [WarehouseController::class, 'issueMultiple'])->name('wh.issue');
        Route::post('/receive', [WarehouseController::class, 'receiveMultiple'])->name('wh.receive');
        Route::get('/{warehouse}/editReceipt', [WarehouseController::class, 'editReceipt'])->name('wh.editReceipt');
        Route::get('/{warehouse}/editIssue', [WarehouseController::class, 'editIssue'])->name('wh.editIssue');
        Route::post('/{warehouse}/update', [WarehouseController::class, 'update'])->name('wh.update');
        Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('wh.destroy');
        Route::post('/get-all-materials', [WarehouseController::class, 'getMaterials'])->name('wh.getMaterials');
    });

    Route::prefix('/app/roles')->group(function () {
        Route::get('', [AdminRoleController::class, 'index'])->name('roles');
        Route::get('/add', [AdminRoleController::class, 'create'])->name('roles.add');
        Route::post('/store', [AdminRoleController::class, 'store'])->name('roles.store');
        Route::get('/bulk', [AdminRoleController::class, 'bulk'])->name('roles.bulk');
        Route::post('/bulk-store', [AdminRoleController::class, 'bulkStore'])->name('roles.bulkStore');
        Route::get('/{role}/edit', [AdminRoleController::class, 'edit'])->name('roles.edit');
        Route::post('/{role}/update', [AdminRoleController::class, 'update'])->name('roles.update');
        Route::delete('/{role}', [AdminRoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::prefix('/app/permissions')->group(function () {
        Route::get('', [AdminPermissionController::class, 'index'])->name('permissions');
        Route::get('/add', [AdminPermissionController::class, 'create'])->name('permissions.add');
        Route::post('/store', [AdminPermissionController::class, 'store'])->name('permissions.store');
        Route::get('/{permission}/edit', [AdminPermissionController::class, 'edit'])->name('permissions.edit');
        Route::post('/{permission}/update', [AdminPermissionController::class, 'update'])->name('permissions.update');
        Route::delete('/{permission}', [AdminPermissionController::class, 'destroy'])->name('permissions.destroy');
    });
});
