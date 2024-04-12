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
use App\Models\MaterialPurchase;
use App\Models\Vendor;
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
    private $errors=[];

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
                $this->importedCount--;
                $rowCount = 3;
                foreach ($rows->slice(2) as $row) {
                    $this->addRawMaterial($row, $this->user, $rowCount);
                    $rowCount++;
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
            
            if (count($this->errors)) {
                throw new \Exception("Error occured while parsing the rows.");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // throw $e;
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

    public function setErrorMessages($data)
    {
        $this->errors[] = $data;
    }

    public function getErrorMessages()
    {
        return $this->errors;
    }

    protected function addRawMaterial($data, $user, $rowCount)
    {
        if (count($data)) {
            if (!empty($data[0]) && !empty($data[2]) && !empty($data[3])) {
                $commodity = Commodity::where('commodity_name', '=', $data[2])->first();
                $category = Category::where('category_name', '=', $data[3])->first();

                if (!empty($data[1])) {
                    $uom = UomUnit::where('uom_shortcode', '=', $data[1])->orWhere('uom_text', '=', $data[1])->first();
                } else {
                    $this->setErrorMessages(['status'=>'error', 'message' => 'Unit is empty. You can set it by editing material', 'row' => $rowCount]);
                }

                if (!empty($data[6]) && !empty($data[7])) {
                    $dm = DependentMaterial::where('description', 'like', $data[6])->where('frequency', 'like', $data[7])->first();
                } else {
                    if (empty($data[6])) {
                        $this->setErrorMessages(['status'=>'warning', 'message' => 'Dependent material is empty. You can set it later.', 'row' => $rowCount]);
                    } else {
                        $this->setErrorMessages(['status'=>'warning', 'message' => 'Dependent material frequency is blank', 'row' => $rowCount]);
                    }
                }

                if ($commodity && $category) {
                    try {
                        $material = RawMaterial::where('description', 'like', $data[0])->first();
                        if (!$material) {
                            $rawMaterial = RawMaterial::firstOrCreate(
                                [
                                    'description' => $data[0],
                                    'type' => 'raw',
                                ],
                                [
                                    'part_code' => $this->generatePartCode($commodity->commodity_number, $category->category_number),
                                    'description' => $data[0],
                                    'uom_id' => $uom->uom_id ?? '',
                                    'type' => 'raw',
                                    'make' => $data[4],
                                    'mpn' => $data[5],
                                    'category_id' => $category->category_id,
                                    'commodity_id' => $commodity->commodity_id,
                                    'dm_id' => $dm->dm_id ?? '',
                                    're_order' => $data[8],
                                    'additional_notes' => $data[15],
                                    'created_by' => $user
                                ]
                            );
                            
                            $this->enterPurchase($rawMaterial->material_id, $data[9], $data[10]);
                            $this->enterPurchase($rawMaterial->material_id, $data[11], $data[12]);
                            $this->enterPurchase($rawMaterial->material_id, $data[13], $data[14]);
                        } else {
                            $this->setErrorMessages(['status'=>'error', 'message' => $data[0] . ' already exists in the master with partcode ' . $material->part_code, 'row' => $rowCount]);
                        }
                        
                    } catch (\Throwable $th) {
                        throw $th;
                    }
                } else {
                    if ($commodity == null) {
                        $this->setErrorMessages(['status'=>'error', 'message' => 'Commodity does not exists in our records', 'row' => $rowCount]);
                    } 
                    
                    if ($category == null) {
                        $this->setErrorMessages(['status'=>'error', 'message' => 'Category does not exists in our records', 'row' => $rowCount]);
                    }
                }
            }else {
                if (empty($data[2])) {
                    $this->setErrorMessages(['status'=>'error', 'message' => 'Commodity is empty. It is required.', 'row' => $rowCount]);
                }

                if (empty($data[3])) {
                    $this->setErrorMessages(['status'=>'error', 'message' => 'Category is empty. It is required.', 'row' => $rowCount]);
                }

                if (empty($data[0])) {
                    $this->setErrorMessages(['status'=>'error', 'message' => 'Description is empty. It is required.', 'row' => $rowCount]);
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
                ->latest('created_at')
                ->value('part_code');
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

    protected function enterPurchase($material_id="", $vendor="", $price="")
    {
        if (!empty($material_id) && !empty($vendor) && !empty($price)) {
            $vendorInfo = Vendor::firstOrCreate([
                'vendor_name' => $vendor,
            ]);

            MaterialPurchase::firstOrCreate([
                'material_id' => $material_id,
                'vendor_id' => $vendorInfo->vendor_id
            ],['price' => $price] );  
        }
    }
}
