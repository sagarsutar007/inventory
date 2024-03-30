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
        $productionOrder = ProductionOrder::findOrFail($po_id);
        $partCodes = [$productionOrder->material->part_code];
        $quantities = [$productionOrder->quantity];

        $bomRecords = $this->fetchBomRecords($partCodes, $quantities, $po_id);

        $context = [
            'bomRecords' => $bomRecords,
        ];

        $returnHTML = view('production-order.viewBomTable', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
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
}
