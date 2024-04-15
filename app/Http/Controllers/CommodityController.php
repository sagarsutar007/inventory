<?php

namespace App\Http\Controllers;

use App\Models\Commodity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Imports\ExcelImportClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

use Excel;


class CommodityController extends Controller
{

    public function index()
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('view-commodities', Auth::user())) {
            $commodities = Commodity::withCount([
                'materials as raw_count' => function ($query) {
                    $query->where('type', 'raw');
                },
                'materials as semi_finished_count' => function ($query) {
                    $query->where('type', 'semi-finished');
                },
                'materials as finished_count' => function ($query) {
                    $query->where('type', 'finished');
                }
            ])->orderBy('commodity_number', 'desc')->get();
            
            return view('commodities', compact('commodities'));
        } else {
            abort(403);
        }
        
    }

    public function add()
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('add-commodity', Auth::user())) {
            return view('new-commodity');
        } else {
            abort(403);
        }
    }

    public function bulk()
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('add-commodity', Auth::user())) {
            return view('bulk-commodity');
        } else {
            abort(403);
        }
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
        
        $file = $request->file('file');
        
        $import =  new ExcelImportClass('commodity', Auth::id());
        Excel::import($import, $file);

        $importedRows = $import->getImportedCount();
 
        return redirect()->back()->with('success', $importedRows . ' records imported successfully!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'commodities' => 'required|array',
            'commodities.*' => 'required|string',
        ],[
            'commodities.*.required' => 'Enter Commodity name or remove the blank field.',
        ]);

        $commodityData = $request->input('commodities');
        $code = $this->getNextCommodityCode();
        
        foreach ($commodityData as $commodityName) {
            Commodity::create([
                'commodity_name' => $commodityName,
                'commodity_number' => $code,
                'created_by' => Auth::id()
            ]);
            $code++;
        }

        return redirect()->route('commodities.add')->with('success', 'One or more Commodities were added successfully');
    }

    public function edit(Commodity $commodity)
    {
        if (!$commodity) {
            return response()->json(['error' => 'Commodity not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['commodity' => $commodity]);
    }

    public function update(Request $request, Commodity $commodity)
    {
        $request->validate([
            'commodity_name' => 'required|string|max:255',
        ]);

        $commodity->update([
            'commodity_name' => $request->input('commodity_name'),
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Commodity updated successfully']);
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

    public function destroy(Commodity $commodity)
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('delete-commodity', Auth::user())) {
            if ($commodity->materials()->exists()) {
                return redirect()->route('commodities')->with('error', 'Commodity cannot be deleted because it is in use');
            }

            $commodity->delete();

            return redirect()->route('commodities')->with('success', 'Commodity deleted successfully');
        } else {
            abort(403);
        }
    }

    public function save(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'commodity_name' => 'required|string|unique:commodities,commodity_name',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false, 'errors' => $validator->errors()], 422);
        }

        $code = $this->getNextCommodityCode();
        
        $commodity = new Commodity();
        $commodity->commodity_name = $request->commodity_name;
        $commodity->commodity_number = $code;
        $commodity->created_by = Auth::id();
        $commodity->save();

        $output = [
            'id' => $commodity->commodity_id,
            'text' => $commodity->commodity_name,
        ];

        return response()->json(['status'=>true, 'message' => 'Commodity created successfully', 'commodity' => $output], 200);
    }

    public function search(Request $request) 
    {
        $searchTerm = $request->input('q');

        $commodities = Commodity::select('commodity_id as id', 'commodity_name as text')
        ->when($searchTerm, function ($query) use ($searchTerm) {
            $query->where('commodity_name', 'like', '%' . $searchTerm . '%')
                ->orWhere('commodity_number', 'like', '%' . $searchTerm . '%');
        })
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

        return response()->json([
            'status'=>true, 
            'message' => 'Commodities fetched successfully!', 
            'results' => $commodities
        ], 200);
    }
}
