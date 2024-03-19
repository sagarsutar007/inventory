<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Vendor;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('supplier.list');
    }

    public function get(Request $request) 
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $query = Vendor::query();

        if (!empty($search)) {
            $query->where('vendor_name', 'like', '%' . $search . '%')
                ->orWhere('vendor_city', 'like', '%' . $search . '%')
                ->orWhere('vendor_address', 'like', '%' . $search . '%');
        }

        $totalRecords = $query->count();
        
        $query->orderBy($columnName, $columnSortOrder);
        $vendors = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $vendors->total(),
            "data" =>  $vendors->items(),
        ];

        return response()->json($response);
    }

    /**
     * Save a resource in storage.
     */
    public function save(Request $request)
    {
        $requestData = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'vendor_city' => 'nullable|string|max:255',
            'vendor_address' => 'nullable|string',
        ]);

        $vendorId = $request->input('vendor_id');

        if ($vendorId) {
            $requestData['updated_by'] = Auth::id();
            $vendor = Vendor::find($vendorId);
            if (!$vendor) {
                return response()->json(['status' => false, 'message' => 'Vendor not found.'], 404);
            }

            $vendor->update($requestData);
            $message = 'Vendor updated successfully.';
        } else {
            $requestData['created_by'] = Auth::id();
            $vendor = Vendor::create($requestData);
            $message = 'Vendor added successfully.';
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $requestData = $request->validate([
            'vendor_id' => 'required',
        ]);

        $vendorId = $request->input('vendor_id');

        if ($vendorId) {
            $vendor = Vendor::find($vendorId);
            if (!$vendor) {
                return response()->json(['status' => false, 'message' => 'Vendor not found.'], 404);
            }

            return response()->json(['status' => true, 'vendor' => $vendor], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Vendor not found.'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request)
    {
        $requestData = $request->validate([
            'vendor_id' => 'required',
        ]);

        $vendorId = $request->input('vendor_id');
        
        $vendor = Vendor::find($vendorId);
        
        if (!$vendor) {
            return response()->json(['status' => false, 'message' => 'Vendor not found.'], 404);
        }
        
        try {
            $vendor->delete();
            return response()->json(['status' => true, 'message' => 'Vendor deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to delete the vendor.'], 500);
        }
    }

    public function autocomplete(Request $request) 
    {
        $term = $request->input('term');
        $vendors = Vendor::where('vendor_name', 'like', '%' . $term . '%')->orderBy('vendor_name', 'asc')->pluck('vendor_name');
        return response()->json($vendors);
    }
}
