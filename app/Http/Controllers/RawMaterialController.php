<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Commodity;
use App\Models\RawMaterial;
use App\Models\MaterialAttachments;
use App\Models\MaterialPurchase;
use App\Models\Material;
use App\Models\UomUnit;
use App\Models\Vendor;
use App\Models\Stock;

use App\Imports\ExcelImportClass;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Excel;

class RawMaterialController extends Controller
{
    public function index()
    {
        $rawmaterials = RawMaterial::with('uom', 'commodity', 'category')->where('type', 'raw')->orderBy('created_at', 'desc')->get();
        return view('rawmaterials', compact('rawmaterials'));
    }
    public function add()
    {
        $uom = UomUnit::all();
        $category = Category::all();
        $commodity = Commodity::all();
        return view('new-raw-material', compact('uom', 'category', 'commodity'));
    }

    public function bulk()
    {
        return view('bulk-raw-material');
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');

        $import = new ExcelImportClass('raw-material', Auth::id());
        Excel::import($import, $file);

        $importedRows = $import->getImportedCount();

        return redirect()->back()->with('success', $importedRows . ' records imported successfully!');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|unique:materials,description,NULL,material_id,type,raw',
            'uom_id' => 'required|exists:uom_units,uom_id',
            'commodity_id' => 'required|exists:commodities,commodity_id',
            'category_id' => 'required|exists:categories,category_id',
            'additional_notes' => 'nullable|string',
            'opening_balance' => 'required',
            'mpn' => 'nullable',
            'make' => 'nullable',
            're_order' => 'nullable',
            'vendor' => 'nullable|array',
            'vendor.*' => 'nullable|string',
            'price' => 'nullable|array',
            'price.*' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            } else {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $validatedData = $validator->validated();

        // Generate Partcode
        $newPartCode = $this->generatePartCode($validatedData['commodity_id'], $validatedData['category_id']);

        $rawMaterial = new RawMaterial($validatedData);
        $rawMaterial->type = "raw";
        $rawMaterial->part_code = $newPartCode;
        $rawMaterial->created_by = Auth::id();
        $rawMaterial->updated_by = Auth::id();
        $rawMaterial->save();

        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = public_path('assets/uploads/materials/' . $filename);
            Image::make($image)->resize(200, 200)->save($imagePath);
            MaterialAttachments::create([
                'material_id' => $rawMaterial->material_id, // Set foreign key
                'path' => $filename,
                'type' => 'image',
            ]);
        }

        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');
            $pdfName = time() . '.' . $pdf->getClientOriginalExtension();
            $pdfPath = public_path('assets/uploads/pdf/');
            $pdf->move($pdfPath, $pdfName);
            MaterialAttachments::create([
                'material_id' => $rawMaterial->material_id,
                'path' => $pdfName,
                'type' => 'pdf',
            ]);
        }

        if ($request->hasFile('doc')) {
            $doc = $request->file('doc');
            $docName = time() . '.' . $doc->getClientOriginalExtension();
            $docPath = public_path('assets/uploads/doc/');
            $doc->move($docPath, $docName);
            MaterialAttachments::create([
                'material_id' => $rawMaterial->material_id,
                'path' => $docName,
                'type' => 'doc',
            ]);
        }
        $vendor_id = null;
        for ($i = 0; $i < count($validatedData['vendor']); $i++) {
            if (!empty($validatedData['vendor'][$i])) {
                $vendor = Vendor::firstOrCreate(
                    ['vendor_name' => $validatedData['vendor'][$i]],
                    ['vendor_id' => Str::uuid(), 'created_at' => Carbon::now()]
                );

                MaterialPurchase::create([
                    'material_id' => $rawMaterial->material_id,
                    'vendor_id' => $vendor->vendor_id,
                    'price' => $validatedData['price'][$i],
                ]);
            }
        }

        //Insert data in stocks table
        $stock = new Stock;
        $stock->material_id = $rawMaterial->material_id;
        $stock->opening_balance = $validatedData['opening_balance'];
        $stock->receipt_qty = 0;
        $stock->issue_qty = 0;
        $stock->created_by = Auth::id();
        $stock->created_at = Carbon::now();
        $stock->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Raw material added successfully.'], 200);
        } else {
            return redirect()->route('raw')->with('success', 'Raw material added successfully.');
        }
    }

    public function edit(RawMaterial $material)
    {
        $uoms = UomUnit::all();
        $categories = Category::all();
        $commodities = Commodity::all();

        $material = $material->fresh();
        $attachments = $material->attachments()->get();
        $uom = $material->uom()->first();
        $commodity = $material->commodity()->first();
        $category = $material->category()->first();
        $purchases = $material->purchases()->with('vendor')->get();

        $stock = Stock::where('material_id', $material->material_id)->first();

        $context = [
            'material' => $material,
            'attachments' => $attachments,
            'commodity' => $commodity,
            'category' => $category,
            'uom' => $uom,
            'uoms' => $uoms,
            'categories' => $categories,
            'commodities' => $commodities,
            'purchases' => $purchases,
            'stock' => $stock,
        ];

        $returnHTML = view('edit-raw-material', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }

    public function show(RawMaterial $material)
    {
        $uoms = UomUnit::all();
        $categories = Category::all();
        $commodities = Commodity::all();

        $material = $material->fresh();
        $attachments = $material->attachments()->get();
        $uom = $material->uom()->first();
        $commodity = $material->commodity()->first();
        $category = $material->category()->first();
        $purchases = $material->purchases()->with('vendor')->get();

        $context = [
            'material' => $material,
            'attachments' => $attachments,
            'commodity' => $commodity,
            'category' => $category,
            'uom' => $uom,
            'uoms' => $uoms,
            'categories' => $categories,
            'commodities' => $commodities,
            'purchases' => $purchases,
        ];

        $returnHTML = view('view-raw-material', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }

    public function update(Request $request, RawMaterial $material)
    {
        $validatedData = $request->validate([
            'description' => 'required|string',
            'uom_id' => 'required|exists:uom_units,uom_id',
            'commodity_id' => 'required|exists:commodities,commodity_id',
            'category_id' => 'required|exists:categories,category_id',
            'additional_notes' => 'nullable|string',
            'opening_balance' => 'required',
            'mpn' => 'nullable',
            'make' => 'nullable',
            're_order' => 'nullable',
            'vendor' => 'nullable|array',
            'vendor.*' => 'nullable|string',
            'price' => 'nullable|array',
            'price.*' => 'nullable|numeric',
        ]);

        try {
            DB::beginTransaction();

            $material->fill($validatedData);
            $material->updated_by = Auth::id();
            $material->save();

            // Handle file uploads
            if ($request->hasFile('photo')) {
                $previousPhoto = MaterialAttachments::where('material_id', $material->material_id)
                    ->where('type', 'image')
                    ->first();

                if ($previousPhoto) {
                    $previousPhoto->delete();
                    Storage::delete('public/assets/uploads/materials/' . $previousPhoto->path);
                }

                // Upload and save the new photo
                $image = $request->file('photo');
                $filename = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = public_path('assets/uploads/materials/' . $filename);
                Image::make($image)->resize(200, 200)->save($imagePath);
                MaterialAttachments::create([
                    'material_id' => $material->material_id,
                    'path' => $filename,
                    'type' => 'image',
                ]);
            }

            if ($request->hasFile('pdf')) {
                $previousPdf = MaterialAttachments::where('material_id', $material->material_id)
                    ->where('type', 'pdf')
                    ->first();

                if ($previousPdf) {
                    $previousPdf->delete();
                    Storage::delete('public/assets/uploads/pdf/' . $previousPdf->path);
                }

                $pdf = $request->file('pdf');
                $pdfName = time() . '.' . $pdf->getClientOriginalExtension();
                $pdfPath = public_path('assets/uploads/pdf/');
                $pdf->move($pdfPath, $pdfName);
                MaterialAttachments::create([
                    'material_id' => $material->material_id,
                    'path' => $pdfName,
                    'type' => 'pdf',
                ]);
            }

            if ($request->hasFile('doc')) {
                $previousDoc = MaterialAttachments::where('material_id', $material->material_id)
                    ->where('type', 'doc')
                    ->first();

                if ($previousDoc) {
                    $previousDoc->delete();
                    Storage::delete('public/assets/uploads/doc/' . $previousDoc->path);
                }

                $doc = $request->file('doc');
                $docName = time() . '.' . $doc->getClientOriginalExtension();
                $docPath = public_path('assets/uploads/doc/');
                $doc->move($docPath, $docName);
                MaterialAttachments::create([
                    'material_id' => $material->material_id,
                    'path' => $docName,
                    'type' => 'doc',
                ]);
            }

            // Update or insert Vendor records
            $this->updateMaterialVendors($material, $request);

            //Update or Insert Stock records
            $stock = Stock::where('material_id', $material->material_id)->first();

            if ($stock) {
                // Stock record exists, update it
                $stock->opening_balance = $validatedData['opening_balance'];
                $stock->updated_by = Auth::id();
                $stock->updated_at = Carbon::now();
                $stock->save();
            } else {
                // Stock record does not exist, insert a new one
                $stock = new Stock;
                $stock->material_id = $material->material_id;
                $stock->opening_balance = $validatedData['opening_balance'];
                $stock->receipt_qty = 0;
                $stock->issue_qty = 0;
                $stock->created_by = Auth::id();
                $stock->created_at = Carbon::now();
                $stock->save();
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Raw Material updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'message' => 'Failed to update material. ' . $e->getMessage()], 500);
        }
    }

    private function updateMaterialVendors($material, $request)
    {
        if ($request->has('vendor') && $request->has('price')) {
            $vendors = $request->input('vendor');
            $prices = $request->input('price');

            if (count($vendors) === count($prices)) {
                try {
                    DB::beginTransaction();

                    $existingMaterialPurchases = MaterialPurchase::where('material_id', $material->material_id)->get();
                    foreach ($existingMaterialPurchases as $existingMaterialPurchase) {
                        if (!in_array($existingMaterialPurchase->vendor_id, $vendors)) {
                            $existingMaterialPurchase->delete();
                        }
                    }

                    foreach ($vendors as $index => $vendor) {
                        if (!empty($vendor)) {
                            $vendorModel = Vendor::firstOrCreate(
                                ['vendor_name' => $vendor],
                                ['vendor_id' => Str::uuid(), 'created_at' => Carbon::now()]
                            );

                            MaterialPurchase::updateOrCreate(
                                [
                                    'material_id' => $material->material_id,
                                    'vendor_id' => $vendorModel->vendor_id,
                                ],
                                ['price' => $prices[$index]]
                            );
                        }
                    }

                    DB::commit();
                    return response()->json(['status' => true, 'message' => 'Raw Material updated successfully'], 200);
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json(['status' => false, 'message' => 'Failed to update material. ' . $e->getMessage()], 500);
                }
            } else {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'Vendors and price count mismatch'], 400);
            }
        }

    }

    private function generatePartCode($commodity_id = '', $category_id = '')
    {
        if ($commodity_id && $category_id) {
            $commodityCode = str_pad(Commodity::find($commodity_id)->commodity_number, 2, '0', STR_PAD_LEFT);
            $categoryCode = str_pad(Category::find($category_id)->category_number, 3, '0', STR_PAD_LEFT);
            $lastMaterial = RawMaterial::where('type', 'raw')
                ->where('commodity_id', $commodity_id)
                ->where('category_id', $category_id)
                ->orderBy('part_code', 'desc')
                ->first();
            $lastPartCode = $lastMaterial ? substr($lastMaterial->part_code, -5) + 1 : 1;
            $newPartCode = $commodityCode . $categoryCode . str_pad($lastPartCode, 5, '0', STR_PAD_LEFT);
            return $newPartCode;
        }
        return null;

    }

    public function destroy(RawMaterial $material)
    {
        try {
            $material->purchases()->delete();
            $material->attachments()->delete();
            $material->delete();
            return redirect()->route('raw')->with('success', 'Raw Material deleted successfully');
        } catch (\Exception $e) {
            \Log::error('Error deleting raw material: ' . $e->getMessage());
            return redirect()->route('raw')->with('error', 'An error occurred while deleting the Raw Material');
        }
    }

    public function priceList(){
        return view('reports.rm-price-list');
    }

    public function fetchPriceList(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $query = Material::query()->with(['category', 'commodity']);

        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('part_code', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('category_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('commodity', function ($query) use ($search) {
                        $query->where('commodity_name', 'like', '%' . $search . '%');
                    });
            });
        }

        $totalRecords = $query->count();

        if (!in_array($columnName, ['serial', 'price_1', 'price_2', 'price_3'])) {
            $query->orderBy($columnName, $columnSortOrder);
        }

        $materials = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
        $data = [];

        foreach ($materials->items() as $index => $item) {
            $priceStats = MaterialPurchase::where('material_id', $item->material_id)
                ->groupBy('material_id')
                ->select([
                    DB::raw('MAX(price) as max_price'),
                    DB::raw('MIN(price) as min_price'),
                    DB::raw('AVG(price) as avg_price'),
                ])
                ->first();

            $data[] = [
                'serial' => $index + 1, 
                'part_code' => $item->part_code,
                'description' => $item->description,
                'commodity' => $item->commodity->commodity_name,
                'category' => $item->category->category_name,
                'price_1' => $priceStats?->min_price,
                'price_2' => $priceStats?->avg_price,
                'price_3' => $priceStats?->max_price,
            ];
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $materials->total(),
            "data" =>  $data,
        ];

        return response()->json($response);
    }


}
