<?php

namespace App\Exports;

use App\Models\BomRecord;
use App\Models\Material;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Log;

class BomRecordExport implements FromCollection, WithHeadings
{
    protected $materialId;
    protected $parentItemDescription;
    protected $uom;

    public function __construct($materialId, $parentItemDescription, $uom)
    {
        $this->materialId = $materialId;
        $this->parentItemDescription = $parentItemDescription;
        $this->uom = $uom;
    }

    public function collection()
    {
        $bomRecords = BomRecord::whereHas('bom', function ($query) {
            $query->where('material_id', $this->materialId);
        })->with([
                    'material' => function ($query) {
                        $query->select('material_id', 'part_code', 'description', 'type')->with('uom');
                    }
                ])->get();

        Log::info($bomRecords->toArray()); // Debug the fetched data

        return $bomRecords->map(function ($bomRecord) {
            $material = Material::with('uom')->find($bomRecord->material->material_id);

            return [
                $bomRecord->material->part_code,
                $bomRecord->material->description,
                $bomRecord->quantity,
                $material->uom ? $material->uom->uom_text : null,
                $bomRecord->material->type,
            ];
        });
    }

    public function headings(): array
    {
        return [
            ["BOM of {$this->parentItemDescription} - {$this->uom}"],
            ['Part Code', 'Material Description', 'Quantity', 'Unit', 'Type'],
        ];
    }
}
