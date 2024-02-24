<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Material;
use App\Models\Warehouse;
use App\Models\WarehouseRecord;
use App\Models\Stock;
use App\Models\Vendor;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('warehouse.list');
    }

    public function transactions()
    {
        return view('warehouse.transactions');
    }

    public function fetchRecords(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $query = Stock::with('material');

        if (!empty($search)) {
            $query->whereHas('material', function ($q) use ($search) {
                $q->where('part_code', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('uom', function ($u) use ($search) {
                        $u->where('uom_text', 'like', '%' . $search . '%');
                    });
            });
        }

        $totalRecords = $query->count();

        if ($columnName === 'part_code') {
            $query->join('materials', 'materials.material_id', '=', 'stocks.material_id')
                ->orderBy('materials.part_code', $columnSortOrder);
        } elseif ($columnName === 'description') {
            $query->join('materials', 'materials.material_id', '=', 'stocks.material_id')
                ->orderBy('materials.description', $columnSortOrder);
        } elseif ($columnName === 'uom_text') {
            $query->join('materials', 'materials.material_id', '=', 'stocks.material_id')
                ->join('uom_units', 'uom_units.id', '=', 'materials.uom_id')
                ->orderBy('uom_units.uom_text', $columnSortOrder);
        } elseif ($columnName === 'quantity') {
            $query->orderBy('quantity', $columnSortOrder);
        } else {
            // $query->orderBy($columnName, $columnSortOrder);
        }

        // Paginate the query
        $whQuery = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
        $warehouses = $whQuery->items();
        $data = [];
        foreach ($warehouses as $index => $warehouse) {
            $material = $warehouse->material;
            if ($material) {
                $actionHtml = '<a href="#" data-warehouseid="' . $warehouse->stock_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalView"><i class="fas fa-eye" data-toggle="tooltip" data-placement="top" title="View"></i></a> / ' .
                    '<a href="#" data-warehouseid="' . $warehouse->stock_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalView"><i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit"></i></a> / ' .
                    '<form action="' . route("wh.destroy", ["warehouse" => $warehouse->stock_id]) . '" method="post" style="display: inline;">' .
                    csrf_field() .
                    method_field('DELETE') .
                    '<button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm(\'Are you sure you want to delete this material?\')"><i class="fas fa-trash" data-toggle="tooltip" data-placement="top" title="Delete"></i></button>' .
                    '</form>';

                $data[] = [
                    // 'sno' => $index + $start + 1,
                    'code' => $material->part_code,
                    'material_name' => $material->description,
                    'unit' => $material->uom->uom_text,
                    'opening_balance' => $warehouse->opening_balance,
                    'receipt_qty' => $warehouse->receipt_qty,
                    'issue_qty' => $warehouse->issue_qty,
                    'closing_balance' => $warehouse->closing_balance,
                    // 'action' => $actionHtml,
                ];
            }
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $whQuery->total(),
            "data" => $data,
        ];

        return response()->json($response);
    }
    public function fetchTransactions(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $query = Warehouse::with('vendor');

        if (!empty($search)) {
            // $query->whereHas('material', function ($q) use ($search) {
            //     $q->where('part_code', 'like', '%' . $search . '%')
            //         ->orWhere('description', 'like', '%' . $search . '%')
            //         ->orWhereHas('uom', function ($u) use ($search) {
            //             $u->where('uom_text', 'like', '%' . $search . '%');
            //         });
            // });
        }

        $totalRecords = $query->count();

        if ($columnName === 'part_code') {
            $query->join('materials', 'materials.material_id', '=', 'warehouse.material_id')
                ->orderBy('materials.part_code', $columnSortOrder);
        } elseif ($columnName === 'description') {
            $query->join('materials', 'materials.material_id', '=', 'warehouse.material_id')
                ->orderBy('materials.description', $columnSortOrder);
        } elseif ($columnName === 'uom_text') {
            $query->join('materials', 'materials.material_id', '=', 'warehouse.material_id')
                ->join('uom_units', 'uom_units.id', '=', 'materials.uom_id')
                ->orderBy('uom_units.uom_text', $columnSortOrder);
        } elseif ($columnName === 'quantity') {
            $query->orderBy('quantity', $columnSortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Paginate the query
        $whQuery = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
        $warehouses = $whQuery->items();
        $data = [];
        foreach ($warehouses as $index => $warehouse) {
            $actionHtml = '<a href="#" data-type="' . $warehouse->type . '" data-warehouseid="' . $warehouse->warehouse_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalView"><i class="fas fa-eye" data-toggle="tooltip" data-placement="top" title="View"></i></a> / ' .
                '<a href="#" data-type="' . $warehouse->type . '" data-warehouseid="' . $warehouse->warehouse_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalEdit"><i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit"></i></a> / ' .
                '<form action="' . route("wh.destroy", ["warehouse" => $warehouse->warehouse_id]) . '" method="post" style="display: inline;">' .
                csrf_field() .
                method_field('DELETE') .
                '<button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm(\'Are you sure you want to delete this material?\')"><i class="fas fa-trash" data-toggle="tooltip" data-placement="top" title="Delete"></i></button>' .
                '</form>';

            $data[] = [
                // 'sno' => $index + $start + 1,
                'transaction_id' => $warehouse->transaction_id,
                'vendor' => $warehouse->vendor->vendor_name ?? 'Not Available',
                'popn' => $warehouse->popn,
                'type' => ucfirst($warehouse->type),
                'action' => $actionHtml,
            ];
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $whQuery->total(),
            "data" => $data,
        ];

        return response()->json($response);
    }

    /**
     * Show the form for issuing the specified resource.
     */
    public function transIssue()
    {
        $vendors = Vendor::all();
        return view('warehouse.issue', compact('vendors'));
    }

    /**
     * Show the form for issuing the specified resource.
     */
    public function transReceive()
    {
        $vendors = Vendor::all();
        return view('warehouse.receive', compact('vendors'));
    }

    public function receiveMultiple(Request $request)
    {
        $validatedData = $request->validate([
            'vendor' => 'required|exists:vendors,vendor_id',
            'date' => 'required',
            'popn' => 'required',
            'part_code' => 'required|array',
            'part_code.*' => 'required|exists:materials,part_code',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric|min:0.001',
        ]);


        try {
            DB::beginTransaction();

            $warehouse = new Warehouse();
            $warehouse->vendor_id = $validatedData['vendor'];
            $warehouse->transaction_id = $this->generateTransactionId();
            $warehouse->type = 'receive';
            $warehouse->popn = $validatedData['popn'];
            $warehouse->date = date('y-m-d', strtotime($validatedData['date']));
            $warehouse->created_by = Auth::id();
            $warehouse->created_at = Carbon::now();
            $warehouse->save();

            foreach ($validatedData['part_code'] as $key => $materialId) {
                $material = Material::where('part_code', $materialId)->first();
                $stock = Stock::where('material_id', $material->material_id)->first();
                if ($stock) {
                    $stock->receipt_qty += $validatedData['quantity'][$key];
                    $stock->created_by = Auth::id();
                    $stock->save();
                } else {
                    $newStock = new Stock();
                    $newStock->material_id = $material->material_id;
                    $newStock->receipt_qty = $validatedData['quantity'][$key];
                    $newStock->issue_qty = 0;
                    $newStock->opening_balance = 0;
                    $newStock->created_by = Auth::id();
                    $newStock->save();
                }

                $warehouseRecord = new WarehouseRecord();
                $warehouseRecord->warehouse_id = $warehouse->warehouse_id;
                $warehouseRecord->material_id = $material->material_id;
                $warehouseRecord->warehouse_type = 'received';
                $warehouseRecord->quantity = $validatedData['quantity'][$key];
                $warehouseRecord->created_by = Auth::id();
                $warehouseRecord->save();
            }

            DB::commit();

            return response()->json(['status' => true, 'message' => "Material Received Successfully!"], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Something went wrong!', 'error' => $e->getMessage()], 500);
        }
    }

    public function issueMultiple(Request $request)
    {
        $validatedData = $request->validate([
            'vendor' => 'required|exists:vendors,vendor_id',
            'date' => 'required',
            'popn' => 'required',
            'part_code' => 'required|array',
            'part_code.*' => 'required|exists:materials,part_code',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric|min:0.001',
        ]);


        try {
            DB::beginTransaction();

            $warehouse = new Warehouse();
            $warehouse->vendor_id = $validatedData['vendor'];
            $warehouse->transaction_id = $this->generateTransactionId();
            $warehouse->popn = $validatedData['popn'];
            $warehouse->type = 'issue';
            $warehouse->date = date('y-m-d', strtotime($validatedData['date']));
            $warehouse->created_by = Auth::id();
            $warehouse->created_at = Carbon::now();
            $warehouse->save();

            foreach ($validatedData['part_code'] as $key => $materialId) {
                $material = Material::where('part_code', $materialId)->first();
                $stock = Stock::where('material_id', $material->material_id)->first();
                if ($stock) {
                    if ($stock->closing_balance >= $validatedData['quantity'][$key]) {
                        $stock->issue_qty += $validatedData['quantity'][$key];
                        $stock->created_by = Auth::id();
                        $stock->save();
                    } else {
                        throw new \Exception('Insufficient quantity available for material with partcode ' . $material->part_code);
                    }
                } else {
                    throw new \Exception('Material record with description ' . $material->description . ' is not available in warehouse');
                }

                $warehouseRecord = new WarehouseRecord();
                $warehouseRecord->warehouse_id = $warehouse->warehouse_id;
                $warehouseRecord->material_id = $material->material_id;
                $warehouseRecord->warehouse_type = 'issued';
                $warehouseRecord->quantity = $validatedData['quantity'][$key];
                $warehouseRecord->created_by = Auth::id();
                $warehouseRecord->save();
            }

            DB::commit();

            return response()->json(['status' => true, 'message' => "Material Issued Successfully!"], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Something went wrong!', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editReceipt(Warehouse $warehouse)
    {
        $vendors = Vendor::all();
        $records = WarehouseRecord::where('warehouse_id', '=', $warehouse->warehouse_id)->get();
        $context = [
            'vendors' => $vendors,
            'warehouse' => $warehouse,
            'records' => $records
        ];
        $returnHTML = view('warehouse.editReceipt', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }

    public function editIssue(Warehouse $warehouse)
    {
        $vendors = Vendor::all();
        $records = WarehouseRecord::with('material')->where('warehouse_id', '=', $warehouse->warehouse_id)->get();
        $context = [
            'vendors' => $vendors,
            'warehouse' => $warehouse,
            'records' => $records
        ];
        $returnHTML = view('warehouse.editIssue', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $validatedData = $request->validate([
            'vendor' => 'required|exists:vendors,vendor_id',
            'date' => 'required',
            'popn' => 'required',
            'part_code' => 'required|array',
            'part_code.*' => 'required|exists:materials,part_code',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric|min:0.001',
        ]);


        try {
            DB::beginTransaction();

            $warehouse->vendor_id = $validatedData['vendor'];
            $warehouse->popn = $validatedData['popn'];
            $warehouse->date = date('y-m-d', strtotime($validatedData['date']));
            $warehouse->updated_by = Auth::id();
            $warehouse->updated_at = Carbon::now();
            $warehouse->save();

            foreach ($validatedData['part_code'] as $key => $materialId) {
                $material = Material::where('part_code', $materialId)->first();
                // Check if a warehouse record already exists for the given warehouse and material
                $warehouseRecord = WarehouseRecord::where('warehouse_id', $warehouse->warehouse_id)
                    ->where('material_id', $material->material_id)
                    ->first();

                if ($warehouseRecord) {
                    if ($warehouse->type == 'issue') {
                        $warehouse_type = 'issued';
                    } else {
                        $warehouse_type = 'received';
                    }

                    $prevQty = $warehouseRecord->quantity;

                    $warehouseRecord->quantity = $validatedData['quantity'][$key];
                    $warehouseRecord->updated_by = Auth::id();
                    $warehouseRecord->save();


                } else {
                    // Create a new warehouse record
                    $prevQty = 0;
                    $warehouseRecord = new WarehouseRecord();
                    $warehouseRecord->warehouse_id = $warehouse->warehouse_id;
                    $warehouseRecord->material_id = $material->material_id;
                    $warehouseRecord->warehouse_type = $warehouse_type;
                    $warehouseRecord->quantity = $validatedData['quantity'][$key];
                    $warehouseRecord->created_by = Auth::id();
                    $warehouseRecord->save();
                }

                $stock = Stock::where('material_id', $warehouseRecord->material_id)->first();
                if ($stock) {
                    if ($warehouse->type == 'issue') {
                        if ($prevQty > $validatedData['quantity'][$key]) {
                            $diff = $prevQty - $validatedData['quantity'][$key];
                            $stock->issue_qty += $diff;
                        } else if ($prevQty < $validatedData['quantity'][$key]) {
                            $diff = $validatedData['quantity'][$key] - $prevQty;
                            $stock->issue_qty += $diff;
                        } else {
                        }

                    } else {
                        if ($prevQty > $validatedData['quantity'][$key]) {
                            $diff = $prevQty - $validatedData['quantity'][$key];
                            $stock->receipt_qty -= $diff;
                        } else if ($prevQty < $validatedData['quantity'][$key]) {
                            $diff = $validatedData['quantity'][$key] - $prevQty;
                            $stock->receipt_qty += $diff;
                        } else {
                        }
                    }

                    $stock->updated_by = Auth::id();
                    $stock->save();
                } else {
                    //add new record
                }
            }

            DB::commit();

            return response()->json(['status' => true, 'message' => "Warehouse Updated Successfully!"], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Something went wrong!', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        //
    }

    public function generateTransactionId()
    {
        $lastTransactionId = Warehouse::max('transaction_id');
        $newTransactionId = $lastTransactionId + 1;
        if ($newTransactionId == 0) {
            $newTransactionId = 1;
        } elseif ($newTransactionId < 1000000000) {
            $newTransactionId = str_pad($newTransactionId, 10, '0', STR_PAD_LEFT);
        }
        return $newTransactionId;
    }

    public function getMaterials(Request $request)
    {
        $term = $request->input('term');
        $materials = Material::where('part_code', 'like', '%' . $term . '%')
            ->orderBy('created_at', 'asc')
            ->pluck('part_code');
        return response()->json($materials);
    }
}
