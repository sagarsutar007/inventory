<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Warehouse;
use App\Models\WarehouseRecord;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('warehouse.list');
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
        
        $query = Warehouse::with(['material']);

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
            // $query->orderBy($columnName, $columnSortOrder);
        }

        // Paginate the query
        $whQuery = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));

        $warehouses = $whQuery->items();
        $data = [];

        foreach ($warehouses as $index => $warehouse) {
            $material = $warehouse->material;
            if ($material) {

                $actionHtml = '<a href="#" role="button" data-warehouseid="' . $warehouse->warehouse_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalView"><i class="fas fa-eye" data-toggle="tooltip" data-placement="top" title="View Material"></i></a> / ' .
                    '<a href="#" role="button" data-warehouseid="' . $warehouse->warehouse_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalEdit"><i class="fas fa-edit"></i></a> / ' .
                    '<form action="' . route("wh.destroy", ["warehouse" => $warehouse->warehouse_id]) . '" method="post" style="display: inline;">' .
                    csrf_field() .
                    method_field('DELETE') .
                    '<button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm(\'Are you sure you want to delete this material?\')"><i class="fas fa-trash"></i></button>' .
                    '</form>';

                $data[] = [
                    'sno' => $index + $start + 1,
                    'code' => $material->part_code,
                    'material_name' => $material->description,
                    'quantity' => $material->quantity,
                    'unit' => $material->uom->uom_text,
                    'action' => $actionHtml,
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Add issue record in warehouse records
     */
    public function issue(Request $request)
    {
        $validatedData = $request->validate([
            'material_id' => 'required',
            'quantity' => 'required|numeric|min:0.001',
        ]);
        
        $existingWarehouse = Warehouse::where('material_id', $validatedData['material_id'])->first();

        if ($existingWarehouse) {
            if ($existingWarehouse->quantity >= $validatedData['quantity']) {
                $existingWarehouse->quantity -= $validatedData['quantity'];
                $existingWarehouse->updated_by = Auth::id();
                $existingWarehouse->save();
            } else {
                return response()->json(['status' => false, 'message' => 'Insufficient quantity available']);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Material record is not available']);
        }

        $warehouseRecord = new WarehouseRecord();
        $warehouseRecord->warehouse_id = $existingWarehouse->warehouse_id;
        $warehouseRecord->warehouse_type = 'issued';
        $warehouseRecord->quantity = $validatedData['quantity'];
        $warehouseRecord->save();

        return response()->json(['status' => true, 'message' => 'Material issued successfully']);
    }


    /**
     * Add receive record in warehouse records
     */
    public function receive(Request $request)
    {
        $validatedData = $request->validate([
            'material_id' => 'required',
            'quantity' => 'required|numeric|min:0.001',
        ]);
        
        $existingWarehouse = Warehouse::where('material_id', $validatedData['material_id'])->first();

        if ($existingWarehouse) {
            $existingWarehouse->quantity += $validatedData['quantity'];
            $existingWarehouse->updated_by = Auth::id();
            $existingWarehouse->save();
        } else {
            $warehouse = new Warehouse();
            $warehouse->material_id = $validatedData['material_id'];
            $warehouse->quantity = $validatedData['quantity']; 
            $warehouse->created_by = Auth::id();
            $warehouse->save();
        }

        // Create a new record in the warehouse_records table
        $warehouseRecord = new WarehouseRecord();
        $warehouseRecord->warehouse_id = $existingWarehouse ? $existingWarehouse->warehouse_id : $warehouse->warehouse_id;
        $warehouseRecord->warehouse_type = 'received';
        $warehouseRecord->quantity = $validatedData['quantity'];
        $warehouseRecord->save();

        return response()->json(['status' => true, 'message' => 'Material received successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        //
    }
}
