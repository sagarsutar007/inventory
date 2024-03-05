<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Material;
use App\Models\BomRecord;
use App\Models\Stock;

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
        $partCodes = $request->input('part_code', []);
        $quantities = $request->input('quantity', []);
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

        $context = [
            'bomRecords' => $bomRecords,
        ];

        $returnHTML = view('production-order.viewBomTable', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
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
}
