<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Material;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $rawMaterialCount = Material::where('type', 'raw')->count();
        $semiMaterialCount = Material::where('type', 'semi-finished')->count();
        $finishedMaterialCount = Material::where('type', 'finished')->count();

        $lowRawMaterialCount = Material::where('type', 'raw')
        ->whereHas('stock', function ($query) {
            $query->where('re_order', '<', \DB::raw('stocks.closing_balance'));
        })
        ->count();

        $lowSemiMaterialCount = Material::where('type', 'semi-finished')
        ->whereHas('stock', function ($query) {
            $query->where('re_order', '<', \DB::raw('stocks.closing_balance'));
        })
        ->count();

        $lowFinishedMaterialCount = Material::where('type', 'finished')
        ->whereHas('stock', function ($query) {
            $query->where('re_order', '<', \DB::raw('stocks.closing_balance'));
        })
        ->count();

        // Count raw materials completely out of stock
        $zeroStockRawMaterialCount = Material::where('type', 'raw')
        ->whereHas('stock', function ($query) {
            $query->whereRaw('closing_balance = 0');
        })
        ->count();

        // Count semi-finished materials completely out of stock
        $zeroStockSemiMaterialCount = Material::where('type', 'semi-finished')
        ->whereHas('stock', function ($query) {
            $query->whereRaw('closing_balance = 0');
        })
        ->count();

        // Count finished materials completely out of stock
        $zeroStockFinishedMaterialCount = Material::where('type', 'finished')
        ->whereHas('stock', function ($query) {
            $query->whereRaw('closing_balance = 0');
        })
        ->count();


        $notifications = Notification::orderBy('created_at', 'ASC')->take(500)->get();

        return view('home', compact('notifications', 'rawMaterialCount', 'semiMaterialCount', 'finishedMaterialCount', 'lowRawMaterialCount', 'lowSemiMaterialCount', 'lowFinishedMaterialCount', 'zeroStockRawMaterialCount', 'zeroStockSemiMaterialCount', 'zeroStockFinishedMaterialCount'));
    }
}
