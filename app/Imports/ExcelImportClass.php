<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Events\AfterImport;
use App\Models\Commodity;
use App\Models\Category;
use App\Models\RawMaterial;
use App\Models\UomUnit;
use Carbon\Carbon;

class ExcelImportClass implements ToCollection, WithBatchInserts
{
    protected $type;
    protected $user;
    private $importedCount = 0;

    public function __construct($type, $user)
    {
        $this->type = $type;
        $this->user = $user;
    }

    public function collection(Collection $rows)
    {
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
            foreach ($rows->slice(1) as $row) {
                $this->addRawMaterial($row, $this->user);
            }
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
        if($name) {
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
        if($name) {
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
        if(count($data)) {
            if (!empty($data[4]) && !empty($data[2]) && !empty($data[3])) {
                $commodity = Commodity::where('commodity_name', '=', $data[2])->first();
                $category = Category::where('category_name', '=', $data[3])->first();
                if ($data[6]) {
                    $uom = UomUnit::where('uom_shortcode', '=', $data[6])->orWhere('uom_text', '=', $data[6])->first();
                }
                if  ($commodity && $category){
                    try {
                        RawMaterial::firstOrCreate(
                            [
                                'description' => $data[4],
                                'type' => 'raw',
                            ],
                            [
                                'part_code' => $this->generatePartCode($commodity->commodity_number, $category->category_number),
                                'description' => $data[4],
                                'uom_id' => $uom->uom_id??'',
                                'opening_balance' => 0,
                                'additional_notes' => '',
                                'type' => 'raw',
                                'mpn' => $data[5],
                                'category_id' => $category->category_id,
                                'commodity_id'=> $commodity->commodity_id,
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

    protected function generatePartCode($commodity_number='', $category_number='')
    {
        if ($commodity_number && $category_number) {
            $commodityCode = $commodity_number;
            $categoryCode = $category_number;
            
            try {
                \DB::beginTransaction();
                $lastMaterial = RawMaterial::where('type', 'raw')
                ->where('commodity_id', $commodity_number)
                ->where('category_id', $category_number)
                ->orderBy('part_code', 'desc')
                ->pluck('part_code')
                ->first();
                $lastPartCode = $lastMaterial ? substr($lastMaterial, -5) + 1 : 1;

                do {
                    $newPartCode = $commodityCode . $categoryCode . str_pad($lastPartCode, 5, '0', STR_PAD_LEFT);
                    $exists = RawMaterial::where('part_code', $newPartCode)->exists();
                    if ($exists) { $lastPartCode++; }
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

}
