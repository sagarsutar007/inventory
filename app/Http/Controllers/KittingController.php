<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;


use App\Models\Material;
use App\Models\BomRecord;
use App\Models\Stock;
use App\Models\ProductionOrder;
use App\Models\ProdOrdersMaterial;
use App\Models\Warehouse;
use App\Models\WarehouseRecord;

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

        $bomRecords = $this->fetchBomRecords($partCodes, $quantities, $po_id);

        $context = [
            'bomRecords' => $bomRecords,
            'prodId' => $po_id,
        ];

        $returnHTML = view('kitting.viewKittingForm', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }

    public function fetchBomRecords($partCodes = [], $quantities = [], $po_id = '')
    {
        $bomRecords = [];

        foreach ($partCodes as $key => $partCode) {
            $material = Material::where('part_code', $partCode)->first();

            if ($material && $material->bom) {
                $records = BomRecord::where('bom_id', $material->bom->bom_id)->get();
                foreach ($records as $record) {
                    $prodOrderMaterial = ProdOrdersMaterial::where('po_id', $po_id)->where('material_id', $record->material->material_id)->first();
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
                            'issued' => $prodOrderMaterial ? $prodOrderMaterial->quantity : 0,
                            'balance' => $prodOrderMaterial ? $quantity - $prodOrderMaterial->quantity : $quantity,
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

        DB::beginTransaction();

        try {
            $prodOrder = ProductionOrder::where('po_id', $request->production_id)->first();

            $warehouseIssue = new Warehouse();
            $warehouseIssue->warehouse_id = Str::uuid();
            $warehouseIssue->transaction_id = $this->generateTransactionId();
            $warehouseIssue->type = 'issue';
            $warehouseIssue->created_by = Auth::id();
            $warehouseIssue->created_at = Carbon::now();
            $warehouseIssue->date = Carbon::now()->toDateString();
            $warehouseIssue->save();

            foreach ($request->material as $index => $materialId) {
                $stock = Stock::where('material_id', $materialId)->first();
                $reqQty = $request->issue[$index];
                $newQuantity = $reqQty;
                
                $warehouseRecord = new WarehouseRecord();
                $warehouseRecord->warehouse_record_id = Str::uuid();
                $warehouseRecord->warehouse_id = $warehouseIssue->warehouse_id;
                $warehouseRecord->material_id = $materialId;
                $warehouseRecord->warehouse_type = 'issued';
                $warehouseRecord->quantity = $newQuantity;
                $warehouseRecord->created_at = now();
                $warehouseRecord->save();

                if ($stock && $newQuantity <= $stock->closing_balance && $newQuantity != 0) {
                    
                    $existingRecord = ProdOrdersMaterial::where('po_id', $request->production_id)
                        ->where('material_id', $materialId)
                        ->first();

                    $status = $this->getStatus($request->production_id, $materialId, $newQuantity);

                    if ($existingRecord) {
                        $newQuantity += $existingRecord->quantity;
                        $existingRecord->update(['quantity' => $newQuantity, 'status' => $status]);
                    } else {
                        ProdOrdersMaterial::create([
                            'po_id' => $request->production_id,
                            'material_id' => $materialId,
                            'quantity' => $newQuantity,
                            'created_by' => Auth::id(),
                            'status' => $status,
                        ]);
                    }

                    $stock->issue_qty += $reqQty;
                    $stock->save();
                } 
                
                // else{
                //     DB::rollBack();

                //     $material = Material::findOrFail($materialId);
                //     return response()->json([
                //         'status' => false,
                //         'message' => 'Invalid quantity for material ' . $material->description,
                //     ], 422);
                // }
            }

            $this->updateProdOrderStatus($request->production_id);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Order Issued Successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error issuing order: ' . $e->getMessage(),
            ]);
        }
    }

    public function generateTransactionId()
    {
        $year = date('y');
        $weekNumber = date('W');
        $date = date('d');
        $transactionId = sprintf('%02d%02d%02d', $year, $weekNumber, $date);
        $lastTransactionId = Warehouse::where('transaction_id', 'like', $year . '%')->max('transaction_id');
        if ($lastTransactionId) {
            $lastNumericPart = intval(substr($lastTransactionId, 6));
            $newNumericPart = str_pad($lastNumericPart + 1, 5, '0', STR_PAD_LEFT);
            $transactionId .= $newNumericPart;
        } else {
            $transactionId .= '00001';
        }

        return $transactionId;
    }

    private function getStatus($prodId="", $materialId="", $quantity=0){
        $prodOrder = ProductionOrder::where('po_id', $prodId)->first();
        $prodMaterial = Material::findOrFail($prodOrder->material_id);
        $required_quantity = $prodOrder->quantity;
        $bomRecords = $prodMaterial->bom?->bomRecords;
        if ($bomRecords) {
            foreach ($bomRecords as $bomRecord) {
                if ($bomRecord->material_id == $materialId) {
                    $required_qty = $bomRecord->quantity * $required_quantity;
                    $existingRecord = ProdOrdersMaterial::where('po_id', $prodId)
                        ->where('material_id', $materialId)
                        ->first();

                    if ($existingRecord) {
                        if ($existingRecord->quantity + $quantity == $required_qty) {
                            return 'Completed';
                        } else if ($existingRecord->quantity + $quantity < $required_qty) {
                            return 'Partial';
                        }
                    } else {
                        if ($quantity == $required_qty) {
                            return 'Completed';
                        } else if ($quantity < $required_qty) {
                            return 'Partial';
                        }
                    }
                }
            }
        }
    }

    private function updateProdOrderStatus($prodId=""){
        $prodOrder = ProductionOrder::where('po_id', $prodId)->first();
        $prodOrderMaterials = ProdOrdersMaterial::where('po_id', $prodId)->get();
        $overallStatus = 'Completed';

        foreach ($prodOrderMaterials as $material) {
            if ($material->status === 'Partial') {
                $overallStatus = 'Partially Issued';
                break;
            }
        }

        $prodOrder->status = $overallStatus;
        $prodOrder->save();

        return $overallStatus;
    }
}
