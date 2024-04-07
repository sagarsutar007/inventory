<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\DependentMaterial;

class DependentMaterialController extends Controller
{
    public function index()
    {
        $dependents = DependentMaterial::withCount([
            'materials as raw_count' => function ($query) {
                $query->where('type', 'raw');
            },
        ])->orderBy('created_at', 'desc')->get();

        return view('dependent.materials', compact('dependents'));
    }

    public function add()
    {
        return view('dependent.new');
    }

    public function store(Request $request)
    {

        $request->validate([
            'frequency' => 'required|array',
            'description' => 'required|array|unique:dependent_materials,description',
            'frequency.*' => 'required|string',
            'description.*' => 'required|string',
        ],[
            'description.*.required' => 'Description is required.',
            'frequency.*.required' => 'Please select frequency.',
            'description.*.unique' => 'Description must be unique.',
        ]);

        $frequencyData = $request->input('frequency');
        $descriptionData = $request->input('description');
        
        foreach ($frequencyData as $index => $frequencyData) {
            DependentMaterial::create([
                'description' => $descriptionData[$index],
                'frequency' => $frequencyData,
                'created_by' => Auth::id()
            ]);
        }

        return redirect()->route('dm.add')->with('success', 'Record added successfully');
    }

    public function edit(DependentMaterial $record)
    {
        if (!$record) {
            return response()->json(['error' => 'Record not found!'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['record' => $record]);
    }

    public function update(Request $request, DependentMaterial $record)
    {
        try {
            $request->validate([
                'description' => 'required|string|max:120',
                'frequency' => 'required|string|max:120',
            ]);

            // print_r([
            //     'frequency' => $request->input('frequency'),
            //     'description' => $request->input('description'),
            //     'updated_by' => Auth::id()
            // ]); exit();
            
            // $record->frequency = $request->input('frequency');
            // $record->description = $request->input('description');
            // $record->updated_by = Auth::id();
            // $record->save();
            
            $record->update([
                'frequency' => $request->input('frequency'),
                'description' => $request->input('description'),
                'updated_by' => Auth::id()
            ]);
    
            return response()->json(['message' => 'Record updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating record: ' . $e->getMessage()], 500);
        }
    }

    public function save(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'frequency' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false, 'errors' => $validator->errors()], 422);
        }
        
        $record = new DependentMaterial();
        $record->description = $request->description;
        $record->frequency = $request->frequency;
        $record->created_by = Auth::id();
        $record->save();

        $output = [
            'id' => $record->dm_id,
            'text' => $record->description . " - " . $record->frequency,
        ];

        return response()->json(['status'=>true, 'message' => 'Record created successfully', 'record' => $output], 200);
    }

    public function destroy(DependentMaterial $record)
    {
        $record->delete();

        return redirect()->route('categories')->with('success', 'Record deleted successfully');
    }

    public function search(Request $request) 
    {
        $searchTerm = $request->input('q');

        $records = DependentMaterial::select(DB::raw("concat(description, ' - ', frequency) as text"), 'dm_id as id')
        ->when($searchTerm, function ($query) use ($searchTerm) {
            $query->where('description', 'like', '%' . $searchTerm . '%')
                ->orWhere('frequency', 'like', '%' . $searchTerm . '%');
        })
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

        return response()->json([
            'status'=>true, 
            'message' => 'Records fetched successfully!', 
            'results' => $records
        ], 200);
    }
}
