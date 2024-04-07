<?php

namespace App\Imports;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Facades\DB;

use App\Models\Commodity;
use App\Models\Category;
use App\Models\RawMaterial;
use App\Models\Material;
use App\Models\UomUnit;
use App\Models\Bom;
use App\Models\BomRecord;
use App\Models\DependentMaterial;
use Carbon\Carbon;

class ExcelImportClass implements ToCollection, WithBatchInserts
{
    protected $type;
    protected $user;
    protected $data;
    private $importedCount = 0;

    public function __construct($type, $user, $data = null)
    {
        $this->type = $type;
        $this->user = $user;
        $this->data = $data;
    }

    public function collection(Collection $rows)
    {
        try {
            DB::beginTransaction();
            $this->importedCount += count($rows->slice(1));
            if ($this->type === "commodity") {
                $code = $this->getNextCommodityCode();
                foreach ($rows->slice(1) as $row) {
                    $this->addCommodity($row[0], $code, $this->user);
                    $code++;
                }

                return true;
            } elseif ($this->type === "category") {
                $code = $this->getNextCategoryCode();
                foreach ($rows->slice(1) as $row) {
                    $this->addCategory($row[0], $code, $this->user);
                    $code++;
                }

                return true;
            } elseif ($this->type === "raw-material") {
                foreach ($rows->slice(2) as $row) {
                    $this->addRawMaterial($row, $this->user);
                }
            } elseif ($this->type === "bom") {
                
                $valueInA1 = $rows[0][0];
                $material_id = $this->data["material_id"];

                $parentMaterial = [
                    'a1cell' =>$valueInA1,
                    'material_id' => $material_id,
                    'records' => $rows->slice(2),
                ];

                $errors = $this->validateImport($parentMaterial);
                if (empty($errors)) {
                    $material = Material::findOrFail($material_id);
                    $imported_part_code = [];

                    foreach ($rows->slice(2) as $row) {
                        $this->importBom($row, $this->user, $this->data);
                        $part_code = $row[0];
                        if (!in_array($row[0], $imported_part_code)) {
                            $imported_part_code[] = $part_code;
                        }
                    }

                    $materialIdsToDelete = Material::whereIn('part_code', $imported_part_code)
                        ->pluck('material_id');

                    BomRecord::whereNotIn('material_id', $materialIdsToDelete)
                        ->delete();
                }else {
                    throw new \Exception(implode("\n", $errors));
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function getNextCommodityCode()
    {
        $commodity = Commodity::orderBy('commodity_number', 'desc')->first();
        if ($commodity) {
            $commodityNumber = $commodity->commodity_number + 1;
        } else {
            $commodityNumber = 10;
        }
        return $commodityNumber;
    }

    protected function getNextCategoryCode()
    {
        $category = Category::orderBy('category_number', 'desc')->first();
        if ($category) {
            $categoryNumber = $category->category_number + 1;
        } else {
            $categoryNumber = 100;
        }
        return $categoryNumber;
    }

    protected function addCommodity($name, $code, $user)
    {
        if ($name) {
            $commodity = Commodity::where('commodity_name', 'like', "$name")->first();
            if (!$commodity) {
                Commodity::create(
                    [
                        'commodity_name' => $name,
                        'commodity_number' => $code,
                        'created_by' => $user,
                        'commodity_id' => Str::uuid(),
                        'created_at' => Carbon::now(),
                    ]
                );
            }
        }
    }

    protected function addCategory($name, $code, $user)
    {
        if ($name) {
            try {
                Category::firstOrCreate(
                    ['category_name' => $name],
                    [
                        'category_number' => $code,
                        'created_by' => $user,
                        'category_id' => Str::uuid(),
                        'created_at' => Carbon::now(),
                    ]
                );
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }

    protected function addRawMaterial($data, $user)
    {
        if (count($data)) {
            if (!empty($data[2]) && !empty($data[4]) && !empty($data[5])) {
                $commodity = Commodity::where('commodity_name', '=', $data[4])->first();
                $category = Category::where('category_name', '=', $data[5])->first();
                if ($data[3]) {
                    $uom = UomUnit::where('uom_shortcode', '=', $data[3])->orWhere('uom_text', '=', $data[3])->first();
                }
                if ($data[8]) {
                    $dm = DependentMaterial::where('description', '=', $data[8])->first();
                }
                if ($commodity && $category) {
                    try {
                        RawMaterial::firstOrCreate(
                            [
                                'description' => $data[2],
                                'type' => 'raw',
                            ],
                            [
                                'part_code' => $this->generatePartCode($commodity->commodity_number, $category->category_number),
                                'description' => $data[2],
                                'uom_id' => $uom->uom_id ?? '',
                                // 'opening_balance' => 0,
                                'additional_notes' => '',
                                'type' => 'raw',
                                'mpn' => $data[7],
                                'make' => $data[6],
                                'category_id' => $category->category_id,
                                'commodity_id' => $commodity->commodity_id,
                                'dm_id' => $dm->dm_id,
                                'created_by' => $user
                            ]
                        );
                    } catch (\Throwable $th) {
                        throw $th;
                    }
                }
            }
        }
    }

    protected function importBom($data, $user, $extraData)
    {
        if (count($data)) {
            if (!empty($data[0]) && !empty($data[2]) && !empty($extraData)) {
                $material_id = $extraData['material_id'];
                $part_code = $data[0];
                $quantity = $data[2];
                $bomMaterial = Material::with('uom')->find($material_id);

                $material = Material::where('part_code', $part_code)->first();

                if (!$material) {
                    throw new \Exception("Material with part code {$part_code} not found.");
                }

                $bom = Bom::where('material_id', $material_id)->first();

                if ($bom) {
                    $bomRecord = BomRecord::where('bom_id', $bom->bom_id)
                        ->where('material_id', $material->material_id)
                        ->first();

                    if ($bomRecord) {
                        $bomRecord->quantity = $quantity;
                        $bomRecord->save();
                    } else {
                        $bomRecord = new BomRecord();
                        $bomRecord->bom_id = $bom->bom_id;
                        $bomRecord->material_id = $material->material_id;
                        $bomRecord->quantity = $quantity;
                        $bomRecord->save();
                    }
                } else {
                    $bom = new Bom();
                    $bom->material_id = $material_id;
                    $bom->created_by = $this->user;
                    $bom->created_at = Carbon::now();
                    $bom->uom_id = $bomMaterial->uom->uom_id;
                    $bom->save();

                    // Create new BomRecord
                    $bomRecord = new BomRecord();
                    $bomRecord->bom_id = $bom->bom_id;
                    $bomRecord->material_id = $material->material_id;
                    $bomRecord->quantity = $quantity;
                    $bomRecord->save();
                }
            }
        }
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public static function afterImport(AfterImport $event)
    {
        $importedCount = $event->getConcernable()->importedCount;
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    protected function generatePartCode($commodity_number = '', $category_number = '')
    {
        if ($commodity_number && $category_number) {
            $commodityCode = $commodity_number;
            $categoryCode = $category_number;

            try {
                \DB::beginTransaction();
                $lastMaterial = RawMaterial::where('type', 'raw')
                    ->orderBy('created_at', 'desc')
                    ->pluck('part_code')
                    ->first();
                    
                    // ->where('commodity_id', $commodity_number)
                    // ->where('category_id', $category_number)
                $lastPartCode = $lastMaterial ? substr($lastMaterial, -5) + 1 : 1;

                do {
                    $newPartCode = $commodityCode . $categoryCode . str_pad($lastPartCode, 5, '0', STR_PAD_LEFT);
                    $exists = RawMaterial::where('part_code', $newPartCode)->exists();
                    if ($exists) {
                        $lastPartCode++;
                    }
                } while ($exists);

                \DB::commit();

                return $newPartCode;
            } catch (\Throwable $th) {
                \DB::rollBack();
                throw $th;
            }
        }

        return null;
    }

    protected function validateImport($data=[])
    {
        $errors = [];

        if (empty($data['material_id'])) { $errors[] = "Material Id is Required!"; }

        $material_id = $data['material_id'];
        $material = Material::find($material_id);

        if (!$material) { 
            $errors[] = "Material not found!"; 
        } else {
            if ($data['a1cell'] != $material->part_code) {
                $errors[] = "Parent Item Part Code " . $material->part_code . " and XLS Import Parent Item Part code " . $data['a1cell'] . " are not matching."; 
            }
            $counter = 3;
            foreach ($data['records'] as $row) {
                if (empty($row[0])) {
                    $errors[] = "Row: " . $counter . " - Partcode not found!";
                }

                if (empty($row[0])) {
                    $errors[] = "Row: " . $counter . " - Quantity not found!";
                }

                $bomMaterial = Material::with('uom')->find($material_id);
                
                $material = Material::where('part_code', $row[0])->first();

                if (!$material) {
                    $errors[] = "Row: " . $counter . " Material with part code {$row[0]} not found.";
                }

                $counter++;
            }
        }

        return $errors;
    }

}
