<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


use App\Models\Material;
use App\Models\Bom;
use App\Models\BomRecord;
use App\Models\WarehouseRecord;
use App\Models\Stock;
use App\Models\ProductionOrder;
use App\Models\ProdOrdersMaterial;
use App\Services\UniqueIdGenerator;

class ProductionOrderController extends Controller
{
    public function index()
    {
        if (Gate::allows('admin', Auth::user()) || Gate::allows('view-po', Auth::user())) {
            return view('production-order.list');
        } else {
            abort(403);
        }
    }

    // public function create()
    // {
    //     return view('production-order.create');
    // }

    public function new()
    {
        if (Gate::allows('admin', Auth::user()) || Gate::allows('add-po', Auth::user())) {
            return view('production-order.new');
        } else {
            abort(403);
        }
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
                    // dd($quantity);
                    if (isset($bomRecords[$record->material->description])) {
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
                            'balance' => $prodOrderMaterial ? subtractAndNormalize($quantity, $prodOrderMaterial->quantity) : $quantity,
                            'uom_shortcode' => $record->material->uom->uom_shortcode,
                            'closing_balance' => (float) $closingBalance,
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

        if (!empty($search)) {
            $search = trim(strip_tags($search));
            $query->where('po_number', 'like', '%' . $search . '%')
                ->orWhere('quantity', 'like', '%' . $search . '%')
                ->orWhere('status', 'like', '%' . $search . '%')
                ->orWhere('record_date', 'like', '%' . date('Y-m-d', strtotime($search)) . '%')
                ->orWhereHas('user', function ($us) use ($search) {
                    $us->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('material', function ($q) use ($search) {
                    $q->where('description', 'like', '%' . $search . '%')
                        ->orWhere('part_code', 'like', '%' . $search . '%')
                        ->orWhereHas('uom', function ($u) use ($search) {
                            $u->where('uom_text', 'like', '%' . $search . '%')
                                ->orWhere('uom_shortcode', 'like', '%' . $search . '%');
                        });
                });
        }

        $totalRecords = $query->count();

        if ($columnName === 'description') {
            $query->join('materials', 'materials.material_id', '=', 'production_orders.material_id')
                ->orderBy('materials.description', $columnSortOrder);
        } elseif ($columnName === 'quantity') {
            $query->orderBy('quantity', $columnSortOrder);
        } elseif ($columnName === 'status') {
            $query->orderBy('status', $columnSortOrder);
        } elseif ($columnName === 'po_number') {
            $query->orderBy('po_number', $columnSortOrder);
        } elseif ($columnName === 'fg_partcode') {
            $query->join('materials', 'materials.material_id', '=', 'production_orders.material_id')
                ->orderBy('materials.part_code', $columnSortOrder);
        } elseif ($columnName === 'uom_shortcode') {
            $query->join('materials', 'materials.material_id', '=', 'production_orders.material_id')
                ->join('uom_units', 'materials.uom_id', '=', 'uom_units.uom_id')
                ->orderBy('uom_units.uom_shortcode', $columnSortOrder);
        } elseif ($columnName === 'created_at') {
            $query->orderBy('record_date', $columnSortOrder);
        } elseif ($columnName === 'created_by') {
            $query->leftJoin('users', 'users.id', '=', 'production_orders.created_by')
                ->orderBy('users.name', $columnSortOrder);
        } else {

        }

        // Paginate the query
        if ($length == -1) {
            $productionOrders = $query->get();
        } else {
            $poQuery = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
            $productionOrders = $poQuery->items();
        }

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
                    // 'created_at' => date('d-m-Y h:i a', strtotime('+5 hours 30 minutes', strtotime($order->created_at))),
                    'created_at' => date('d-m-Y', strtotime($order->record_date)),
                    'created_by' => $order->user->name,
                    'status' => $order->status,
                ];
            }
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
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
            $poNumber = UniqueIdGenerator::generateId('production_orders', 'po_number', 'PO');
            for ($i = 0; $i < count($request->part_code); $i++) {
                $material = Material::where('part_code', $request->part_code[$i])->first();
                ProductionOrder::create([
                    'old_po_number' => $poNumber,
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
            $poNumber = UniqueIdGenerator::generateId('production_orders', 'po_number', 'PO');

            for ($i = 0; $i < count($request->part_code); $i++) {

                $material = Material::where('part_code', $request->part_code[$i])->first();

                $productionOrder = ProductionOrder::create([
                    'old_po_number' => $poNumber,
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
        if (Gate::allows('admin', Auth::user()) || Gate::allows('view-po-report', Auth::user())) {
            return view('reports.po-report');
        } else {
            abort(403);
        }
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

        if (!empty($searchTerm)) {
            $query->whereHas('material', function ($q) use ($searchTerm) {
                $q->where('description', 'like', '%' . $searchTerm . '%')
                    ->orWhere('part_code', 'like', '%' . $searchTerm . '%');
            });

            
        }

        if (!empty($status)) {
            if ($status === "Partially + Pending") {
                $query->whereNot('status', 'Completed');
            } else {
                $query->where('status', 'like', $status);
            }
        }


        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('record_date', [$startDate, $endDate]);
        }

        if (!empty($search)) {
            $query->whereHas('material', function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%');
            });
<<<<<<< Updated upstream
=======

            $query->where('po_number', 'like', '%' . $search . '%');
        }
>>>>>>> Stashed changes

            $query->orWhere('po_number', 'like', '%' . $search . '%');
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
                ->join('uom_units', 'materials.uom_id', '=', 'uom_units.uom_id')
                ->orderBy('uom_units.uom_shortcode', $columnSortOrder);
        } elseif ($columnName === 'status') {
            $query->orderBy('status', $columnSortOrder);
        } elseif ($columnName === 'serial') {
            $query->orderBy('created_at', $columnSortOrder == 'asc' ? 'desc' : 'asc');
        }

        // Paginate the query
        if ($length == -1) {
            $productionOrders = $query->get();
            $totalCount = $totalRecords;
        } else {
            $poQuery = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
            $productionOrders = $poQuery->items();
        }

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
            "recordsFiltered" => $totalCount ?? $poQuery->total(),
            "data" => $data,
        ];

        return response()->json($response);
    }

    public function poShortageReport()
    {
        if (Gate::allows('admin', Auth::user()) || Gate::allows('view-po-shortage', Auth::user())) {
            return view('reports.po-shortage-report');
        } else {
            abort(403);
        }
    }

    public function fetchPoShortageReport(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        // $startDate = $request->input('startDate');
        // $endDate = $request->input('endDate');
        // $searchTerm = $request->input('searchTerm');

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $query = ProductionOrder::with('material.bom.bomRecords', 'prod_order_materials')->whereNot('production_orders.status', 'Completed');

        // if (!empty($startDate) && !empty($endDate)) {
        //     $query->whereBetween('record_date', [$startDate, $endDate]);
        // }

        if (!empty($search)) {
            $query->where(function ($qury) use ($search) {
                $qury->whereHas('prod_order_materials', function ($qry) use ($search) {
                    $qry->whereHas('material', function ($q) use ($search) {
                        $q->where('description', 'like', '%' . $search . '%')
                            ->orWhere('part_code', 'like', '%' . $search . '%')
                            ->orWhere('mpn', 'like', '%' . $search . '%')
                            ->orWhere('make', 'like', '%' . $search . '%')
                            ->orWhereHas('uom', function ($u) use ($search) {
                                $u->where('uom_text', 'like', '%' . $search . '%')
                                    ->orWhere('uom_shortcode', 'like', '%' . $search . '%');
                            });
                    });
                })
                    ->orWhereHas('material.bom.bomRecords.material', function ($bomQuery) use ($search) {
                        $bomQuery->where('description', 'like', '%' . $search . '%')
                            ->orWhere('part_code', 'like', '%' . $search . '%')
                            ->orWhere('mpn', 'like', '%' . $search . '%')
                            ->orWhere('make', 'like', '%' . $search . '%')
                            ->orWhereHas('uom', function ($un) use ($search) {
                                $un->where('uom_text', 'like', '%' . $search . '%')
                                    ->orWhere('uom_shortcode', 'like', '%' . $search . '%');
                            });
                    })
                    ->orWhereHas('material', function ($bomQuery) use ($search) {
                        $bomQuery->where('description', 'like', '%' . $search . '%')
                            ->orWhere('part_code', 'like', '%' . $search . '%');
                    })
                    ->orWhere('po_number', 'like', '%' . $search . '%')
                    ->orWhere('quantity', 'like', '%' . $search . '%')
                    ->orWhere('record_date', 'like', '%' . $search . '%');
            });
        }

        if ($columnName === 'po_number') {
            $query->orderBy('po_number', $columnSortOrder);
        } elseif ($columnName === 'po_date') {
            $query->orderBy('record_date', $columnSortOrder);
        } elseif ($columnName === 'quantity') {
            $query->orderBy('quantity', $columnSortOrder);
        }

        // dd($query->toRawSql());

        // Paginate the query
        // $poQuery = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
        // $productionOrders = $poQuery->items();
        $productionOrders = $query->get();

        $data = [];
        $currentPage = ($start / $length) + 1;
        // $serial = ($currentPage - 1) * $length + 1;

        foreach ($productionOrders as $index => $order) {
            $bomRecords = $order->material?->bom?->bomRecords;
            if ($bomRecords) {
                foreach ($bomRecords as $bomRecord) {
                    $pomRecord = ProdOrdersMaterial::where('po_id', 'like', $order->po_id)
                        ->where('material_id', 'like', $bomRecord->material_id)
                        ->first();

                    if ($pomRecord == null || $pomRecord->status == "Partial") {

                        $stock = $bomRecord->material->stock?->closing_balance;
                        $balance = $bomRecord->quantity * $order->quantity - $pomRecord?->quantity;

                        if ($balance > $stock) {
                            $shortage = number_format(abs($stock - $balance), 3);

                            $data[] = [
                                'po_id' => $order->po_id,
                                'po_number' => $order->po_number,
                                'po_date' => $order->record_date,
                                'fg_partcode' => $order->material->part_code,
                                'part_code' => $bomRecord->material->part_code,
                                'description' => $bomRecord->material->description,
                                'make' => $bomRecord->material->make,
                                'mpn' => $bomRecord->material->mpn,
                                'quantity' => number_format($order->quantity * $bomRecord->quantity, 3),
                                'stock' => number_format($stock, 3),
                                'balance' => number_format($balance, 3),
                                'shortage' => $shortage,
                                'unit' => $bomRecord->material->uom->uom_shortcode,
                                'status' => $pomRecord->status ?? '',
                                'issued' => number_format($pomRecord->quantity ?? 0, 3),
                            ];
                        }
                    }
                }
            }
        }

        usort($data, function ($a, $b) use ($columnName, $columnSortOrder) {
            if (in_array($columnName, ['quantity', 'issued', 'balance', 'stock', 'shortage'])) {
                $valueA = floatval($a[$columnName]);
                $valueB = floatval($b[$columnName]);

                // Perform numeric comparison
                if ($columnSortOrder === 'asc') {
                    return $valueA - $valueB;
                } else {
                    return $valueB - $valueA;
                }
            } else if ($columnName != "serial") {
                if ($columnSortOrder === 'asc') {
                    return strcmp($a[$columnName], $b[$columnName]);
                } else {
                    return strcmp($b[$columnName], $a[$columnName]);
                }
            }

        });

        $totalRecords = count($data);

        if ($length != -1) {
            $data = array_slice($data, $start, $length);
        }


        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $data,
        ];

        return response()->json($response);
    }

    public function poConsolidatedShortageReport()
    {
        if (Gate::allows('admin', Auth::user()) || Gate::allows('view-po-short-cons', Auth::user())) {
            return view('reports.po-consolidated-shortage-report');
        } else {
            abort(403);
        }
    }

    // public function fetchPoConsolidatedShortageReport(Request $request)
    // {
    //     $draw = $request->input('draw');
    //     $start = $request->input('start');
    //     $length = $request->input('length');
    //     $search = $request->input('search')['value'];

    //     // $startDate = $request->input('startDate');
    //     // $endDate = $request->input('endDate');

    //     $order = $request->input('order');
    //     $columnIndex = $order[0]['column'];
    //     $columnName = $request->input('columns')[$columnIndex]['name'];
    //     $columnSortOrder = $order[0]['dir'];

    //     $query = ProductionOrder::with('material', 'prod_order_materials')
    //     ->whereNot('production_orders.status', 'Complete');

    //     // if (!empty($startDate) && !empty($endDate)) {
    //     //     $query->whereBetween('record_date', [$startDate, $endDate]);
    //     // }

    //     // $totalRecords = $query->count();
    //     $productionOrders = $query->get();

    //     $data = [];

    //     // Loop through each production order
    //     foreach ($productionOrders as $orders => $order) {
    //         $bomRecords = $order->material?->bom?->bomRecords;

    //         foreach ($bomRecords as $bomKey => $bomRec) {
    //             $prOdrMat = ProdOrdersMaterial::where('po_id', 'like', $order->po_id)
    //                 ->where('material_id', 'like', $bomRec->material_id)
    //                 ->first();

    //             $quantity = $order->quantity * $bomRec->quantity;
    //             // $stock = $prOdrMat ? $prOdrMat->material->stock->closing_balance : $bomRec->material->stock->closing_balance;
    //             $stock = $bomRec->material->stock->closing_balance;

    //             if ($prOdrMat === null || $prOdrMat->status == "Partial") {
    //                 $balance = $prOdrMat ? $quantity - $prOdrMat->quantity : $quantity;

    //                 $matchesSearch = false;
    //                 if (!empty($search)) {
    //                     $matchesSearch =
    //                         stripos($bomRec->material->description, $search) !== false ||
    //                         stripos($bomRec->material->part_code, $search) !== false ||
    //                         stripos($bomRec->material->mpn, $search) !== false ||
    //                         stripos($bomRec->material->make, $search) !== false ||
    //                         stripos($bomRec->material->uom->uom_shortcode, $search) !== false ||
    //                         stripos((string)$stock, $search) !== false;
    //                 }

    //                 if (empty($search) || $matchesSearch) {
    //                     $data[$bomRec->material_id] ??= [
    //                         'part_code' => $bomRec->material->part_code,
    //                         'description' => $bomRec->material->description,
    //                         'make' => $bomRec->material->make,
    //                         'mpn' => $bomRec->material->mpn,
    //                         'quantity' => 0,
    //                         'stock' => $stock,
    //                         'balance' => 0,
    //                         'unit' => $bomRec->material->uom->uom_shortcode
    //                     ];

    //                     $data[$bomRec->material_id]['quantity'] += $quantity;
    //                     $data[$bomRec->material_id]['balance'] += $balance;
    //                 }
    //             }
    //         }
    //     }

    //     $formattedData = array_values($data);

    //     if (in_array($columnName, ['part_code', 'description', 'make', 'mpn', 'stock', 'quantity', 'shortage', 'unit'])) {
    //         usort($formattedData, function ($a, $b) use ($columnName, $columnSortOrder) {
    //             $cmp = strcmp($a[$columnName], $b[$columnName]);
    //             return ($columnSortOrder === 'asc') ? $cmp : -$cmp;
    //         });
    //     }

    //     $serialNo = $start + 1;

    //     if ($length == -1) {
    //         $length = 900000000000000000;
    //     }

    //     $paginatedData = [];
    //     foreach ($formattedData as $key => $obj) {
    //         $index = $start + $key + 1;
    //         if ($index >= $start && $index < ($start + $length)) {
    //             $obj['serial'] = $serialNo++;

    //             $stock = $obj['stock'];
    //             $balance = $obj['balance'];

    //             $obj['quantity'] = formatQuantity($obj['quantity']);
    //             $obj['stock'] = formatQuantity($obj['stock']);
    //             $obj['balance'] = formatQuantity($obj['balance']);

    //             if ($obj['stock'] >= $obj['balance']) {
    //                 $obj['shortage'] = "0.000";
    //             } else {
    //                 $obj['shortage'] = abs($stock - $balance);
    //                 $obj['shortage'] = formatQuantity($obj['shortage']);
    //                 $paginatedData[] = $obj;
    //             }
    //         }
    //     }

    //     $response = [
    //         "draw" => intval($draw),
    //         "recordsTotal" => count($paginatedData),
    //         "recordsFiltered" => count($paginatedData),
    //         "data" => $paginatedData,
    //     ];

    //     return response()->json($response);
    // }

    public function fetchPoConsolidatedShortageReport(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length', 10);
        $search = $request->input('search')['value'];

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $query = ProductionOrder::with(['material.bom.bomRecords.material.uom', 'prod_order_materials.material.stock'])
            ->whereNot('status', 'Completed');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('prod_order_materials.material', function ($q) use ($search) {
                    $q->where('description', 'like', '%' . $search . '%')
                        ->orWhere('part_code', 'like', '%' . $search . '%')
                        ->orWhere('mpn', 'like', '%' . $search . '%')
                        ->orWhere('make', 'like', '%' . $search . '%')
                        ->orWhereHas('uom', function ($q) use ($search) {
                            $q->where('uom_text', 'like', '%' . $search . '%')
                                ->orWhere('uom_shortcode', 'like', '%' . $search . '%');
                        });
                })
                    ->orWhereHas('material.bom.bomRecords.material', function ($q) use ($search) {
                        $q->where('description', 'like', '%' . $search . '%')
                            ->orWhere('part_code', 'like', '%' . $search . '%')
                            ->orWhere('mpn', 'like', '%' . $search . '%')
                            ->orWhere('make', 'like', '%' . $search . '%')
                            ->orWhereHas('uom', function ($q) use ($search) {
                                $q->where('uom_text', 'like', '%' . $search . '%')
                                    ->orWhere('uom_shortcode', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        $productionOrders = $query->get();

        $data = [];
        foreach ($productionOrders as $order) {
            $bomRecords = $order->material->bom->bomRecords ?? [];

            foreach ($bomRecords as $bomRec) {
                $prOdrMat = $order->prod_order_materials->firstWhere('material_id', $bomRec->material_id);
                $quantity = $order->quantity * $bomRec->quantity;
                $stock = $bomRec->material->stock->closing_balance ?? 0;
                $balance = $prOdrMat ? $quantity - $prOdrMat->quantity : $quantity;

                if ($prOdrMat === null || $prOdrMat->status == "Partial") {
                    $matchesSearch = empty($search) || stripos($bomRec->material->description, $search) !== false
                        || stripos($bomRec->material->part_code, $search) !== false
                        || stripos($bomRec->material->mpn, $search) !== false
                        || stripos($bomRec->material->make, $search) !== false
                        || stripos($bomRec->material->uom->uom_shortcode, $search) !== false
                        || stripos((string) $stock, $search) !== false;
                    if ($matchesSearch) {
                        $materialId = $bomRec->material_id;
                        if (!isset($data[$materialId])) {
                            $data[$materialId] = [
                                'part_code' => $bomRec->material->part_code,
                                'description' => $bomRec->material->description,
                                'make' => $bomRec->material->make,
                                'mpn' => $bomRec->material->mpn,
                                'quantity' => 0,
                                'stock' => $stock,
                                'balance' => 0,
                                'unit' => $bomRec->material->uom->uom_shortcode,
                            ];
                        }

                        $data[$materialId]['quantity'] += $quantity;
                        $data[$materialId]['balance'] += $balance;
                    }
                }
            }
        }

        $totalRecords = count($productionOrders);
        $newData = [];
        foreach ($data as $dt) {
            if ($dt['balance'] > $dt['stock']) {
                $newData[] = $dt;
            }
        }

        $formattedData = array_values($newData);

        if (in_array($columnName, ['part_code', 'description', 'make', 'mpn', 'stock', 'quantity', 'balance', 'unit'])) {
            usort($formattedData, function ($a, $b) use ($columnName, $columnSortOrder) {
                $cmp = strcmp($a[$columnName], $b[$columnName]);
                return ($columnSortOrder === 'asc') ? $cmp : -$cmp;
            });
        }

        $serialNo = $start;
        $paginatedData = [];
        if ($start > 0) {
            $newArr = array_slice($formattedData, $start, $length);
        } else {
            if ($length > 0) {
                $newArr = array_slice($formattedData, 0, $length);
            } else {
                $newArr = $formattedData;
            }

        }

        foreach ($newArr as $obj) {
            $serialNo = $serialNo + 1;
            $obj['serial'] = $serialNo;
            $stock = $obj['stock'];
            $balance = $obj['balance'];

            if ($stock < $balance) {
                $obj['quantity'] = formatQuantity($obj['quantity']);
                $obj['stock'] = formatQuantity($obj['stock']);
                $obj['balance'] = formatQuantity($obj['balance']);
                $obj['shortage'] = $stock >= $balance ? "0.000" : formatQuantity(abs($stock - $balance));

                $paginatedData[] = $obj;
            }

        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => count($newData),
            "recordsFiltered" => count($newData),
            "data" => $paginatedData,
        ];

        return response()->json($response);
    }


    public function fetchMaterialShortageConsolidated(Request $request)
    {
        // $startDate = $request->input('startDate');
        // $endDate = $request->input('endDate');
        $partcode = $request->input('partcode');

        $query = ProductionOrder::with('material', 'prod_order_materials')->whereNot('production_orders.status', 'Completed');

        // if (!empty($startDate) && !empty($endDate)) {
        //     $query->whereBetween('record_date', [$startDate, $endDate]);
        // }

        $productionOrders = $query->get();
        $data = [];
        foreach ($productionOrders as $order) {
            $bomRecords = $order->material->bom->bomRecords;
            foreach ($bomRecords as $bomObject) {
                $prodOrderMaterial = ProdOrdersMaterial::where('po_id', 'like', $order->po_id)->where('material_id', $bomObject->material_id)->first();
                if ($prodOrderMaterial && $prodOrderMaterial->status == "Partial" && $partcode == $bomObject->material->part_code) {
                    $quantity = $order->quantity * $bomObject->quantity;
                    $stock = $bomObject->material->stock->closing_balance;
                    $balance = $bomObject->quantity * $order->quantity - $prodOrderMaterial->quantity;
                    if ($stock >= $balance) {
                        $shortage = 0;
                    } else {
                        $shortage = abs($stock - $balance);
                    }

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
                        'balance' => $balance,
                        'shortage' => $shortage,
                        'unit' => $bomObject->material->uom->uom_shortcode,
                        'status' => $prodOrderMaterial->status,
                    ];
                } else if ($partcode == $bomObject->material->part_code && empty($prodOrderMaterial)) {
                    $quantity = $order->quantity * $bomObject->quantity;
                    $stock = $bomObject->material->stock->closing_balance;
                    $balance = $bomObject->quantity * $order->quantity;
                    if ($stock >= $balance) {
                        $shortage = 0;
                    } else {
                        $shortage = abs($stock - $balance);
                    }
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
                        'balance' => $balance,
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

    public function ploShortageReport()
    {
        if (Gate::allows('admin', Auth::user()) || Gate::allows('view-plan-short', Auth::user())) {
            return view('reports.planned-shortage-report');
        } else {
            abort(403);
        }
    }

    public function fetchPlannedShortage(Request $request)
    {
        $partcodes = $request->input('part_code');
        $quantities = $request->input('quantity');
        $materialsArr = [];

        $combinedQuantities = [];
        $allMaterials = [];

        foreach ($partcodes as $index => $partcode) {
            $material = Material::where('part_code', 'like', $partcode)->first();
            $quantity = $quantities[$index];
            $materials = $this->getPlannedStats($partcode, $quantity);
            $combinedMaterials = [];

            foreach ($materials as $record) {
                $partCode = $record['part_code'];
                $reqQty = $record['req_qty'];
                if (!isset($combinedQuantities[$partCode])) {
                    $combinedQuantities[$partCode]['req_qty'] = $reqQty;
                } else {
                    $combinedQuantities[$partCode]['req_qty'] = $reqQty;
                }
                $combinedMaterials[] = $record;
            }

            foreach ($materials as $record) {
                $partCode = $record['part_code'];
                $record['req_qty'] = $combinedQuantities[$partCode]['req_qty'];
                $record['short_qty'] = ($record['stock_qty'] < ($record['req_qty'] + $record['reserved_qty'])) ? abs($record['stock_qty'] - ($record['req_qty'] + $record['reserved_qty'])) : 0;
                $record['status'] = ($record['short_qty'] === 0) ? "" : "Shortage";

                $allMaterials[] = $record;
            }

            $materialsArr[] = [
                'fg_partcode' => $partcode,
                'description' => $material->description,
                'quantity' => $quantity,
                'unit' => $material->uom->uom_shortcode,
                'records' => $combinedMaterials
            ];
        }

        $aggMaterials = [];

        foreach ($allMaterials as $key => $obj) {
            if (isset($aggMaterials[$obj['part_code']])) {
                $aggMaterials[$obj['part_code']]['bom_qty'] += $obj['bom_qty'];
                $aggMaterials[$obj['part_code']]['req_qty'] += $obj['req_qty'];
                // $aggMaterials[$obj['part_code']]['reserved_qty'] += $obj['reserved_qty'];
                $aggMaterials[$obj['part_code']]['short_qty'] += $obj['short_qty'];


            } else {
                $aggMaterials[$obj['part_code']] = $obj;
            }

            $aggMaterials[$obj['part_code']]['short_qty'] = ($aggMaterials[$obj['part_code']]['stock_qty'] < ($aggMaterials[$obj['part_code']]['req_qty'] + $aggMaterials[$obj['part_code']]['reserved_qty'])) ? abs($aggMaterials[$obj['part_code']]['stock_qty'] - ($aggMaterials[$obj['part_code']]['req_qty'] + $aggMaterials[$obj['part_code']]['reserved_qty'])) : 0;
            $aggMaterials[$obj['part_code']]['status'] = ($aggMaterials[$obj['part_code']]['short_qty'] === 0) ? "" : "Shortage";
        }

        $context = [
            'materials' => $materialsArr,
            'combinedMaterials' => $aggMaterials
        ];

        $returnHTML = view('popup.viewPlannedShortageTable', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }

    protected function getPlannedStats($partcode = "", $quantity = 1)
    {
        $material = Material::with('bom.bomRecords')->where('part_code', 'like', $partcode)->first();
        $bomRecords = $material->bom?->bomRecords;
        $data = [];
        if ($bomRecords) {
            foreach ($bomRecords as $records => $record) {

                $bomQty = $record->quantity;
                $reqQty = $bomQty * $quantity;
                // $reservedQty = $this->countReservedQty($record->material_id);
                $reservedQty = $this->getMaterialReservedQty($record->material_id);


                $stockQty = (float) $record->material->stock->closing_balance;
                $shortQty = ($stockQty < ($reqQty + $reservedQty)) ? abs($stockQty - ($reqQty + $reservedQty)) : 0;

                $status = ($stockQty < ($reqQty + $reservedQty)) ? "Shortage" : "";

                $data[] = [
                    'part_code' => $record->material->part_code,
                    'description' => $record->material->description,
                    'category' => $record->material->category->category_name,
                    'commodity' => $record->material->commodity->commodity_name,
                    'make' => $record->material->make,
                    'mpn' => $record->material->mpn,
                    'unit' => $record->material->uom->uom_shortcode,
                    'bom_qty' => $bomQty,
                    'req_qty' => $reqQty,
                    'stock_qty' => $stockQty,
                    'reserved_qty' => $reservedQty,
                    'short_qty' => $shortQty,
                    'status' => $status,
                ];
            }
        }

        return $data;
    }

    public static function getMaterialReservedQty($materialId)
    {
        $reservedQty = Material::getReservedQty($materialId);
        return $reservedQty;
    }

    protected function countReservedQty($material_id = "", $data = false)
    {
        $reservedQty = 0;
        $productionOrders = ProductionOrder::with('material.uom', 'prod_order_materials')->whereNot('status', 'Completed')->get();
        foreach ($productionOrders as $prodOrders => $order) {
            $prodMaterial = $order->material;
            $bomRecords = $prodMaterial->bom->bomRecords;
            foreach ($bomRecords as $records => $record) {
                $prodOrderMaterial = ProdOrdersMaterial::where('po_id', 'like', $order->po_id)->where('material_id', $record->material_id)->first();
                if ($prodOrderMaterial && $prodOrderMaterial->material_id == $material_id) {
                    $reservedQty = $this->getMaterialReservedQty($material_id);
                    if ($data) {
                        $material = Material::with('category', 'commodity', 'uom')->find($material_id);
                        $data = [
                            'po_number' => $order->po_number,
                            'po_status' => $order->status,
                            'po_qty' => $order->quantity,
                            'partcode' => $order->material->part_code,
                            'description' => $order->material->description,
                            'unit' => $order->material->uom->uom_shortcode,
                            'issued' => $prodOrderMaterial->quantity,
                            'type' => 'Production Order',
                            'quantity' => $reservedQty,
                            'unit' => $material->uom->uom_shortcode,
                        ];

                        return $data;
                    } else {
                        return $reservedQty;
                    }

                } else if ($record->material_id == $material_id) {
                    $reservedQty = $order->quantity * $record->quantity;
                    if ($data) {
                        $material = Material::with('category', 'commodity')->find($material_id);
                        $data = [
                            'po_number' => $order->po_number,
                            'po_status' => $order->status,
                            'po_qty' => $order->quantity,
                            'partcode' => $order->material->part_code,
                            'description' => $order->material->description,
                            'unit' => $order->material->uom->uom_shortcode,
                            'issued' => 0,
                            'type' => 'Bill of Material',
                            'quantity' => $reservedQty,
                            'unit' => $material->uom->uom_shortcode,
                        ];

                        return $data;
                    } else {
                        return $reservedQty;
                    }
                }
            }
        }
        return NULL;
    }

    protected function viewReservedQty($material_id = "")
    {
        $records = DB::select('CALL get_production_orders_by_material_id(?)', [$material_id]);
        $recordsArray = json_decode(json_encode($records), true);
        return $recordsArray;
    }

    public function calcReservedQty(Request $request)
    {
        $partcode = $request->input('partcode');
        $material = Material::where('part_code', $partcode)->first();
        $record = $this->viewReservedQty($material->material_id, TRUE);
        // dd($material->material_id);
        $context = [
            'records' => $record
        ];

        $returnHTML = view('popup.viewReservedQuantity', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));

    }

    public function showStockTrans(Request $request)
    {
        $partcode = $request->input('partcode');
        // $fromdate = date('');
        // $todate = date('');


        $material = Material::with('stock')->where('part_code', $partcode)->first();

        $openingTransactions = WarehouseRecord::with('warehouse', 'material')
            ->where('material_id', $material->material_id)
            ->whereHas('warehouse', function ($q) {
                $q->orderBy('transaction_id', 'asc');
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $transactions = WarehouseRecord::with('warehouse', 'material')
            ->where('material_id', $material->material_id)
            ->whereHas('warehouse', function ($q) {
                $q->orderBy('transaction_id', 'asc');
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $context = [
            'transactions' => $transactions,
            'opening' => $material->stock->opening_balance
        ];

        $returnHTML = view('popup.viewStockTransactions', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }
}
