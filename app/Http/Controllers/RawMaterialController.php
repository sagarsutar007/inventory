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
use App\Models\DependentMaterial;

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
        return view('rawmaterials');
    }

    public function fetchRawMaterials(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $rawmaterials = RawMaterial::with('uom', 'commodity', 'category', 'dependant')->where('type', 'raw');

        if (!empty ($search)) {
            $rawmaterials->where(function ($query) use ($search) {
                $query->where('part_code', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('make', 'like', '%' . $search . '%')
                    ->orWhere('mpn', 'like', '%' . $search . '%')
                    ->orWhere('re_order', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('category_name', 'like', '%' . $search . '%');
                        $query->orWhere('category_number', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('commodity', function ($query) use ($search) {
                        $query->where('commodity_name', 'like', '%' . $search . '%');
                        $query->orWhere('commodity_number', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('dependant', function ($query) use ($search) {
                        $query->where('description', 'like', '%' . $search . '%');
                        $query->orWhere('frequency', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('uom', function ($query) use ($search) {
                        $query->where('uom_shortcode', 'like', '%' . $search . '%');
                        $query->orWhere('uom_text', 'like', '%' . $search . '%');
                    });
            });
        }

        $totalRecords = $rawmaterials->count();

        if (!in_array($columnName, ['serial', 'image', 'actions'])) {
            if ($columnName === 'uom_shortcode') {
                $rawmaterials->join('uom_units', 'materials.uom_id', '=', 'uom_units.uom_id')
                    ->orderBy('uom_units.uom_shortcode', $columnSortOrder);
            } else if ($columnName === 'commodity_name') {
                $rawmaterials->join('commodities', 'materials.commodity_id', '=', 'commodities.commodity_id')
                    ->orderBy('commodities.commodity_name', $columnSortOrder);
            } else if ($columnName === 'category_name') {
                $rawmaterials->join('categories', 'materials.category_id', '=', 'categories.category_id')
                    ->orderBy('categories.category_name', $columnSortOrder);
            } else if ($columnName === 'dependent') {
                $rawmaterials->leftJoin('dependent_materials', 'materials.dm_id', '=', 'dependent_materials.dm_id')
                    ->orderBy('dependent_materials.description', $columnSortOrder);
            } else if ($columnName === 'frequency') {
                $rawmaterials->leftJoin('dependent_materials', 'materials.dm_id', '=', 'dependent_materials.dm_id')
                    ->orderBy('dependent_materials.frequency', $columnSortOrder);
            } else {
                $rawmaterials->orderBy($columnName, $columnSortOrder);
            }
        }

        $materials = $rawmaterials->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));

        $data = [];
        foreach ($materials as $index => $material) {

            $currentPage = ($start / $length) + 1;
            $serial = ($currentPage - 1) * $length + $index + 1;

            $imageAttachment = $material->attachments()->where('type', 'image')->first();
            if ($imageAttachment) {
                $image = '<div class="text-center"><img src="' . asset('assets/uploads/materials/' . $imageAttachment->path) . '" class="mt-2" width="15px" height="15px"></div>';
            } else {
                $image = '<div class="text-center"><img src="' . asset('assets/img/default-image.jpg') . '" class="mt-2" width="15px" height="15px"></div>';
            }

            $actions = '<div class="text-center"><a href="#" role="button" data-matid="' . $material->material_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalView"><i class="fas fa-eye" data-toggle="tooltip" data-placement="top" title="View"></i></a> / 
                <a href="#" role="button" data-matid="' . $material->material_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalEdit"><i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit"></i></a> /
                <a href="#" role="button" data-matid="' . $material->material_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalClone"><i class="fas fa-copy" data-toggle="tooltip" data-placement="top" title="Clone"></i></a>
                / <form action="' . route('raw.destroy', $material->material_id) . '" method="post" style="display: inline;">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm(\'Are you sure you want to delete this record?\')"><i class="fas fa-trash" data-toggle="tooltip" data-placement="top" title="Delete"></i></button>
                </form></div>';

            $data[] = [
                'serial' => $serial,
                'image' => $image,
                'part_code' => $material->part_code,
                'description' => $material->description,
                'unit' => $material->uom?->uom_shortcode,
                'commodity_name' => $material->commodity->commodity_name,
                'category_name' => $material->category->category_name,
                'make' => $material->make,
                'mpn' => $material->mpn,
                're_order' => $material->re_order,
                'dependent' => $material->dependant?->description,
                'frequency' => $material->dependant?->frequency,
                'actions' => $actions,
            ];
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $materials->total(),
            "data" => $data,
        ];

        return response()->json($response);
    }

    public function add()
    {
        $uom = UomUnit::all();
        $category = Category::all();
        $commodity = Commodity::all();
        $dependents = DependentMaterial::all();
        return view('new-raw-material', compact('uom', 'category', 'commodity', 'dependents'));
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
            'dm_id' => 'required|exists:dependent_materials,dm_id',
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
            if (!empty ($validatedData['vendor'][$i])) {
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

        $this->updatePrices($rawMaterial->material_id);
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
        $dependents = DependentMaterial::all();

        $material = $material->fresh();
        $attachments = $material->attachments()->get();
        $uom = $material->uom()->first();
        $commodity = $material->commodity()->first();
        $category = $material->category()->first();
        $dependent = $material->dependant()->first();
        $purchases = $material->purchases()->with('vendor')->get();

        $stock = Stock::where('material_id', $material->material_id)->first();

        $context = [
            'material' => $material,
            'attachments' => $attachments,
            'commodity' => $commodity,
            'category' => $category,
            'dependent' => $dependent,
            'uom' => $uom,
            'uoms' => $uoms,
            'categories' => $categories,
            'commodities' => $commodities,
            'dependents' => $dependents,
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
        $dm = $material->dependant()->first();

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
            'dm' => $dm
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
            'dm_id' => 'required',
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
            $this->updatePrices($material->material_id);
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
                        if (!empty ($vendor)) {
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
                ->latest('created_at')
                ->first();
            $lastPartCode = $lastMaterial ? substr($lastMaterial->part_code, -5) + 1 : 1;
            do {
                $newPartCode = $commodityCode . $categoryCode . str_pad($lastPartCode, 5, '0', STR_PAD_LEFT);
                $exists = RawMaterial::where('part_code', $newPartCode)->exists();
                if ($exists) {
                    $lastPartCode++;
                }
            } while ($exists);
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

    public function priceList()
    {
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

        $query = Material::query()->where('type', 'raw')->with(['category', 'commodity']);

        if (!empty ($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('part_code', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('min_price', 'like', '%' . $search . '%')
                    ->orWhere('avg_price', 'like', '%' . $search . '%')
                    ->orWhere('max_price', 'like', '%' . $search . '%')
                    ->orWhere('make', 'like', '%' . $search . '%')
                    ->orWhere('mpn', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('category_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('commodity', function ($query) use ($search) {
                        $query->where('commodity_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('uom', function ($query) use ($search) {
                        $query->where('uom_shortcode', 'like', '%' . $search . '%');
                    });
            });
        }

        $totalRecords = $query->count();

        if (!in_array($columnName, ['serial',])) {

            if ($columnName === 'uom_shortcode') {
                $query->join('uom_units', 'uom_units.uom_id', '=', 'materials.uom_id')
                    ->orderBy('uom_units.uom_shortcode', $columnSortOrder);
            } elseif ($columnName === 'commodity_name') {
                $query->join('commodities', 'commodities.commodity_id', '=', 'materials.commodity_id')
                    ->orderBy('commodities.commodity_name', $columnSortOrder);
            } elseif ($columnName === 'category_name') {
                $query->join('categories', 'categories.category_id', '=', 'materials.category_id')
                    ->orderBy('categories.category_name', $columnSortOrder);
            } else {
                $query->orderBy($columnName, $columnSortOrder);
            }
        }

        if ($length == -1) {
            $items = $query->get();
            $total = count($items);
        } else {
            $materials = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
            $items = $materials->items();
            $total = $materials->total();
        }
        
        $data = [];

        foreach ($items as $index => $item) {

            $data[] = [
                'serial' => $index + 1,
                'part_code' => $item->part_code,
                'description' => $item->description,
                'commodity' => $item->commodity->commodity_name,
                'category' => $item->category->category_name,
                'make' => $item->make,
                'mpn' => $item->mpn,
                'uom_shortcode' => $item->uom->uom_shortcode,
                'price_1' => number_format($item->min_price, 2),
                'price_2' => number_format($item->avg_price, 2),
                'price_3' => number_format($item->max_price, 2),
            ];
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $total,
            "data" => $data,
        ];

        return response()->json($response);
    }

    public function materialList()
    {
        return view('reports.material-master-list');
    }

    public function fetchMaterialList(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $query = Material::query()->where('type', 'raw')->with(['category', 'commodity', 'uom']);

        if (!empty ($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('part_code', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('make', 'like', '%' . $search . '%')
                    ->orWhere('mpn', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('category_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('commodity', function ($query) use ($search) {
                        $query->where('commodity_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('uom', function ($query) use ($search) {
                        $query->where('uom_text', 'like', '%' . $search . '%');
                        $query->orWhere('uom_shortcode', 'like', '%' . $search . '%');
                    });

                $query->orWhere(function ($query) use ($search) {
                    $query->whereHas('purchases', function ($query) use ($search) {
                        $query->whereHas('vendor', function ($query) use ($search) {
                            $query->where('vendor_name', 'like', '%' . $search . '%');
                        });
                    });
                });
            });
        }

        $totalRecords = $query->count();

        if (!in_array($columnName, ['serial', 'vendor_1', 'vendor_2', 'vendor_3'])) {
            if ($columnName === 'uom_shortcode') {
                $material->join('uom_units', 'materials.uom_id', '=', 'uom_units.uom_id')
                    ->orderBy('uom_units.uom_shortcode', $columnSortOrder);
            } else if ($columnName === 'commodity') {
                $material->join('commodities', 'materials.commodity_id', '=', 'commodities.commodity_id')
                    ->orderBy('commodities.commodity_name', $columnSortOrder);
            } else if ($columnName === 'category') {
                $material->join('categories', 'materials.category_id', '=', 'categories.category_id')
                    ->orderBy('categories.category_name', $columnSortOrder);
            } else {
                $query->orderBy($columnName, $columnSortOrder);
            }
            
        }

        $materials = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
        $data = [];

        foreach ($materials->items() as $index => $item) {
            $vendorList = MaterialPurchase::with('vendor')
                ->where('material_id', $item->material_id)
                ->limit(3)
                ->get();

            $vendorArr = [];

            for ($i = 0; $i < 3; $i++) {
                $temp['vendor_' . ($i + 1)] = $vendorList[$i]->vendor->vendor_name ?? null;
                $vendorArr[] = $temp;
            }

            $dummy = [
                'serial' => $index + 1,
                'part_code' => $item->part_code,
                'description' => $item->description,
                'commodity' => $item->commodity->commodity_name,
                'category' => $item->category->category_name,
                'make' => $item->make,
                'mpn' => $item->mpn,
                'uom' => $item->uom->uom_shortcode,
            ];

            $data[] = array_merge($dummy, ...$vendorArr);
        }

        if (in_array($columnName, ['vendor_1', 'vendor_2', 'vendor_3'])) {
            usort($data, function($a, $b) use ($columnName, $columnSortOrder) {
                if ($columnSortOrder === 'asc') {
                    return strcmp($a[$columnName], $b[$columnName]);
                } else {
                    return strcmp($b[$columnName], $a[$columnName]);
                }
            });
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $materials->total(),
            "data" => $data,
        ];

        return response()->json($response);
    }

    public function rmPurchaseReport()
    {
        return view('reports.rm-purchase');
    }

    public function rmIssuanceReport()
    {
        return view('reports.rm-issue');
    }

    public function fetchPurchaseList(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $query = Material::query()->where('type', 'raw')
        ->with(['category', 'commodity'])
        ->join('warehouse_records', 'materials.material_id', '=', 'warehouse_records.material_id');

        if ($request->input('type') == 'issued') {
            $query->where('warehouse_type', 'issued');
        } else {
            $query->where('warehouse_type', 'received');
        }
        

        if (!empty ($request->searchTerm)) {
            $query->where('part_code', 'like', '%' . $request->searchTerm . '%');
        }

        if (!empty ($request->startDate) && !empty ($request->endDate)) {
            $query->whereBetween('warehouse_records.created_at', [$request->startDate, $request->endDate]);
        }

        if (!empty ($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('part_code', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('category_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('commodity', function ($query) use ($search) {
                        $query->where('commodity_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('warehouse_records', function ($query) use ($search) {
                        $query->where('record_date', 'like', '%' . date('Y-m-d',strtotime($search)) . '%');
                    });
            });
        }

        if (!in_array($columnName, ['serial', 'image', 'actions'])) {
            if ($columnName === 'uom_shortcode') {
                $rawmaterials->join('uom_units', 'materials.uom_id', '=', 'uom_units.uom_id')
                    ->orderBy('uom_units.uom_shortcode', $columnSortOrder);
            } else if ($columnName === 'commodity_name') {
                $rawmaterials->join('commodities', 'materials.commodity_id', '=', 'commodities.commodity_id')
                    ->orderBy('commodities.commodity_name', $columnSortOrder);
            } else if ($columnName === 'category_name') {
                $rawmaterials->join('categories', 'materials.category_id', '=', 'categories.category_id')
                    ->orderBy('categories.category_name', $columnSortOrder);
            } else {
                $rawmaterials->orderBy($columnName, $columnSortOrder);
            }
        }

        $totalRecords = $query->count();

        if ($length == -1) {
            $materials = $query->get();
        } else {
            $materials = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
        }

        $data = [];

        foreach ($materials as $index => $item) {

            $data[] = [
                'serial' => $index + 1,
                'part_code' => $item->part_code,
                'description' => $item->description,
                'commodity' => $item->commodity->commodity_name,
                'category' => $item->category->category_name,
                'unit' => $item->uom->uom_shortcode,
                'receipt_date' => $item->record_date,
                'quantity' => $item->quantity,
                'price_3' => $item->avg_price,
                'amount' => number_format($item->avg_price * $item->quantity,2),
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

    public function stockReport()
    {
        return view('reports.rm-stock');
    }

    public function fetchRmStockList(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];
        
        $material = Material::query()->where('type', 'raw')->with(['category', 'commodity', 'uom']);
        $material->join('stocks', 'materials.material_id', '=', 'stocks.material_id');

        if (!empty ($search)) {
            $material->where(function ($query) use ($search) {
                $query->where('part_code', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('make', 'like', '%' . $search . '%')
                    ->orWhere('mpn', 'like', '%' . $search . '%')
                    ->orWhere('stock', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('category_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('commodity', function ($query) use ($search) {
                        $query->where('commodity_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('uom', function ($query) use ($search) {
                        $query->where('uom_text', 'like', '%' . $search . '%');
                        $query->orWhere('uom_shortcode', 'like', '%' . $search . '%');
                    });
            });
        }

        $totalRecords = $material->count();

        if (!in_array($columnName, ['serial'])) {
            if ($columnName === 'uom_shortcode') {
                $material->join('uom_units', 'materials.uom_id', '=', 'uom_units.uom_id')
                    ->orderBy('uom_units.uom_shortcode', $columnSortOrder);
            } else if ($columnName === 'commodity') {
                $material->join('commodities', 'materials.commodity_id', '=', 'commodities.commodity_id')
                    ->orderBy('commodities.commodity_name', $columnSortOrder);
            } else if ($columnName === 'category') {
                $material->join('categories', 'materials.category_id', '=', 'categories.category_id')
                    ->orderBy('categories.category_name', $columnSortOrder);
            } else if ($columnName === 'stock') {
                $material->join('stocks', 'materials.material_id', '=', 'stocks.material_id')
                    ->orderBy('stocks.closing_balance', $columnSortOrder);
            } else {
                $material->orderBy($columnName, $columnSortOrder);
            }
            
        }

        if ($length == -1) {
            $materials = $material->get();
        } else {
            $materials = $material->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
        }
        
        
        $data = [];

        foreach ($materials as $index => $item) {

            if ($item?->stock && $item?->re_order && $item?->stock?->closing_balance < $item?->re_order) {
                $rostatus = "<span class='text-danger font-weight-bold'>Required</span>";
            } else {
                $rostatus = "";
            }

            $data[] = [
                'serial' => $index + 1,
                'part_code' => $item->part_code,
                'description' => $item->description,
                'commodity' => $item->commodity->commodity_name,
                'category' => $item->category->category_name,
                'make' => $item->make,
                'mpn' => $item->mpn,
                'uom' => $item->uom?->uom_shortcode,
                'stock' => $item->stock?->closing_balance,
                'reorder_qty' => $item->re_order,
                'reorder' => $rostatus,
            ];
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $data,
        ];

        return response()->json($response);
    }

    private function updatePrices($material_id='')
    {
        if (empty($material_id)) { return false; }
        $material = Material::find($material_id);
        $existingPrices = $material->purchases()->pluck('price')->toArray();

        if (!empty($existingPrices)) {
            // Calculate average price
            $avgPrice = array_sum($existingPrices) / count($existingPrices);
            $material->avg_price = $avgPrice;

            // Update lowest price
            $lowestPrice = min($existingPrices);
            $material->min_price = $lowestPrice;

            // Update highest price
            $highestPrice = max($existingPrices);
            $material->max_price = $highestPrice;

            // Save the changes
            $material->save();
        }
        return true;
    }

}
