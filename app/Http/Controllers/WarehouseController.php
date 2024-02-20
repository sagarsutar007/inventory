<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Material;
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

                $actionHtml = '<a href="#" role="button" data-warehouseid="' . $warehouse->warehouse_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalView"><i class="fas fa-eye" data-toggle="tooltip" data-placement="top" title="View"></i></a> / ' .
                    '<form action="' . route("wh.destroy", ["warehouse" => $warehouse->warehouse_id]) . '" method="post" style="display: inline;">' .
                    csrf_field() .
                    method_field('DELETE') .
                    '<button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm(\'Are you sure you want to delete this material?\')"><i class="fas fa-trash" data-toggle="tooltip" data-placement="top" title="Delete"></i></button>' .
                    '</form>';

                $data[] = [
                    // 'sno' => $index + $start + 1,
                    'code' => $material->part_code,
                    'material_name' => $material->description,
                    'quantity' => $warehouse->quantity,
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
     * Show the form for issuing the specified resource.
     */
    public function transIssue()
    {
        return view('warehouse.issue');
    }

    /**
     * Show the form for issuing the specified resource.
     */
    public function transReceive()
    {
        return view('warehouse.receive');
    }

    public function receiveMultiple(Request $request)
    {
        $validatedData = $request->validate([
            'material_id' => 'required|array',
            'material_id.*' => 'required|exists:materials,material_id',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric|min:0.001',
        ]);

        $trans_code = $this->generateTransactionId();

        foreach ($validatedData['material_id'] as $key => $materialId) {
            $warehouse = Warehouse::firstOrNew(['material_id' => $materialId]);
            $warehouse->quantity += $validatedData['quantity'][$key];
            $warehouse->transaction_id = $trans_code;
            $warehouse->created_by = Auth::id();
            $warehouse->created_at = Carbon::now();
            $warehouse->save();

            $warehouseRecord = new WarehouseRecord();
            $warehouseRecord->warehouse_id = $warehouse->warehouse_id;
            $warehouseRecord->warehouse_type = 'received';
            $warehouseRecord->quantity = $validatedData['quantity'][$key];
            $warehouseRecord->created_by = Auth::id();
            $warehouseRecord->save();
        }

        return redirect()->route('wh')->with('success', 'Material received successfully.');
    }

    public function issueMultiple(Request $request)
    {
        $validatedData = $request->validate([
            'material_id' => 'required|array',
            'material_id.*' => 'required|exists:materials,material_id',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric|min:0.001',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validatedData['material_id'] as $key => $materialId) {
                $existingWarehouse = Warehouse::where('material_id', $materialId)->first();

                if ($existingWarehouse) {
                    if ($existingWarehouse->quantity >= $validatedData['quantity'][$key]) {
                        $existingWarehouse->quantity -= $validatedData['quantity'][$key];
                        $existingWarehouse->save();
                    } else {
                        $material = Material::find($materialId);
                        throw new \Exception('Insufficient quantity available for material with partcode ' . $material->part_code);
                    }
                } else {
                    throw new \Exception('Material record with ID ' . $materialId . ' is not available');
                }

                $warehouseRecord = new WarehouseRecord();
                $warehouseRecord->warehouse_id = $existingWarehouse->warehouse_id;
                $warehouseRecord->warehouse_type = 'issued';
                $warehouseRecord->quantity = $validatedData['quantity'][$key];
                $warehouseRecord->created_by = Auth::id();
                $warehouseRecord->save();
            }

            DB::commit();

            return response()->json(['status' => true, 'message' => "Material Issued Successfully!"], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
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
        $searchTerm = $request->input('q');

        if (empty($searchTerm)) {
            $materials = Warehouse::with('material')->orderBy('created_at', 'desc')->limit(10)->get();
        } else {
            $materials = Warehouse::with('material')
                ->whereHas('material', function ($query) use ($searchTerm) {
                    $query->where('description', 'like', '%' . $searchTerm . '%')
                        ->orWhere('part_code', 'like', '%' . $searchTerm . '%');
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        $mappedMaterials = $materials->map(function ($item) {
            return [
                'material_id' => $item->material_id,
                'description' => $item->material->description,
                'part_code' => $item->material->part_code,
            ];
        });

        return response()->json($mappedMaterials);
    }
}
