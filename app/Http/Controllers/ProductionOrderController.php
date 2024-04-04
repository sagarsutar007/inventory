<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


use App\Models\Material;
use App\Models\BomRecord;
use App\Models\Stock;
use App\Models\ProductionOrder;
use App\Models\ProdOrdersMaterial;

class ProductionOrderController extends Controller
{
    public function index()
    {
        return view('production-order.list');
    }

    public function create()
    {
        return view('production-order.create');
    }

    public function new()
    {
        return view('production-order.new');
    }

    public function viewOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'po_id' => 'required|exists:production_orders,po_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $po_id = $request->input('po_id');
        $productionOrder = ProductionOrder::with('material.uom')->findOrFail($po_id);
        $partCodes = [$productionOrder->material->part_code];
        $quantities = [$productionOrder->quantity];

        $bomRecords = $this->fetchBomRecords($partCodes, $quantities, $po_id);

        $context = [
            'bomRecords' => $bomRecords,
        ];

        $returnHTML = view('production-order.viewBomTable', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML, 'info' => $productionOrder));
    }

    public function getBomRecords(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'part_code' => 'required|array',
            'quantity' => 'required|array',
            'part_code.*' => 'required|string',
            'quantity.*' => 'required|numeric',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $partCodes = $request->input('part_code', []);
        $quantities = $request->input('quantity', []);

        $bomRecords = $this->fetchBomRecords($partCodes, $quantities);

        $context = [
            'bomRecords' => $bomRecords,
        ];

        if ($request->input('report')) {
            $returnHTML = view('reports.viewBomTable', $context)->render();
        } else {
            $returnHTML = view('production-order.viewBomTable', $context)->render();
        }

        return response()->json(array('status' => true, 'html' => $returnHTML));
    }

    public function fetchBomRecords($partCodes = [], $quantities = [], $po_id = '')
    {
        $bomRecords = [];

        foreach ($partCodes as $key => $partCode) {
            $material = Material::with('category', 'commodity')->where('part_code', $partCode)->first();
            if ($material && $material->bom) {
                $records = BomRecord::where('bom_id', $material->bom->bom_id)->get();
                foreach ($records as $record) {
                    $prodOrderMaterial = ProdOrdersMaterial::where('po_id', $po_id)->where('material_id', $record->material->material_id)->first();
                    $closingBalance = Stock::where('material_id', $record->material->material_id)->value('closing_balance');
                    $quantity = $record->quantity * $quantities[$key];
                    if (isset ($bomRecords[$record->material->description])) {
                        $bomRecords[$record->material->description]['quantity'] += $quantity;
                    } else {
                        $bomRecords[$record->material->description] = [
                            'material_id' => $record->material->material_id,
                            'part_code' => $record->material->part_code,
                            'material_description' => $record->material->description,
                            'category' => $record->material->category->category_name,
                            'commodity' => $record->material->commodity->commodity_name,
                            'quantity' => $quantity,
                            'bom_qty' => $record->quantity,
                            'issued' => $prodOrderMaterial ? $prodOrderMaterial->quantity : 0,
                            'balance' => $prodOrderMaterial ? $quantity - $prodOrderMaterial->quantity : $quantity,
                            'uom_shortcode' => $record->material->uom->uom_shortcode,
                            'closing_balance' => $closingBalance,
                        ];
                    }
                }
            }
        }
        $bomRecords = array_values($bomRecords);
        return $bomRecords;
    }

    public function fetchProductionOrders(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $query = ProductionOrder::with('material.uom');

        if (!empty ($search)) {
            $query->whereHas('material', function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%');
            });
        }

        $totalRecords = $query->count();

        if ($columnName === 'description') {
            $query->orderBy('description', $columnSortOrder);
        } elseif ($columnName === 'quantity') {
            $query->orderBy('quantity', $columnSortOrder);
        } else {

        }

        // Paginate the query
        $poQuery = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
        $productionOrders = $poQuery->items();
        $data = [];
        foreach ($productionOrders as $index => $order) {
            $material = $order->material;
            if ($material) {
                $data[] = [
                    'po_id' => $order->po_id,
                    'po_number' => $order->po_number,
                    'fg_partcode' => $order->material->part_code,
                    'part_code' => $material->part_code,
                    'description' => $material->description,
                    'unit' => $material->uom->uom_shortcode,
                    'quantity' => $order->quantity,
                    'created_at' => date('d-m-Y h:i a', strtotime('+5 hours 30 minutes', strtotime($order->created_at))),
                    'created_by' => $order->user->name,
                    'status' => $order->status,
                ];
            }
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $poQuery->total(),
            "data" => $data,
        ];

        return response()->json($response);
    }

    public function initOrder(Request $request)
    {

        $messages = [
            'part_code.*.required' => 'The part code field is required.',
            'quantity.*.required' => 'The quantity field is required.',
        ];

        // Validate input data
        $validator = Validator::make($request->all(), [
            'part_code' => 'required|array',
            'quantity' => 'required|array',
            'part_code.*' => 'required|string',
            'quantity.*' => 'required|numeric',
        ], $messages);

        // If validation fails, redirect back with error message
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $poNumber = $this->generatePoNumber();
            for ($i = 0; $i < count($request->part_code); $i++) {
                $material = Material::where('part_code', $request->part_code[$i])->first();
                ProductionOrder::create([
                    'po_number' => $poNumber,
                    'material_id' => $material->material_id,
                    'quantity' => $request->quantity[$i],
                    'record_date' => $this->getISTDate(),
                    'status' => 'Pending',
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Production order created successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to create production order: ' . $e->getMessage());
        }
    }

    public function generatePoNumber()
    {
        $year = Carbon::now()->format('y');

        $weekNumber = Carbon::now()->weekOfYear;
        $day = Carbon::now()->format('d');
        $poPrefix = 'PO' . $year . $weekNumber . $day;
        $lastPoNumber = ProductionOrder::where('po_number', 'like', '%' . $year . '%')->max('po_number');
        $increment = 1;
        if ($lastPoNumber) {
            $lastNumericPart = (int) substr($lastPoNumber, -5);
            $increment = $lastNumericPart + 1;
        }
        $incrementFormatted = str_pad($increment, 5, '0', STR_PAD_LEFT);
        $poNumber = $poPrefix . $incrementFormatted;
        return $poNumber;
    }

    public function createOrder(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'part_code' => 'required|array',
            'quantity' => 'required|array',
            'part_code.*' => 'required|string',
            'quantity.*' => 'required|numeric',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        DB::beginTransaction();

        try {
            $poNumber = $this->generatePoNumber();

            for ($i = 0; $i < count($request->part_code); $i++) {

                $material = Material::where('part_code', $request->part_code[$i])->first();

                $productionOrder = ProductionOrder::create([
                    'po_number' => $poNumber,
                    'material_id' => $material->material_id,
                    'quantity' => $request->quantity[$i],
                    'status' => 'Pending',
                    'record_date' => $this->getISTDate(),
                    'created_by' => Auth::id(),
                ]);

                // $bomRecords = $this->fetchBomRecords([$request->part_code[$i]], [$request->quantity[$i]]);

                // foreach ($bomRecords as $bomRecord) {
                //     ProdOrdersMaterial::create([
                //         'po_id' => $productionOrder->po_id,
                //         'material_id' => $bomRecord['material_id'],
                //         'quantity' => $bomRecord['quantity'],
                //         'created_by' => Auth::id(),
                //     ]);
                // }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Production order created successfully',
                'production_order_id' => $productionOrder->po_id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to create production order: ' . $e->getMessage(),
            ]);
        }
    }

    public function getFinishedGoods(Request $request)
    {
        $term = $request->input('term');

        $existingPartCodes = $request->input('existingPartCodes', []);

        $existingPartCodes = array_filter($existingPartCodes, function ($value) {
            return $value !== null;
        });

        $materials = Material::with('uom')
            ->whereNotIn('part_code', $existingPartCodes)
            ->where(function ($query) use ($term) {
                $query->where('part_code', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%');
            })
            ->whereIn('type', ['semi-finished', 'finished'])
            ->orderBy('description')
            ->limit(20)
            ->get();

        $formattedMaterials = $materials->map(function ($material) {
            return [
                'value' => $material->part_code,
                'unit' => $material->uom->uom_shortcode,
                'desc' => $material->description,
            ];
        });

        return response()->json($formattedMaterials);
    }

    public function removeOrder(Request $request)
    {
        try {
            $request->validate([
                'po_id' => 'required|exists:production_orders,po_id',
            ]);
            $po_id = $request->input('po_id');
            $orderMaterialsCount = ProdOrdersMaterial::where('po_id', $po_id)->count();
            if ($orderMaterialsCount === 0) {
                $productionOrder = ProductionOrder::find($po_id);
                if ($productionOrder) {
                    $productionOrder->delete();
                    return response()->json(['success' => true, 'message' => 'Production order deleted successfully']);
                } else {
                    return response()->json(['success' => false, 'message' => 'Production order not found'], 404);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Cannot delete production order. Records found in kitting.'], 400);
            }
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your request.'], 500);
        }
    }
    
    public function getISTDate()
    {
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Kolkata');
        $dateIST = date('Y-m-d');
        date_default_timezone_set($defaultTimeZone);

        return $dateIST;
    }

    public function poReport()
    {
        return view('reports.po-report');
    }

    public function fetchPoReport(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $searchTerm = $request->input('searchTerm');
        $status = $request->input('status');

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $query = ProductionOrder::with('material.uom');

        if(!empty($searchTerm)){
            $query->whereHas('material', function ($q) use ($searchTerm) {
                $q->where('description', 'like', '%' . $searchTerm . '%');
            });
        }

        if(!empty($status)){
            $query->where('status', 'like', $status);
        }
                    

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('record_date', [$startDate, $endDate]);
        }

        if (!empty ($search)) {
            $query->whereHas('material', function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%');
            });
        }

        $totalRecords = $query->count();

        if ($columnName === 'description') {
            $query->orderBy('description', $columnSortOrder);
        } elseif ($columnName === 'quantity') {
            $query->orderBy('quantity', $columnSortOrder);
        } elseif ($columnName === 'po_number') {
            $query->orderBy('po_number', $columnSortOrder);
        } elseif ($columnName === 'po_date') {
            $query->orderBy('record_date', $columnSortOrder);
        } elseif ($columnName === 'part_code') {
            $query->join('materials', 'production_orders.material_id', '=', 'materials.material_id')
                  ->orderBy('materials.part_code', $columnSortOrder);
        } elseif ($columnName === 'unit') {
            $query->join('materials', 'production_orders.material_id', '=', 'materials.material_id')
                  ->join('uoms', 'materials.uom_id', '=', 'uoms.uom_id')
                  ->orderBy('uoms.uom_shortcode', $columnSortOrder);
        } elseif ($columnName === 'status') {
            $query->orderBy('status', $columnSortOrder);
        }

        // Paginate the query
        $poQuery = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
        $productionOrders = $poQuery->items();
        $data = [];
        foreach ($productionOrders as $index => $order) {
            $material = $order->material;
            if ($material) {

                $currentPage = ($start / $length) + 1;
                $serial = ($currentPage - 1) * $length + $index + 1;
                $data[] = [
                    'serial' => $serial,
                    'po_id' => $order->po_id,
                    'po_number' => $order->po_number,
                    'po_date' => $order->record_date,
                    'description' => $material->description,
                    'part_code' => $material->part_code,
                    'unit' => $material->uom->uom_shortcode,
                    'quantity' => $order->quantity,
                    'status' => $order->status,
                ];
            }
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $poQuery->total(),
            "data" => $data,
        ];

        return response()->json($response);
    }
    
    public function poShortageReport()
    {
        return view('reports.po-shortage-report');
    }

    public function fetchPoShortageReport(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        // $searchTerm = $request->input('searchTerm');

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $query = ProductionOrder::with('material','prod_order_materials')->where('production_orders.status', 'Partially Issued');

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('record_date', [$startDate, $endDate]);
        }

        if (!empty($search)) {
            $query->whereHas('material', function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                    ->orWhere('part_code', 'like', '%' . $search . '%')
                    ->orWhere('mpn', 'like', '%' . $search . '%')
                    ->orWhere('make', 'like', '%' . $search . '%')
                    ->orWhereHas('uom', function ($u) use ($search) {
                        $u->where('uom_text', 'like', '%' . $search . '%')
                            ->orWhere('uom_shortcode', 'like', '%' . $search . '%');
                    });
            })
            ->orWhere('po_number', 'like', '%' . $search . '%')
            ->orWhere('quantity', 'like', '%' . $search . '%')
            ->orWhere('record_date', 'like', '%' . $search . '%');
        }

        $totalRecords = $query->count();

        if ($columnName === 'po_number') {
            $query->orderBy('po_number', $columnSortOrder);
        } elseif ($columnName === 'po_date') {
            $query->orderBy('record_date', $columnSortOrder);
        } elseif ($columnName === 'quantity') {
            $query->orderBy('quantity', $columnSortOrder);
        } 

        // Paginate the query
        $poQuery = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
        $productionOrders = $poQuery->items();
        $data = [];
        foreach ($productionOrders as $index => $order) {
            $poMaterials = $order->prod_order_materials;
            if ($poMaterials) {
                foreach ($poMaterials as $pomIndex => $pomObject) {
                    if ($pomObject->status == "Partial") {
                        $currentPage = ($start / $length) + 1;
                        $serial = ($currentPage - 1) * $length + $index + 1;

                        $stock = $pomObject->material->stock->closing_balance;
                        $balance = $pomObject->material->bomRecord->quantity * $order->quantity - $pomObject->quantity;

                        $data[] = [
                            'serial' => $serial,
                            'po_id' => $order->po_id,
                            'po_number' => $order->po_number,
                            'po_date' => $order->record_date,
                            'fg_partcode' => $order->material->part_code,
                            'part_code' => $pomObject->material->part_code,
                            'description' => $pomObject->material->description,
                            'make' => $pomObject->material->make,
                            'mpn' => $pomObject->material->mpn,
                            'quantity' => $order->quantity * $pomObject->material->bomRecord->quantity,
                            'stock' => $stock,
                            'balance' => $balance,
                            'shortage' => abs($stock - $balance),
                            'unit' => $pomObject->material->uom->uom_shortcode,
                            'status' => $pomObject->status,
                        ];
                    }
                }
            }
        }

        if (in_array($columnName, ['part_code', 'description', 'make', 'mpn', 'stock', 'shortage', 'unit'])) {
            usort($data, function ($a, $b) use ($columnName) {
                return strcmp($a[$columnName], $b[$columnName]);
            });
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $poQuery->total(),
            "data" => $data,
        ];

        return response()->json($response);
    }
    
    public function poConsolidatedShortageReport()
    {
        return view('reports.po-consolidated-shortage-report');
    }

    public function fetchPoConsolidatedShortageReport(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $query = ProductionOrder::with('material', 'prod_order_materials')
        ->where('production_orders.status', 'Partially Issued');

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('record_date', [$startDate, $endDate]);
        }

        $totalRecords = $query->count();
        $productionOrders = $query->get();

        $data = [];

        // Loop through each production order
        foreach ($productionOrders as $orders => $order) {
            $bomRecords = $order->material?->bom?->bomRecords;
            
            foreach ($bomRecords as $bomKey => $bomRec) {
                $prOdrMat = ProdOrdersMaterial::where('po_id', 'like', $order->po_id)
                    ->where('material_id', 'like', $bomRec->material_id)
                    ->first();

                $quantity = $order->quantity * $bomRec->quantity;
                $stock = $prOdrMat ? $prOdrMat->material->stock->closing_balance : $bomRec->material->stock->closing_balance;

                if ($prOdrMat === null || $prOdrMat->status == "Partial") {
                    $balance = $prOdrMat ? $quantity - $prOdrMat->quantity : $quantity;

                    $matchesSearch = false;
                    if (!empty($search)) {
                        $matchesSearch = 
                            stripos($bomRec->material->description, $search) !== false ||
                            stripos($bomRec->material->part_code, $search) !== false ||
                            stripos($bomRec->material->mpn, $search) !== false ||
                            stripos($bomRec->material->make, $search) !== false ||
                            stripos($bomRec->material->uom->uom_shortcode, $search) !== false ||
                            stripos((string)$stock, $search) !== false;
                    }

                    if (empty($search) || $matchesSearch) {
                        $data[$bomRec->material_id] ??= [
                            'part_code' => $bomRec->material->part_code,
                            'description' => $bomRec->material->description,
                            'make' => $bomRec->material->make,
                            'mpn' => $bomRec->material->mpn,
                            'quantity' => 0,
                            'stock' => $stock,
                            'balance' => 0,
                            'unit' => $bomRec->material->uom->uom_shortcode
                        ];

                        $data[$bomRec->material_id]['quantity'] += $quantity;
                        $data[$bomRec->material_id]['balance'] += $balance;
                    }
                }
            }
        }
        
        $formattedData = array_values($data);

        if (in_array($columnName, ['part_code', 'description', 'make', 'mpn', 'stock', 'shortage', 'unit'])) {
            usort($formattedData, function ($a, $b) use ($columnName, $columnSortOrder) {
                $cmp = strcmp($a[$columnName], $b[$columnName]);
                return ($columnSortOrder === 'asc') ? $cmp : -$cmp;
            });
        }

        $serialNo = $start + 1;
        
        $paginatedData = [];
        foreach ($formattedData as $key => $obj) {
            $index = $start + $key + 1;
            if ($index >= $start && $index < ($start + $length)) {
                $obj['serial'] = $serialNo++;
                $obj['shortage'] = abs($obj['stock'] - $obj['balance']);
                $paginatedData[] = $obj;
            }
        }
        
        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => count($data),
            "data" => $paginatedData,
        ];

        return response()->json($response);
    }

    public function fetchMaterialShortageConsolidated(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $partcode = $request->input('partcode');

        $query = ProductionOrder::with('material','prod_order_materials')->where('production_orders.status', 'Partially Issued');

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('record_date', [$startDate, $endDate]);
        }

        $productionOrders = $query->get();

        foreach ($productionOrders as $order) {
            $bomRecords = $order->material->bom->bomRecords;
            foreach ($bomRecords as $bomObject) {
                $prodOrderMaterial = ProdOrdersMaterial::where('po_id', 'like', $order->po_id)->where('material_id', $bomObject->material_id)->first();
                if ($prodOrderMaterial && $prodOrderMaterial->status == "Partial" && $partcode == $bomObject->material->part_code) {
                    $quantity = $order->quantity * $bomObject->quantity;
                    $stock = $bomObject->material->stock->closing_balance;
                    $shortage = $bomObject->quantity * $order->quantity - $prodOrderMaterial->quantity;
    
                    $data[] = [
                        'po_id' => $order->po_id,
                        'po_number' => $order->po_number,
                        'po_date' => $order->record_date,
                        'part_code' => $bomObject->material->part_code,
                        'description' => $bomObject->material->description,
                        'make' => $bomObject->material->make,
                        'mpn' => $bomObject->material->mpn,
                        'quantity' => $quantity,
                        'stock' => $stock,
                        'shortage' => $shortage,
                        'unit' => $bomObject->material->uom->uom_shortcode,
                        'status' => $prodOrderMaterial->status,
                    ];
                } 
                else if ($partcode == $bomObject->material->part_code && empty($prodOrderMaterial)) {
                    $quantity = $order->quantity * $bomObject->quantity;
                    $stock = $bomObject->material->stock->closing_balance;
                    $shortage = $bomObject->quantity * $order->quantity;
    
                    $data[] = [
                        'po_id' => $order->po_id,
                        'po_number' => $order->po_number,
                        'po_date' => $order->record_date,
                        'part_code' => $bomObject->material->part_code,
                        'description' => $bomObject->material->description,
                        'make' => $bomObject->material->make,
                        'mpn' => $bomObject->material->mpn,
                        'quantity' => $quantity,
                        'stock' => $stock,
                        'shortage' => $shortage,
                        'unit' => $bomObject->material->uom->uom_shortcode,
                        'status' => "Shortage",
                    ];
                }
            }
        }

        $context = [
            'records' => $data,
        ];

        $returnHTML = view('popup.viewMaterialShortageTable', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }
}
