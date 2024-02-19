<?php

namespace App\Http\Controllers;

use App\Exports\BomRecordExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Material;
use Illuminate\Support\Facades\Log;

class MaterialController extends Controller
{

    public function exportBomRecords($materialId)
    {
        $material = Material::findOrFail($materialId);
        $partCode = str_replace(['/', '\\'], ['_', '_'], $material->part_code);
        $description = str_replace(['/', '\\'], ['_', '_'], $material->description);
        $fileName = $partCode . '_' . $description . '_BOM.xlsx';

        $export = new BomRecordExport($materialId);

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

}
