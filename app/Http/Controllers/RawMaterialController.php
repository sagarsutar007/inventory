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
use App\Models\BomRecord;

use App\Imports\ExcelImportClass;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Excel;

class RawMaterialController extends Controller
{
    public function index()
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('view-raw-materials', Auth::user())) {
            return view('rawmaterials');
        } else {
            abort(403);
        }
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

        if ($length == -1) {
            $materials = $rawmaterials->get();
        } else {
            $materials = $rawmaterials->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
        }

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

            if ( Gate::allows('admin', Auth::user()) || Gate::allows('view-raw-materials', Auth::user())) {
                $actions = '<div class="text-center"> <a href="#" role="button" data-matid="' . $material->material_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalView"><i class="fas fa-eye" data-toggle="tooltip" data-placement="top" title="View"></i></a>';
            }

            if ( Gate::allows('admin', Auth::user()) || Gate::allows('edit-raw-material', Auth::user())) {
                $actions .= ' / <a href="#" role="button" data-matid="' . $material->material_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalEdit"><i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit"></i></a>';
            }

            if ( Gate::allows('admin', Auth::user()) || Gate::allows('clone-raw-material', Auth::user())) {
                $actions .= ' / <a href="#" role="button" data-matid="' . $material->material_id . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalClone"><i class="fas fa-copy" data-toggle="tooltip" data-placement="top" title="Clone"></i></a>';
            }

            if ( Gate::allows('admin', Auth::user()) || Gate::allows('delete-raw-material', Auth::user())) {
                $actions .= ' / <form action="' . route('raw.destroy', $material->material_id) . '" method="post" style="display: inline;">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm(\'Are you sure you want to delete this record?\')"><i class="fas fa-trash" data-toggle="tooltip" data-placement="top" title="Delete"></i></button>
                </form></div>';
            }

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
                're_order' => formatQuantity($material->re_order),
                'dependent' => $material->dependant?->description,
                'frequency' => $material->dependant?->frequency,
                'actions' => $actions,
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

    public function add()
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('add-raw-material', Auth::user())) {
            $uom = UomUnit::all();
            $category = Category::all();
            $commodity = Commodity::all();
            $dependents = DependentMaterial::all();
            return view('new-raw-material', compact('uom', 'category', 'commodity', 'dependents'));
        } else {
            abort(403);
        }
    }

    public function bulk()
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('add-raw-material', Auth::user())) {
            return view('bulk-raw-material');
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
        $import = new ExcelImportClass('raw-material', Auth::id());
        Excel::import($import, $file);

        $importedRows = $import->getImportedCount();

        $warnings = $import->getErrorMessages();

        if ( $warnings ) {
            return redirect()->back()->with('warnings', $warnings);
        }

        $notices = $import->getNotices();

        if (!empty($notices)) {
            foreach ($notices as $notice) {
                session()->flash('notice', $notice['message']);
            }
        }

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
        $notices = [];

        for ($i = 0; $i < count($validatedData['vendor']); $i++) {
            if (!empty ($validatedData['vendor'][$i])) {

                $vendor = Vendor::where('vendor_name', 'like', $validatedData['vendor'][$i])->first();

                if ($vendor) {
                    MaterialPurchase::create([
                        'material_id' => $rawMaterial->material_id,
                        'vendor_id' => $vendor->vendor_id,
                        'price' => $validatedData['price'][$i],
                    ]);
                } else {
                    if ( Gate::allows('admin', Auth::user()) || Gate::allows('add-vendor', Auth::user()) ) {

                        $vendor = Vendor::create([
                            'vendor_name' => $validatedData['vendor'][$i],
                            'vendor_id' => Str::uuid(),
                            'created_at' => Carbon::now()
                        ]);

                        MaterialPurchase::create([
                            'material_id' => $rawMaterial->material_id,
                            'vendor_id' => $vendor->vendor_id,
                            'price' => $validatedData['price'][$i],
                        ]);
                    } else {
                        $notices[] = [ 'message' => $validatedData['vendor'][$i] . " couldn't be created due to insufficient permission."];
                    }
                }
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
            $response = ['success' => true, 'message' => 'Raw material added successfully.'];
            if (!empty($notices)) {
                $response['notices'] = $notices;
            }
            return response()->json($response, 200);
        } else {
            $successMessage = 'Raw material added successfully.';
            if (!empty($notices)) {
                foreach ($notices as $notice) {
                    session()->flash('notice', $notice['message']);
                }
            }
            return redirect()->route('raw')->with('success', $successMessage);
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
        $used = [];
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

        $records = BomRecord::with('bom')->where('material_id', '=', $material->material_id)->get();
        foreach ($records as $key => $obj) {
            $temp['part_code'] = $obj->bom->material->part_code;
            $temp['description'] = $obj->bom->material->description;
            $temp['type'] = $obj->bom->material->type;
            $temp['category'] = $obj->bom->material->category->category_name;
            $temp['commodity'] = $obj->bom->material->commodity->commodity_name;
            $temp['make'] = $obj->bom->material->commodity->make;
            $temp['mpn'] = $obj->bom->material->commodity->mpn;
            $temp['stock'] = $obj->bom->material->stock->closing_balance;
            $temp['quantity'] = formatQuantity($obj->quantity);
            $temp['unit'] = $obj->bom->material->uom->uom_shortcode;
            $used[] = $temp;
        }

        $reserved = DB::select('CALL get_production_orders_by_material_id(?)', [$material->material_id]);
        $reserved = json_decode(json_encode($reserved), true);
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
            'dm' => $dm,
            'used' => $used,
            'reserved' => $reserved,
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
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('delete-raw-material', Auth::user())) {
            try {
                $material->purchases()->delete();
                $material->attachments()->delete();
                $material->delete();
                return redirect()->route('raw')->with('success', 'Raw Material deleted successfully');
            } catch (\Exception $e) {
                \Log::error('Error deleting raw material: ' . $e->getMessage());
                return redirect()->route('raw')->with('error', 'An error occurred while deleting the Raw Material');
            }
        } else {
            abort(403);
        }
    }

    public function priceList()
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('view-rm-price', Auth::user())) {
            return view('reports.rm-price-list');
        } else {
            abort(403);
        }
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
                'uom_shortcode' => $item->uom?->uom_shortcode,
                'price_1' => formatQuantity($item->min_price),
                'price_2' => formatQuantity($item->avg_price),
                'price_3' => formatQuantity($item->max_price),
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
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('view-material-master', Auth::user())) {
            return view('reports.material-master-list');
        } else {
            abort(403);
        }
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
                $query->join('uom_units', 'materials.uom_id', '=', 'uom_units.uom_id')
                    ->orderBy('uom_units.uom_shortcode', $columnSortOrder);
            } else if ($columnName === 'commodity') {
                $query->join('commodities', 'materials.commodity_id', '=', 'commodities.commodity_id')
                    ->orderBy('commodities.commodity_name', $columnSortOrder);
            } else if ($columnName === 'category') {
                $query->join('categories', 'materials.category_id', '=', 'categories.category_id')
                    ->orderBy('categories.category_name', $columnSortOrder);
            } else {
                $query->orderBy($columnName, $columnSortOrder);
            }

        }

        if ($length == -1) {
            $result = $query->get();
        } else {
            $materials = $query->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
            $result = $materials->items();
        }

        $data = [];

        foreach ($result as $index => $item) {
            $serial = $start + $index + 1;

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
                'serial' => $serial,
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
            "recordsFiltered" => $totalRecords,
            "data" => $data,
        ];

        return response()->json($response);
    }

    public function rmPurchaseReport()
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('view-raw-pur', Auth::user())) {
            return view('reports.rm-purchase');
        } else {
            abort(403);
        }
    }

    public function rmIssuanceReport()
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('view-raw-issu', Auth::user())) {
            return view('reports.rm-issue');
        } else {
            abort(403);
        }
    }

    public function fetchPurchaseList(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $order = $request->input('order');
        $mtype = $request->input('mtype');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $query = Material::query()
        ->with(['category', 'commodity'])
        ->join('warehouse_records', 'materials.material_id', '=', 'warehouse_records.material_id')
        ->join('warehouse', 'warehouse.warehouse_id', '=', 'warehouse_records.warehouse_id');

        if (!empty($mtype)) {
            $query->where('materials.type', $mtype);
        }

        if ($request->input('type') == 'issued') {
            $query->where('warehouse_type', 'issued');
        } else {
            $query->whereNot('warehouse_type', 'issued');
        }

        if (!empty ($request->searchTerm)) {
            $query->where('part_code', 'like', '%' . $request->searchTerm . '%');
        }

        if (!empty ($request->startDate) && !empty ($request->endDate)) {
            $query->whereBetween('warehouse_records.record_date', [$request->startDate, $request->endDate]);
        }

        if (!empty ($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('part_code', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('transaction_id', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('category_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('commodity', function ($query) use ($search) {
                        $query->where('commodity_name', 'like', '%' . $search . '%');
                    })
                    ->where('record_date', 'like', '%' . date('Y-m-d',strtotime($search)) . '%');
                    // ->orWhereHas('warehouse_records', function ($query) use ($search) {
                        // $query->where('record_date', 'like', '%' . date('Y-m-d',strtotime($search)) . '%');
                    // });
            });
        }

        if (!in_array($columnName, ['serial', 'image', 'actions'])) {
            if ($columnName === 'unit') {
                $query->join('uom_units', 'materials.uom_id', '=', 'uom_units.uom_id')
                    ->orderBy('uom_units.uom_shortcode', $columnSortOrder);
            } else if ($columnName === 'commodity') {
                $query->join('commodities', 'materials.commodity_id', '=', 'commodities.commodity_id')
                    ->orderBy('commodities.commodity_name', $columnSortOrder);
            } else if ($columnName === 'category') {
                $query->join('categories', 'materials.category_id', '=', 'categories.category_id')
                    ->orderBy('categories.category_name', $columnSortOrder);
            } else if ($columnName === 'receipt_date') {
                $query->orderBy('warehouse_records.record_date', $columnSortOrder);
            } else if ($columnName === 'price_3') {
                $query->orderBy('avg_price', $columnSortOrder);
            } else {
                $query->orderBy($columnName, $columnSortOrder);
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
                'transaction_id' => $item->transaction_id,
                'part_code' => $item->part_code,
                'description' => $item->description,
                'commodity' => $item->commodity->commodity_name,
                'category' => $item->category->category_name,
                'unit' => $item->uom->uom_shortcode,
                'receipt_date' => $item->record_date,
                'quantity' => $item->quantity,
                'price_3' => $item->avg_price,
                'amount' => formatPrice($item->avg_price * $item->quantity),
                'type' => $item->warehouse_type,
                'warehouse_id' => $item->warehouse_id
            ];
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" =>  $data,
        ];

        return response()->json($response);
    }

    public function stockReport()
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('view-raw-stock', Auth::user())) {
            return view('reports.rm-stock');
        } else {
            abort(403);
        }
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

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $searchTerm = $request->input('searchTerm');

        $material = Material::query()
            ->where('type', 'raw')
            ->with(['category', 'commodity', 'uom'])
            ->leftJoin('stocks', 'materials.material_id', '=', 'stocks.material_id');

        if (!empty($searchTerm)) {
            $material
                ->where('part_code', 'like', '%' . $searchTerm . '%')
                ->orWhere('description', 'like', '%' . $searchTerm . '%');
        }

        if (!empty ($search)) {
            $material->where(function ($query) use ($search) {
                $query->where('materials.part_code', 'like', '%' . $search . '%')
                    ->orWhere('materials.description', 'like', '%' . $search . '%')
                    ->orWhere('materials.make', 'like', '%' . $search . '%')
                    ->orWhere('materials.mpn', 'like', '%' . $search . '%')
                    ->orWhere('stocks.closing_balance', 'like', '%' . $search . '%')
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

        if (!in_array($columnName, ['serial', 'issued', 'receipt', 'reorder'])) {
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
                $material->orderBy('stocks.closing_balance', $columnSortOrder);
            } else if ($columnName === 'opening') {
                $material->orderBy('stocks.opening_balance', $columnSortOrder);
            } else if ($columnName === 'reorder_qty') {
                $material->orderBy('re_order', $columnSortOrder);
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

            $calcBals = $this->calcBalances($item->material_id, $startDate, $endDate);

            $data[] = [
                'serial' => $index + 1,
                'part_code' => $item->part_code,
                'description' => $item->description,
                'commodity' => $item->commodity->commodity_name,
                'category' => $item->category->category_name,
                'make' => $item->make,
                'mpn' => $item->mpn,
                'uom' => $item->uom?->uom_shortcode,
                'opening' => formatQuantity( $calcBals['computedOP'] + $item->stock?->opening_balance),
                'issued' => formatQuantity($calcBals['issued']),
                'receipt' => formatQuantity($calcBals['receipt']),
                'stock' => formatQuantity($calcBals['computedOP'] + $item->stock?->opening_balance - $calcBals['issued'] + $calcBals['receipt']),
                'reorder_qty' => formatQuantity($item->re_order),
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

    private function calcBalances($material_id, $startDate, $endDate)
    {
        $result = DB::select("
            SELECT
                    (
                        SELECT SUM(CASE WHEN warehouse_type = 'issued' THEN quantity * -1 ELSE quantity END)
                        FROM warehouse_records
                        WHERE material_id = m.material_id AND record_date < '".$startDate."'
                    ) AS computedOP,
                    (
                        SELECT SUM(CASE WHEN warehouse_type = 'issued' THEN quantity ELSE 0 END)
                        FROM warehouse_records
                        WHERE material_id = m.material_id AND record_date BETWEEN '".$startDate."' AND '".$endDate."'
                    ) AS issued,
                    (
                        SELECT SUM(CASE WHEN warehouse_type != 'issued' THEN quantity ELSE 0 END)
                        FROM warehouse_records
                        WHERE material_id = m.material_id AND record_date BETWEEN '".$startDate."' AND '".$endDate."'
                    ) AS receipt
                FROM
                    materials m
                LEFT OUTER JOIN
                    stocks o ON o.material_id = m.material_id
            WHERE m.material_id = '".$material_id."';
        ");

        $firstResult = $result[0] ?? null;

        if ($firstResult) {
            return [
                'computedOP' => $firstResult->computedOP,
                'issued' => $firstResult->issued,
                'receipt' => $firstResult->receipt,
            ];
        } else {
            return [
                'computedOP' => 0,
                'issued' => 0,
                'receipt' => 0,
            ];
        }
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

    public function vendorPriceList(Request $request)
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('view-raw-vendor-price', Auth::user())) {

            $data = [];
            $maxVendorNum = 0;
            $page = $request->input('page') ?? 1;
            $length = $request->input('length') ?? 10;

            $query = Material::query()->where('type', 'raw')->with(['category', 'commodity']);

            if ($request->has('uom_id') && $request->input('uom_id')[0] != 'all') {
                $query->whereHas('uom', function ($q) use ($request) {
                    $q->whereIn('uom_id', $request->input('uom_id'));
                });
            }

            if ($request->has('commodity_id') && $request->input('commodity_id')[0] != 'all') {
                $query->whereHas('commodity', function ($q) use ($request) {
                    $q->whereIn('commodity_id', $request->input('commodity_id'));
                });
            }

            if ($request->has('category_id') && $request->input('category_id')[0] != 'all') {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->whereIn('category_id', $request->input('category_id'));
                });
            }

            if ($request->has('dm_id') && $request->input('dm_id')[0] != 'all') {
                $query->whereHas('dependant', function ($q) use ($request) {
                    $q->whereIn('dm_id', $request->input('dm_id'));
                });
            }

            if ($request->has('vendor_id') && $request->input('vendor_id')[0] != 'all') {
                $query->whereHas('vendors', function ($q) use ($request) {
                    $q->whereIn('vendors.vendor_id', $request->input('vendor_id'));
                });
            }

            if (!empty($request->input('part_code'))) {
                $query->where('part_code', 'like', '%' . $request->input('part_code') . '%');
            }

            if (!empty($request->input('description'))) {
                $query->where('description', 'like', '%' . $request->input('description') . '%');
            }

            if (!empty($request->input('make'))) {
                $query->where('make', 'like', '%' . $request->input('make') . '%');
            }

            if (!empty($request->input('mpn'))) {
                $query->where('mpn', 'like', '%' . $request->input('mpn') . '%');
            }

            if ($length == -1) {
                $page = 1;
                $items = $query->get();
            } else {
                $materials = $query->paginate($length);
                $items = $materials->items();
            }

            foreach ($items as $index => $item) {

                $purchases = MaterialPurchase::where('material_id', 'like', $item->material_id)->get();
                $purArr = [];
                if ($purchases->isNotEmpty()) {
                    $integ = 0;
                    foreach ($purchases as $purchase => $pur) {
                        $purArr['vendor_' . $purchase + 1 ] = $pur->vendor->vendor_name;
                        $purArr['price_' . $purchase + 1 ] = formatPrice($pur->price);
                        $integ++;
                    }

                    if ($maxVendorNum < $integ) {
                        $maxVendorNum = $integ;
                    }
                }else {
                    $purArr = [
                        'vendor_1' => null,
                        'price_1' => null,
                        'vendor_2' => null,
                        'price_2' => null,
                        'vendor_3' => null,
                        'price_3' => null,
                    ];
                }

                $data[] = array_merge([
                    'serial' => $index + 1,
                    'part_code' => $item->part_code,
                    'description' => $item->description,
                    'material_id' => $item->material_id,
                    'commodity' => $item->commodity->commodity_name,
                    'category' => $item->category->category_name,
                    'make' => $item->make,
                    'mpn' => $item->mpn,
                    'uom_shortcode' => $item->uom?->uom_shortcode,
                    'dependent' => $item->dependant?->description,
                    'frequency' => $item->dependant?->frequency,
                ], $purArr);
            }

            $currentUrl = url()->current();
            $queryString = $request->getQueryString();

            if ($queryString) {
                $currentUrl .= '?' . $queryString;
            }


            $context = [
                "data" => $data,
                "columnsCount" => $maxVendorNum,
                "page" => $page,
                "length" => $length,
                'currentUrl' => $currentUrl,
                'uom' => UomUnit::all(),
                'category' => Category::all(),
                'commodity' => Commodity::all(),
                'dependents' => DependentMaterial::all(),
                'vendors' => Vendor::all(),
            ];

            if (isset($materials)) {
                $context['materials'] = $materials;
            }

            return view('raw-material-vendor-price', $context);
        } else {
            abort(403);
        }
    }

    public function bulkPrice()
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('import-raw-vendor-price', Auth::user())) {
            return view('bulk-material-price-list');
        } else {
            abort(403);
        }
    }

    public function bulkPriceStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        $import = new ExcelImportClass('rm-price', Auth::id());
        Excel::import($import, $file);

        $importedRows = $import->getImportedCount();

        $warnings = $import->getErrorMessages();

        if ( $warnings ) {
            return redirect()->back()->with('warnings', $warnings);
        }

        $notices = $import->getNotices();

        if (!empty($notices)) {
            foreach ($notices as $notice) {
                session()->flash('notice', $notice['message']);
            }
        }

        return redirect()->back()->with('success', $importedRows . ' records imported successfully!');
    }

    public function whereUsed()
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('view-material-master', Auth::user())) {
            return view('reports.where-used');
        } else {
            abort(403);
        }
    }

    public function getMaterialsFromRaw(Request $request)
    {
        $partCodes = $request->input('part_code');

        if (count($partCodes)) {
            $materials=[];
            foreach ($partCodes as $partCode => $pc) {
                $mat = Material::where('part_code', '=', $pc)->first();
                $records = BomRecord::with('bom')->where('material_id', '=', $mat->material_id)->get();
                foreach ($records as $key => $obj) {
                    $temp['part_code'] = $obj->bom->material->part_code;
                    $temp['description'] = $obj->bom->material->description;
                    $temp['type'] = $obj->bom->material->type;
                    $temp['category'] = $obj->bom->material->category->category_name;
                    $temp['commodity'] = $obj->bom->material->commodity->commodity_name;
                    $temp['make'] = $obj->bom->material->commodity->make;
                    $temp['mpn'] = $obj->bom->material->commodity->mpn;
                    $temp['stock'] = $obj->bom->material->stock->closing_balance;
                    $temp['quantity'] = formatQuantity($obj->quantity);
                    $temp['unit'] = $obj->bom->material->uom->uom_shortcode;
                    $materials[] = $temp;
                }
            }

            $context = [
                'materials' => $materials,
            ];

            $returnHTML = view('reports.list-material-used', $context)->render();
            return response()->json(array('status' => true, 'html' => $returnHTML));
        } else {
            return response()->json(['status' => false, 'error' => 'Part code is required'], 400);
        }
    }

}
