<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\UomUnit;


class UomunitController extends Controller
{
    public function store(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'uom_text' => 'required|string|unique:uom_units,uom_text',
            'uom_shortcode' => 'required|string|unique:uom_units,uom_shortcode',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false, 'errors' => $validator->errors()], 422);
        }

        $uomUnit = new UomUnit();
        $uomUnit->uom_text = $request->uom_text;
        $uomUnit->uom_shortcode = $request->uom_shortcode;
        $uomUnit->save();

        $output = [
            'id' => $uomUnit->uom_id,
            'text' => $uomUnit->uom_text,
        ];

        return response()->json(['status'=>true, 'message' => 'UOM created successfully', 'uom' => $output], 200);
    }


    public function search(Request $request) 
    {
        $searchTerm = $request->input('q');

        $units = UomUnit::select('uom_id as id', 'uom_text as text')
        ->when($searchTerm, function ($query) use ($searchTerm) {
            $query->where('uom_text', 'like', '%' . $searchTerm . '%')
                ->orWhere('uom_shortcode', 'like', '%' . $searchTerm . '%');
        })
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

        return response()->json([
            'status'=>true, 
            'message' => 'Units fetched successfully!', 
            'results' => $units
        ], 200);
    }

}
