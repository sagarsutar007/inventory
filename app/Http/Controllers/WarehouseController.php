<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        if (Gate::allows('admin', Auth::user()) || Gate::allows('view-stock', Auth::user())) {
            return view('warehouse.list');
        } else {
            abort(403);
        }
    }

    public function transactions()
    {
        if (Gate::allows('admin', Auth::user()) || Gate::allows('view-transaction', Auth::user())) {
            return view('warehouse.transactions');
        } else {
            abort(403);
        }
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

        if ($columnName === 'part_code') {
            $query->join('materials', 'materials.material_id', '=', 'stocks.material_id')
                ->orderBy('materials.part_code', $columnSortOrder);
        } elseif ($columnName === 'description') {
            $query->join('materials', 'materials.material_id', '=', 'stocks.material_id')
                ->orderBy('materials.description', $columnSortOrder);
        } elseif ($columnName === 'uom_text') {
            $query->join('materials', 'materials.material_id', '=', 'stocks.material_id')
                ->join('uom_units', 'uom_units.uom_id', '=', 'materials.uom_id')
                ->orderBy('uom_units.uom_text', $columnSortOrder);
        } elseif ($columnName === 'category') {
            $query->join('materials', 'materials.material_id', '=', 'stocks.material_id')
                ->join('categories', 'categories.category_id', '=', 'materials.category_id')
                ->orderBy('categories.category_name', $columnSortOrder);
        } elseif ($columnName === 'commodity') {
            $query->join('materials', 'materials.material_id', '=', 'stocks.material_id')
                ->join('commodities', 'commodities.commodity_id', '=', 'materials.commodity_id')
                ->orderBy('commodities.commodity_name', $columnSortOrder);
        } elseif ($columnName === 'quantity') {
            $query->orderBy('quantity', $columnSortOrder);
        } elseif ($columnName === 're_order_status') {
            $query->join('materials', 'materials.material_id', '=', 'stocks.material_id')
                ->orderByRaw("CASE WHEN stocks.closing_balance < materials.re_order THEN 1 ELSE 0 END $columnSortOrder");
        } elseif ($columnName === 're_order') {
            $query->join('materials', 'materials.material_id', '=', 'stocks.material_id')
                ->orderBy('materials.re_order', $columnSortOrder);
        } else {
            $query->orderBy($columnName, $columnSortOrder);
        }

        $totalRecords = $query->count();
        // Paginate the query

        if ($length == -1) {
            $warehouses = $query->get();
        } else {
            $whQuery = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
            $warehouses = $whQuery->items();
        }

        $data = [];
        foreach ($warehouses as $index => $warehouse) {
            $material = $warehouse->material;
            if ($material) {

                if ($warehouse->closing_balance < $material->re_order && $material->re_order != null) {
                    $stockQty = "<span class='text-danger fw-bold'>" . $warehouse->closing_balance . "</span><div data-toggle='tooltip' data-placement='top' class='record-badge-reorder' title='Re Order'></div>";
                    $re_order_status = "<span class='text-danger fw-bold'>Yes</span>";
                } else {
                    $stockQty = $warehouse->closing_balance;
                    $re_order_status = "No";
                }

                $data[] = [
                    // 'sno' => $index + $start + 1,
                    'code' => $material->part_code,
                    'material_name' => $material->description,
                    'category' => $material->category->category_name,
                    'commodity' => $material->commodity->commodity_name,
                    'unit' => $material->uom->uom_shortcode,
                    'opening_balance' => $warehouse->opening_balance,
                    'receipt_qty' => $warehouse->receipt_qty,
                    'issue_qty' => $warehouse->issue_qty,
                    'closing_balance' => $stockQty,
                    're_order' => $material->re_order,
                    're_order_status' => $re_order_status,
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
            $query->leftJoin('vendors', 'vendors.vendor_id', '=', 'warehouse.vendor_id')
                ->where(function ($query) use ($search) {
                    $query->where('vendors.vendor_name', 'like', '%' . $search . '%');
                })
                ->orWhere('popn', 'like', '%' . $search . '%')
                ->orWhere('type', 'like', '%' . $search . '%')
                ->orWhere('reason', 'like', '%' . $search . '%')
                ->orWhere('transaction_id', 'like', '%' . $search . '%')
                ->orWhere('date', 'like', '%' . date('Y-m-d', strtotime($search)) . '%');
        }

        $totalRecords = $query->count();

        if ($columnName === 'vendor') {
            $query->join('vendors', 'vendors.vendor_id', '=', 'warehouse.vendor_id')
                ->orderBy('vendors.vendor_name', $columnSortOrder);
        } elseif ($columnName === 'popn') {
            $query->orderBy('popn', $columnSortOrder);
        } elseif ($columnName === 'type') {
            $query->orderBy('type', $columnSortOrder);
        } elseif ($columnName === 'reason') {
            $query->orderBy('reason', $columnSortOrder);
        } elseif ($columnName === 'transaction_id') {
            $query->orderBy('warehouse.created_at', $columnSortOrder);
        } elseif ($columnName === 'date') {
            $query->orderBy('date', $columnSortOrder);
        }

        // Paginate the query
        if ($length == -1) {
            $warehouses = $query->get();
        } else {
            $whQuery = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
            $warehouses = $whQuery->items();
        }
        $data = [];
        foreach ($warehouses as $index => $warehouse) {
            $actionHtml = '<a href="#" data-type="' . $warehouse->type . '" data-warehouseid="' . $warehouse->warehouse_id . '" data-transactionid="' . $warehouse->transaction_id . '" class="btn btn-sm btn-link" data-toggle="modal" data-target="#modalView"><i class="fas fa-eye" data-toggle="tooltip" data-placement="top" title="View"></i></a>';
            // $actionHtml = '<a href="#" data-type="' . $warehouse->type . '" data-warehouseid="' . $warehouse->warehouse_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalEdit"><i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit"></i></a>';
            if ($warehouse->type == 'reversal') {
                $type = "<span class='text-danger'>" . ucfirst($warehouse->type) . "</span>";
            } else {
                $type = ucfirst($warehouse->type);
            }

            $data[] = [
                // 'sno' => $index + $start + 1,
                'transaction_id' => $warehouse->transaction_id,
                'vendor' => $warehouse->vendor->vendor_name ?? 'Not Available',
                'popn' => $warehouse->popn,
                'reason' => $warehouse->reason,
                'type' => $type,
                'date' => date('d-m-Y', strtotime($warehouse->date)),
                'action' => $actionHtml,
            ];
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $data,
        ];

        return response()->json($response);
    }

    /**
     * Show the form for issuing the specified resource.
     */
    public function transIssue()
    {
        if (Gate::allows('admin', Auth::user()) || Gate::allows('issue-warehouse', Auth::user())) {
            $vendors = Vendor::all();
            return view('warehouse.issue', compact('vendors'));
        } else {
            abort(403);
        }
    }

    /**
     * Show the form for issuing the specified resource.
     */
    public function transReceive()
    {
        if (Gate::allows('admin', Auth::user()) || Gate::allows('receive-warehouse', Auth::user())) {
            $vendors = Vendor::all();
            return view('warehouse.receive', compact('vendors'));
        } else {
            abort(403);
        }
    }

    public function receiveMultiple(Request $request)
    {
        $validatedData = $request->validate([
            'vendor' => 'nullable|exists:vendors,vendor_id',
            'date' => 'required',
            'popn' => 'nullable',
            'part_code' => 'required|array',
            'part_code.*' => [
                'nullable',
                'exists:materials,part_code',
                function ($attribute, $value, $fail) {
                    // Check if the part code is required for index 0
                    if (key(request()->input('part_code', [])) === 0 && empty ($value)) {
                        $fail('The part code is required.');
                    }
                },
            ],
            'quantity' => 'required|array',
            'quantity.*' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) {
                    // Check if the quantity is less than or equal to 0 for indexes greater than 0
                    if (key(request()->input('quantity', [])) > 0 && ($value <= 0 || $value === '')) {
                        $fail('The quantity must be greater than 0 for indexes greater than 0.');
                    }
                },
                'min:0.001',
            ],
        ]);

        try {
            DB::beginTransaction();

            $warehouse = new Warehouse();
            $warehouse->vendor_id = $validatedData['vendor'];
            $warehouse->old_transaction_id = $this->generateTransactionId();
            $warehouse->type = 'receive';
            $warehouse->popn = $validatedData['popn'];
            $warehouse->date = date('y-m-d', strtotime($validatedData['date']));
            $warehouse->created_by = Auth::id();
            $warehouse->created_at = Carbon::now();
            $warehouse->save();

            foreach ($validatedData['part_code'] as $key => $materialId) {
                if (!empty($materialId)) {
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
                    $warehouseRecord->created_at = Carbon::now();
                    $warehouseRecord->record_date = $warehouse->date;
                    $warehouseRecord->save();
                }
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
            'vendor' => 'nullable|exists:vendors,vendor_id',
            'date' => 'required',
            'reason' => 'required',
            'part_code' => 'required|array',
            'part_code.*' => 'required|exists:materials,part_code',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric|min:0.001',
        ]);

        try {
            DB::beginTransaction();

            $warehouse = new Warehouse();
            $warehouse->vendor_id = $validatedData['vendor'];
            $warehouse->old_transaction_id = $this->generateTransactionId();
            // $warehouse->popn = $validatedData['popn'];
            $warehouse->reason = $validatedData['reason'];
            $warehouse->type = 'issue';
            $warehouse->date = date('Y-m-d', strtotime($validatedData['date']));
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
                $warehouseRecord->created_at = Carbon::now();
                $warehouseRecord->record_date = $warehouse->date;
                $warehouseRecord->save();
            }

            DB::commit();

            return response()->json(['status' => true, 'message' => "Material Issued Successfully!"], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'error' => $e->getMessage()], 500);
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

    public function viewTransaction(Warehouse $warehouse)
    {
        // Get all records of this warehouse issue
        $records = WarehouseRecord::where('warehouse_id', '=', $warehouse->warehouse_id)->get();

        if ($warehouse->type == "issue") {
            $type = ($warehouse->po_kitting == 'true') ? 'Material Issue Voucher <span class="text-danger">(Kitting)</span>' : 'Material Issue Voucher <span class="text-danger">(Manual)</span>';
        } else if ($warehouse->type == "reversal") {
            $type = 'Material Reciept Voucher <span class="text-danger">(Reversal)</span>';
        } else {
            $type = 'Material Reciept Voucher';
        }

        $wh = Warehouse::with('production')->first();

        $finishedGood = $warehouse->production?->material->description;

        $quantity = $warehouse->production?->quantity . " " . $warehouse->production?->material?->uom?->uom_shortcode;

        $context = [
            'title' => $type,
            'warehouse' => $warehouse,
            'records' => $records
        ];
        $returnHTML = view('warehouse.viewModalForm', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML, 'quantity' => $quantity ?? '', 'material' => $finishedGood ?? ''));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $validatedData = $request->validate([
            'vendor' => 'required|exists:vendors,vendor_id',
            'date' => 'required',
            'popn' => ($warehouse->type == 'issue') ? 'nullable' : 'required',
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

            $existingRecords = WarehouseRecord::where('warehouse_id', $warehouse->warehouse_id)->get();
            $existingPartCodes = $existingRecords->map(function ($record) {
                return $record->material->part_code;
            })->toArray();

            $partCodesToRemove = array_diff($existingPartCodes, $validatedData['part_code']);

            foreach ($partCodesToRemove as $partCodeToRemove) {
                $recordToDelete = WarehouseRecord::where('warehouse_id', $warehouse->warehouse_id)
                    ->whereHas('material', function ($query) use ($partCodeToRemove) {
                        $query->where('part_code', $partCodeToRemove);
                    })
                    ->first();

                if ($recordToDelete) {
                    $stock = Stock::where('material_id', $recordToDelete->material_id)->first();
                    if ($stock) {
                        if ($warehouse->type == 'issue') {
                            $stock->issue_qty -= $recordToDelete->quantity;
                        } else {
                            $stock->receipt_qty -= $recordToDelete->quantity;
                        }
                        $stock->save();
                    }
                    $recordToDelete->delete();
                }
            }

            if ($warehouse->type == 'issue') {
                $warehouse_type = 'issued';
            } else {
                $warehouse_type = 'received';
            }

            foreach ($validatedData['part_code'] as $key => $materialId) {
                $material = Material::where('part_code', $materialId)->first();

                $warehouseRecord = WarehouseRecord::where('warehouse_id', $warehouse->warehouse_id)
                    ->where('material_id', $material->material_id)
                    ->first();
                if ($warehouseRecord) {
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
                    $warehouseRecord->record_date = $warehouse->date;
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
                    $stock = new Stock();
                    $stock->material_id = $material->material_id;
                    $stock->issue_qty = 0;
                    $stock->receipt_qty = 0;
                    $stock->opening_balance = 0;
                    if ($warehouse->type == 'issue') {
                        $stock->issue_qty = $validatedData['quantity'][$key];
                    } else {
                        $stock->receipt_qty = $validatedData['quantity'][$key];
                    }
                    $stock->created_by = Auth::id();
                    $stock->save();
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
        $year = date('y');
        $weekNumber = date('W');
        $date = date('d');
        $transactionId = sprintf('%02d%02d%02d', $year, $weekNumber, $date);
        $lastTransactionId = Warehouse::where('transaction_id', 'like', $transactionId . '%')->orderBy('transaction_id', 'desc')->first();
        if ($lastTransactionId) {
            $lastNumericPart = intval(substr($lastTransactionId->transaction_id, 6));
            $newNumericPart = str_pad($lastNumericPart + 1, 5, '0', STR_PAD_LEFT);
            $transactionId .= $newNumericPart;
        } else {
            $transactionId .= '00001';
        }

        return $transactionId;
    }

    public function getMaterials(Request $request)
    {
        $term = $request->input('term');
        $existingPartCodes = $request->input('existingPartCodes', []);

        $existingPartCodes = array_filter($existingPartCodes, function ($value) {
            return $value !== null;
        });

        $materials = Material::with('uom', 'stock')
            ->whereNotIn('part_code', $existingPartCodes)
            ->where(function ($query) use ($term) {
                $query->where('part_code', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%');
            })
            ->limit(20)
            ->orderBy('part_code', 'asc')
            ->get();

        $formattedMaterials = $materials->map(function ($material) {
            return [
                'value' => $material->part_code,
                'unit' => $material->uom->uom_shortcode,
                'closing_balance' => $material->stock?->closing_balance ?? 0,
                'desc' => $material->description,
            ];
        });

        return response()->json($formattedMaterials);
    }
}
