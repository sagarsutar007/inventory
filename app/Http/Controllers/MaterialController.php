<?php

namespace App\Http\Controllers;

use App\Exports\BomRecordExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Material;
use App\Imports\ExcelImportClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        // try {
        if ($request->hasFile('file')) {

            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
                'material_id' => 'required|string'
            ]);

            $file = $request->file('file');
            $exData = [
                'material_id' => $request->input('material_id')
            ];

            $import = new ExcelImportClass('bom', Auth::id(), $exData);
            Excel::import($import, $file);

            // $importedRows = $import->getImportedCount();

            return response()->json(['status' => true, 'message' => 'BOM records imported successfully.']);
        } else {
            return response()->json(['status' => false, 'message' => 'No file uploaded.']);
        }
        // } catch (\Exception $e) {
        //     Log::error('Error importing file: ' . $e->getMessage());
        //     return response()->json(['status' => false, 'error' => 'Failed to import BOM records.']);
        // }
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

        $material = Material::with('uom')
            ->where('part_code', '=', $searchTerm)
            ->first();

        if ($material) {
            // Material found, return success response with material details
            return response()->json(['success' => true, 'data' => $material]);
        } else {
            // Material not found, return error response
            \Log::error('Material not found for part code: ' . $searchTerm);
            return response()->json(['success' => false, 'error' => 'Material not found'], 404);
        }
    }

}
