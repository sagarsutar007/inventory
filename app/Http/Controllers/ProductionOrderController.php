<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


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

        $returnHTML = view('production-order.viewBomTable', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }
    public function fetchBomRecords($partCodes = [], $quantities = [])
    {
        $bomRecords = [];

        foreach ($partCodes as $key => $partCode) {
            $material = Material::where('part_code', $partCode)->first();

            if ($material && $material->bom) {
                $records = BomRecord::where('bom_id', $material->bom->bom_id)->get();
                $closingBalance = Stock::where('material_id', $material->material_id)->value('closing_balance');

                foreach ($records as $record) {
                    if ($record->material->type == "semi-finished") {
                        $semiFinishedRecords = BomRecord::where('bom_id', $record->material->bom->bom_id)->get();
                        foreach ($semiFinishedRecords as $semiFinishedRecord) {
                            $requiredMaterial = Material::find($semiFinishedRecord->material_id);
                            $requiredMaterialStock = Stock::where('material_id', $material->material_id)->value('closing_balance');
                            if ($requiredMaterial) {
                                $quantity = $semiFinishedRecord->quantity * $record->quantity * $quantities[$key];
                                if (isset($bomRecords[$requiredMaterial->description])) {
                                    $bomRecords[$requiredMaterial->description]['quantity'] += $quantity;
                                } else {
                                    $bomRecords[$requiredMaterial->description] = [
                                        'material_id' => $requiredMaterial->material_id,
                                        'part_code' => $requiredMaterial->part_code,
                                        'material_description' => $requiredMaterial->description,
                                        'quantity' => $quantity,
                                        'uom_shortcode' => $requiredMaterial->uom->uom_shortcode,
                                        'closing_balance' => $requiredMaterialStock,
                                    ];
                                }
                            }
                        }
                    } else {
                        $quantity = $record->quantity * $quantities[$key];
                        if (isset($bomRecords[$record->material->description])) {
                            $bomRecords[$record->material->description]['quantity'] += $quantity;
                        } else {
                            $bomRecords[$record->material->description] = [
                                'material_id' => $record->material->material_id,
                                'part_code' => $record->material->part_code,
                                'material_description' => $record->material->description,
                                'quantity' => $quantity,
                                'uom_shortcode' => $record->material->uom->uom_shortcode,
                                'closing_balance' => $closingBalance,
                            ];
                        }
                    }
                }
            }
        }

        $bomRecords = array_values($bomRecords);

        return $bomRecords;
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
            $lastPoNumber = ProductionOrder::max('po_number');
            $nextPoNumber = $lastPoNumber ? $lastPoNumber + 1 : 100000;

            for ($i = 0; $i < count($request->part_code); $i++) {

                $material = Material::where('part_code', $request->part_code[$i])->first();

                $productionOrder = ProductionOrder::create([
                    'po_number' => $nextPoNumber,
                    'material_id' => $material->material_id,
                    'quantity' => $request->quantity[$i],
                    'status' => 'Pending',
                    'created_by' => Auth::id(),
                ]);

                $bomRecords = $this->fetchBomRecords([$request->part_code[$i]], [$request->quantity[$i]]);

                foreach ($bomRecords as $bomRecord) {
                    ProdOrdersMaterial::create([
                        'po_id' => $productionOrder->po_id,
                        'material_id' => $bomRecord['material_id'],
                        'quantity' => $bomRecord['quantity'],
                        'created_by' => Auth::id(),
                    ]);
                }
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
            ->whereIn('type', ['finished'])
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

    public function getProdOrderRecords(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $query = ProductionOrder::with('material', 'user');

        if (!empty($search)) {
            $query->where('po_number', 'like', '%' . $search . '%')
                ->orWhereHas('material', function ($q) use ($search) {
                    $q->where('part_code', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
        }

        // Count total records
        $totalRecords = $query->count();

        // Apply ordering
        if ($columnName === 'material_name') {
            $query->join('materials', 'materials.material_id', '=', 'production_orders.material_id')
                ->orderBy('materials.description', $columnSortOrder);
        } else {
            $query->orderBy($columnName, $columnSortOrder);
        }

        // Paginate the query
        $prodOrders = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
        $data = [];

        foreach ($prodOrders as $index => $prodOrder) {
            $material = $prodOrder->material;
            if ($material) {

                $action = '<button class="btn btn-sm btn-link"><i class="fas fa-eye text-primary"></i></button>' . '<button class="btn btn-sm btn-link"><i class="fas fa-edit text-warning"></i></button>' . '<button class="btn btn-sm btn-link"><i class="fas fa-trash text-danger"></i></button>';

                $createdByUser = $prodOrder->user()->first(); // Retrieve the user associated with the created_by field

                $createdByName = $createdByUser ? $createdByUser->name : 'Unknown';

                $data[] = [
                    'po_number' => $prodOrder->po_number,
                    'material_name' => $material->description,
                    'quantity' => $prodOrder->quantity,
                    'status' => $prodOrder->status,
                    'created_by' => $createdByName,
                    'created_at' => $prodOrder->created_at->format('d-m-Y H:i:s'),
                    'action' => $action,
                ];
            }
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $prodOrders->total(),
            "data" => $data,
        ];

        return response()->json($response);
    }
}
