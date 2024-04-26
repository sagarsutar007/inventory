<?php

namespace App\Http\Controllers;

use App\Exports\BomRecordExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Material;
use App\Models\WarehouseRecord;
use App\Imports\ExcelImportClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MaterialController extends Controller
{

    public function exportBomRecords($materialId)
    {
        $material = Material::findOrFail($materialId);
        $partCode = str_replace(['/', '\\'], ['_', '_'], $material->part_code);
        $description = str_replace(['/', '\\'], ['_', '_'], $material->description);
        $fileName = $partCode . '_' . $description . '_BOM.xlsx';

        $export = new BomRecordExport($materialId, $material->description, $material->uom->uom_text);

        try {
            $tempFilePath = 'exports/' . $fileName;
            Excel::store($export, $tempFilePath);
            $publicFilePath = public_path('storage/exports/' . $fileName);
            rename(storage_path('app/' . $tempFilePath), $publicFilePath);
            $downloadUrl = asset('storage/exports/' . $fileName);

            Log::info('File stored successfully: ' . $publicFilePath);

            return response()->json(['downloadUrl' => $downloadUrl, 'filePath' => $publicFilePath]);
        } catch (\Exception $e) {
            Log::error('Error storing file: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to export BOM records.']);
        }
    }

    public function importBomRecords(Request $request, Material $material)
    {
        try {
            if ($request->hasFile('file')) {

                $request->validate([
                    'file' => 'required|mimes:xlsx,xls',
                    'material_id' => 'required|string'
                ]);

                $file = $request->file('file');
                $exData = [
                    'material_id' => $request->input('material_id')
                ];
                DB::beginTransaction();
                $import = new ExcelImportClass('bom', Auth::id(), $exData);
                Excel::import($import, $file);
                DB::commit();

                // $importedRows = $import->getImportedCount();

                return response()->json(['status' => true, 'message' => 'BOM records imported successfully.']);
            } else {
                return response()->json(['status' => false, 'message' => ['No file uploaded.']]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessages = explode("\n", $e->getMessage());

            // Log::error('Error importing file: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => $errorMessages]);
        }
    }

    public function getMaterials(Request $request)
    {
        $searchTerm = $request->input('q');

        if (empty($searchTerm)) {
            $materials = Material::with('uom')
                ->select('material_id', 'description', 'part_code')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        } else {
            $materials = Material::with('uom')
                ->select('material_id', 'description', 'part_code')
                ->where('description', 'like', '%' . $searchTerm . '%')
                ->orWhere('part_code', 'like', '%' . $searchTerm . '%')
                ->orderBy('description')
                ->get();
        }
        return response()->json($materials);
    }

    public function getMaterialDetails(Request $request)
    {
        $searchTerm = $request->input('part_code');

        $material = Material::with('uom', 'stock')
            ->where('part_code', '=', $searchTerm)
            ->first();

        if ($material) {
            return response()->json(['success' => true, 'data' => $material]);
        } else {
            \Log::error('Material not found for part code: ' . $searchTerm);
            return response()->json(['success' => false, 'error' => 'Material not found'], 404);
        }
    }

    public function stockDetail(Request $request) {
        $partcode = $request->input('partcode');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $material = Material::with('stock')->where('part_code', $partcode)->first();

        $result = DB::select("
            SELECT
                    (
                        SELECT SUM(CASE WHEN warehouse_type = 'issued' THEN quantity * -1 ELSE quantity END)
                        FROM warehouse_records
                        WHERE material_id = m.material_id AND record_date < '".$startDate."'
                    ) AS computedOP
                FROM
                    materials m 
                LEFT OUTER JOIN
                    stocks o ON o.material_id = m.material_id
            WHERE m.material_id = '".$material->material_id."';
        ");

        $firstResult = $result[0] ?? null;

        $transactions = WarehouseRecord::with('warehouse', 'material')
        ->where('material_id', $material->material_id)
        ->whereHas('warehouse', function ($q) {
            $q->orderBy('transaction_id', 'asc');
        })
        ->whereBetween('record_date', [$startDate, $endDate])
        ->orderBy('created_at', 'asc')
        ->get();
        
        $context=[
            'transactions' => $transactions,
            'opening' => $material->stock->opening_balance + $firstResult?->computedOP,
        ];

        $returnHTML = view('popup.viewStockTransactions', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }

    public function getRawMaterials(Request $request)
    {
        $searchTerm = $request->input('q') ?? $request->input('term');
        $selectedValues = $request->input('selected_values', []);

        $selectedValues = array_filter($selectedValues, function ($value) {
            return $value !== null;
        });

        $query = Material::with('uom')
            ->where('type', '=', 'raw')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('material_purchases')
                    ->whereColumn('material_purchases.material_id', 'materials.material_id');
            });

        if (!empty($searchTerm)) {
            $query->where(function ($subquery) use ($searchTerm) {
                $subquery->where('description', 'like', '%' . $searchTerm . '%')
                    ->orWhere('part_code', 'like', '%' . $searchTerm . '%');
            });
        }

        if (!empty($selectedValues)) {
            $query->whereNotIn('materials.material_id', $selectedValues);
        }

        $materials = $query->orderBy('description')->get();

        return response()->json($materials);
    }

}
