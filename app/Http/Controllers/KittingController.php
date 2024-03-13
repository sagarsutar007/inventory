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

class KittingController extends Controller
{
    public function index()
    {
        return view('kitting.list');
    }

    public function viewKittingForm(Request $request)
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

        $bomRecords = $this->fetchBomRecords($partCodes, $quantities);

        $context = [
            'bomRecords' => $bomRecords,
            'prodId' => $po_id,
        ];

        $returnHTML = view('kitting.viewKittingForm', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }

    public function fetchBomRecords($partCodes = [], $quantities = [])
    {
        $bomRecords = [];

        foreach ($partCodes as $key => $partCode) {
            $material = Material::where('part_code', $partCode)->first();

            if ($material && $material->bom) {
                $records = BomRecord::where('bom_id', $material->bom->bom_id)->get();
                foreach ($records as $record) {
                    $closingBalance = Stock::where('material_id', $record->material->material_id)->value('closing_balance');
                    $quantity = $record->quantity * $quantities[$key];
                    if (isset($bomRecords[$record->material->description])) {
                        $bomRecords[$record->material->description]['quantity'] += $quantity;
                    } else {
                        $bomRecords[$record->material->description] = [
                            'material_id' => $record->material->material_id,
                            'part_code' => $record->material->part_code,
                            'material_description' => $record->material->description,
                            'quantity' => $quantity,
                            'bom_qty' => $record->quantity,
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

    public function issueOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'production_id' => 'required|exists:production_orders,po_id',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        foreach ($request->material as $index => $materialId) {
            $existingRecord = ProdOrdersMaterial::where('po_id', $request->production_id)
            ->where('material_id', $materialId)
            ->first();
            $newQuantity = $request->issue[$index];

            if ($existingRecord) {
                $newQuantity += $existingRecord->quantity;
                $existingRecord->update(['quantity' => $newQuantity]);
            } else {
                ProdOrdersMaterial::create([
                    'po_id' => $request->production_id,
                    'material_id' => $materialId,
                    'quantity' => $newQuantity
                ]);
            }
        }
        return response()->json(['success' => true, 'message' => 'Order Issued Successfully!']);
    }
}
