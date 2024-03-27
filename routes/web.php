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
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\KittingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
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
    Route::get('/app', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    //Utility Routes
    Route::get('app/vendor/autocomplete', [VendorController::class, 'autocomplete'])->name('vendors.autocomplete');
    Route::post('app/unit/store', [UomunitController::class, 'store'])->name('uom.store');
    Route::post('app/unit/search', [UomunitController::class, 'search'])->name('uom.search');
    Route::post('app/material-attachment/{attachment}/destroy/', [AttachmentsController::class, 'destroy'])->name('attachment.destroy');
    Route::post('app/material/{material}/export-bom/', [MaterialController::class, 'exportBomRecords'])->name('material.exportBOM');
    Route::post('app/material/{material}/import-bom/', [MaterialController::class, 'importBomRecords'])->name('material.importBOM');
    Route::post('app/material/get-all-materials', [MaterialController::class, 'getMaterials'])->name('materials.get');
    Route::post('app/material/get-details', [MaterialController::class, 'getMaterialDetails'])->name('material.getDetails');
    Route::post('app/material/get-finished-goods', [ProductionOrderController::class, 'getFinishedGoods'])->name('material.getFinishedGoods');

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
        Route::post('/fetch-raw-materials', [RawMaterialController::class, 'fetchRawMaterials'])->name('raw.fetchRawMaterials');
        Route::get('/add', [RawMaterialController::class, 'add'])->name('raw.add');
        Route::post('/store', [RawMaterialController::class, 'store'])->name('raw.store');
        Route::get('/bulk', [RawMaterialController::class, 'bulk'])->name('raw.bulk');
        Route::post('/bulk-store', [RawMaterialController::class, 'bulkStore'])->name('raw.bulkStore');
        Route::get('/{material}/show', [RawMaterialController::class, 'show'])->name('raw.show');
        Route::get('/{material}/edit', [RawMaterialController::class, 'edit'])->name('raw.edit');
        Route::post('/{material}/update', [RawMaterialController::class, 'update'])->name('raw.update');
        Route::delete('/{material}', [RawMaterialController::class, 'destroy'])->name('raw.destroy');
        Route::post('/price-list', [RawMaterialController::class, 'fetchPriceList'])->name('raw.fetchPriceList');
        Route::post('/material-list', [RawMaterialController::class, 'fetchMaterialList'])->name('raw.fetchMaterialList');
        Route::post('/purchase-list', [RawMaterialController::class, 'fetchPurchaseList'])->name('raw.fetchPurchaseList');
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
        Route::post('/bom-list', [BOMController::class, 'fetchBomList'])->name('bom.fetchBomList');
        Route::post('/bom-cost-list', [BOMController::class, 'getBomRecords'])->name('bom.getBomRecords');
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
        Route::get('/{warehouse}/viewTransaction', [WarehouseController::class, 'viewTransaction'])->name('wh.viewTransaction');
        Route::post('/{warehouse}/update', [WarehouseController::class, 'update'])->name('wh.update');
        Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('wh.destroy');
        Route::post('/get-all-materials', [WarehouseController::class, 'getMaterials'])->name('wh.getMaterials');
    });

    // Production Orders Routes
    Route::prefix('/app/production-orders')->group(function () {
        Route::get('', [ProductionOrderController::class, 'index'])->name('po');
        Route::post('/fetch-records', [ProductionOrderController::class, 'fetchProductionOrders'])->name('po.get');
        Route::get('/new', [ProductionOrderController::class, 'new'])->name('po.new');
        Route::get('/create', [ProductionOrderController::class, 'create'])->name('po.create');
        Route::post('/get-bom', [ProductionOrderController::class, 'getBomRecords'])->name('po.getBom');
        Route::post('/create', [ProductionOrderController::class, 'createOrder'])->name('po.createOrder');
        Route::post('/new-order', [ProductionOrderController::class, 'initOrder'])->name('po.initOrder');
        Route::delete('/remove-order', [ProductionOrderController::class, 'removeOrder'])->name('po.removeOrder');
        Route::get('/view-order', [ProductionOrderController::class, 'viewOrder'])->name('po.viewOrder');
    });

    // Production Orders Routes
    Route::prefix('/app/po-kitting')->group(function () {
        Route::get('', [KittingController::class, 'index'])->name('kitting');
        Route::get('/view-kitting-form', [KittingController::class, 'viewKittingForm'])->name('kitting.viewKittingForm');
        Route::get('/view-kitting-records', [KittingController::class, 'warehouseRecords'])->name('kitting.warehouseRecords');
        Route::post('/issue-kitting', [KittingController::class, 'issueOrder'])->name('kitting.issue');
        Route::post('/reverse-kitting', [KittingController::class, 'reverseItem'])->name('kitting.reverse');
    });

    Route::prefix('/app/vendors')->group(function () {
        Route::get('', [VendorController::class, 'index'])->name('vendor');
        Route::post('/get', [VendorController::class, 'get'])->name('vendor.get');
        Route::get('/{vendor}/edit', [VendorController::class, 'edit'])->name('vendor.edit');
        Route::post('/show', [VendorController::class, 'show'])->name('vendor.show');
        Route::post('/save', [VendorController::class, 'save'])->name('vendor.save');
        Route::post('/delete', [VendorController::class, 'delete'])->name('vendor.delete');
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

    Route::prefix('/app/users')->group(function () {
        Route::get('', [UserController::class, 'index'])->name('users');
        Route::get('/add', [UserController::class, 'create'])->name('users.add');
        Route::post('/store', [UserController::class, 'store'])->name('users.store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::post('/{user}/update', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('user.destroy');
    });

    Route::prefix('/app/reports')->group(function () {
        Route::get('', [ReportController::class, 'index'])->name('reports');
        Route::get('/rm-price-list', [RawMaterialController::class, 'priceList'])->name('reports.rmPriceList');
        Route::get('/material-list', [RawMaterialController::class, 'materialList'])->name('reports.materialList');
        Route::get('/bom-view', [BOMController::class, 'bomView'])->name('reports.bom');
        Route::get('/bom-cost-view', [BOMController::class, 'bomCostView'])->name('reports.bomCost');
        Route::get('/fg-bom-cost', [BOMController::class, 'fgBomCostSummary'])->name('reports.fgBomCostSummary');
        Route::get('/rm-stock', [RawMaterialController::class, 'stockReport'])->name('reports.stockReport');
        Route::get('/po-stock', [ProductionOrderController::class, 'poReport'])->name('reports.poReport');
        Route::get('/po-shortage-stock', [ProductionOrderController::class, 'poShortageReport'])->name('reports.poShortageReport');
        Route::get('/po-shortage-consolidated-stock', [ProductionOrderController::class, 'poConsolidatedShortageReport'])->name('reports.poConsolidatedShortageReport');
        Route::get('/plo-shortage-report', [ProductionOrderController::class, 'ploShortageReport'])->name('reports.ploShortageReport');
        Route::get('/plo-shortage-consolidated-report', [ProductionOrderController::class, 'ploConsolidatedShortageReport'])->name('reports.ploConsolidatedShortageReport');
        Route::get('/rm-purchase-report', [RawMaterialController::class, 'rmPurchaseReport'])->name('reports.rmPurchaseReport');
        Route::get('/rm-issuance-report', [RawMaterialController::class, 'rmIssuanceReport'])->name('reports.rmIssuanceReport');
    });

});
