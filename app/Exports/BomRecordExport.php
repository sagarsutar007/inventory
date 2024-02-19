<?php

namespace App\Exports;

use App\Models\BomRecord;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BomRecordExport implements FromCollection, WithHeadings
{
    protected $materialId;

    public function __construct($materialId)
    {
        $this->materialId = $materialId;
    }

    public function collection()
    {
        return BomRecord::whereHas('bom', function ($query) {
            $query->where('material_id', $this->materialId);
        })->with([
                    'material' => function ($query) {
                        $query->select('material_id', 'part_code', 'description', 'type'); // Select only necessary fields from Material model
                    }
                ])->get()->map(function ($bomRecord) {
                    return [
                        $bomRecord->material->part_code,
                        $bomRecord->material->description,
                        $bomRecord->quantity,
                        $bomRecord->material->type,
                    ];
                });
    }

    public function headings(): array
    {
        return [
            ['BOM Import'],
            ['Part Code', 'Material Description', 'Quantity', 'Type'],
        ];
    }
}
