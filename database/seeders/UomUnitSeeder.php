<?php

namespace Database\Seeders;

use App\Models\UomUnit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UomUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $uomUnitsData = [
            [
                'uom_text' => 'Meter',
                'uom_shortcode' => 'Mtr',
            ],
            [
                'uom_text' => 'Kilogram',
                'uom_shortcode' => 'Kg',
            ],
            [
                'uom_text' => 'Pieces',
                'uom_shortcode' => 'Pcs'
            ],
            [
                'uom_text' => 'Litre',
                'uom_shortcode' => 'Ltr'
            ],
            [
                'uom_text' => 'Grams',
                'uom_shortcode' => 'Gms'
            ],
            [
                'uom_text' => 'Packet',
                'uom_shortcode' => 'Pkt'
            ],
            [
                'uom_text' => 'Roll',
                'uom_shortcode' => 'Roll'
            ]
        ];

        foreach ($uomUnitsData as $data) {
            UomUnit::create($data);
        }
    }
}
