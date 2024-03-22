<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Bom;
use App\Models\BomRecord;

class BOMController extends Controller
{
    public function index()
    {
        return view('bom.bill-of-material');
    }
    public function getBom(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];


        $query = Bom::with(['bomRecords.material']);


        if (!empty ($search)) {
            $query->whereHas('material', function ($q) use ($search) {
                $q->where('part_code', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('commodity', function ($cm) use ($search) {
                        $cm->where('commodity_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('category', function ($ct) use ($search) {
                        $ct->where('category_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('uom', function ($u) use ($search) {
                        $u->where('uom_text', 'like', '%' . $search . '%');
                    });
            });
        }


        $totalRecords = $query->count();


        if ($columnName === 'part_code') {
            $query->join('materials', 'materials.material_id', '=', 'boms.material_id')
                ->orderBy('materials.part_code', $columnSortOrder);
        } elseif ($columnName === 'description') {
            $query->join('materials', 'materials.material_id', '=', 'boms.material_id')
                ->orderBy('materials.description', $columnSortOrder);
        } elseif ($columnName === 'uom_text') {
            $query->join('materials', 'materials.material_id', '=', 'boms.material_id')
                ->join('uom_units', 'uom_units.id', '=', 'materials.uom_id')
                ->orderBy('uom_units.uom_text', $columnSortOrder);
        } elseif ($columnName === 'commodity_name') {
            $query->join('materials', 'materials.material_id', '=', 'boms.material_id')
                ->join('commodities', 'commodities.id', '=', 'materials.commodity_id')
                ->orderBy('commodities.commodity_name', $columnSortOrder);
        } elseif ($columnName === 'category_name') {
            $query->join('materials', 'materials.material_id', '=', 'boms.material_id')
                ->join('categories', 'categories.id', '=', 'materials.category_id')
                ->orderBy('categories.category_name', $columnSortOrder);
        } else {
            // $query->orderBy($columnName, $columnSortOrder);
        }

        // Paginate the query
        $bomsQuery = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));

        $boms = $bomsQuery->items();
        $data = [];

        foreach ($boms as $index => $bom) {
            $material = $bom->material;
            if ($material) {

                $actionHtml = '<a href="#" role="button" data-partcode="' . $material->part_code . '" data-desc="' . $material->description . '" data-bomid="' . $bom->bom_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalView"><i class="fas fa-eye" data-toggle="tooltip" data-placement="top" title="View Material"></i></a> / ' .
                    '<a href="#" role="button" data-partcode="' . $material->part_code . '" data-desc="' . $material->description . '" data-bomid="' . $bom->bom_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalEdit"><i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit"></i></a> / ' .
                    '<form action="' . route("bom.destroy", ["bom" => $bom->bom_id]) . '" method="post" style="display: inline;">' .
                    csrf_field() .
                    method_field('DELETE') .
                    '<button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm(\'Are you sure you want to delete this material?\')"><i class="fas fa-trash" data-toggle="tooltip" data-placement="top" title="Delete"></i></button>' .
                    '</form> / ' .
                    '<button role="button" data-desc="' . $material->description . '" data-matid="' . $material->material_id . '" class="btn btn-sm btn-link text-success p-0 btn-export-bom"><i class="fas fa-file-excel" data-toggle="tooltip" data-placement="top" title="Export BOM"></i></button> / ' .
                    '<button role="button" data-desc="' . $material->description . '" data-matid="' . $material->material_id . '" class="btn btn-sm btn-link text-warning p-0 btn-import-bom"><i class="fas fa-file-import" data-toggle="tooltip" data-placement="top" title="Import BOM"></i></i></button>';


                $data[] = [
                    // 'sno' => $index + $start + 1,
                    'code' => $material->part_code,
                    'material_name' => $material->description,
                    'unit' => $material->uom->uom_text,
                    'commodity' => $material->commodity->commodity_name,
                    'category' => $material->category->category_name,
                    'action' => $actionHtml,
                ];
            }
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $bomsQuery->total(),
            "data" => $data,
        ];

        return response()->json($response);
    }
    public function show(Bom $bom)
    {

        $materialWithBomRecords = Bom::with(['bomRecords'])->find($bom->bom_id);
        if ($materialWithBomRecords->bomRecords) {
            $bomRecords = $materialWithBomRecords->bomRecords;
        } else {
            $bomRecords = null;
        }
        $context = [
            'boms' => $bomRecords,
        ];

        $returnHTML = view('bom.view-finished-material', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }

    public function edit(Bom $bom)
    {
        $context = [
            'bom' => $bom,
        ];

        $returnHTML = view('bom.edit-bom-material', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bom $bom)
    {
        // try {
        //     $validatedData = $request->validate([
        //         'raw' => 'required|array',
        //         'raw.*' => 'required|string',
        //         'quantity' => 'required|array',
        //         'quantity.*' => 'required|numeric',
        //     ]);
        // } catch (ValidationException $e) {
        //     return response()->json(['status' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        // }

        try {
            DB::beginTransaction();

            // Update or insert BOM records
            if ($request->has('raw') && $request->has('quantity')) {
                $rawMaterials = $request->input('raw');
                $quantities = $request->input('quantity');

                if (count($rawMaterials) === count($quantities)) {
                    foreach ($rawMaterials as $index => $rawMaterialId) {
                        if (!empty ($rawMaterialId)) {
                            $bomRecord = BomRecord::where('material_id', $rawMaterialId)
                                ->where('bom_id', $bom->bom_id)
                                ->first();

                            if ($bomRecord) {
                                $bomRecord->quantity = $quantities[$index];
                                $bomRecord->save();
                            } else {
                                $bom->bomRecords()->create([
                                    'material_id' => $rawMaterialId,
                                    'bom_id' => $bom->bom_id,
                                    'quantity' => $quantities[$index],
                                ]);
                            }
                        }
                    }

                    //fetch all bom records and those not in the request should be deleted.
                    $deleteBoms = BomRecord::where('bom_id', $bom->bom_id)->whereNotIn(
                        'material_id',
                        $rawMaterials
                    )->get();
                    foreach ($deleteBoms as $delBom) {
                        $delBom->delete();
                    }
                } else {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'Materials and quantities count mismatch'], 400);
                }
            } else {
                $bom->bomRecords()->delete();
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'BOM updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'message' => 'Failed to update BOM. ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Bom $bom)
    {
        $bom->delete();
        return redirect()->back()->with('success', 'BOM deleted successfully');
    }
}
