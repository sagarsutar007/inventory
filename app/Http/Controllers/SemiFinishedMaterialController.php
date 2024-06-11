<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use App\Imports\ExcelImportClass;
use Carbon\Carbon;
use Excel;

use App\Models\Category;
use App\Models\Commodity;
use App\Models\RawMaterial;
use App\Models\MaterialAttachments;
use App\Models\MaterialPurchase;
use App\Models\UomUnit;
use App\Models\Bom;
use App\Models\BomRecord;
use App\Models\Stock;

class SemiFinishedMaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $materials = Material::with('uom', 'commodity', 'category')->where('type', 'semi-finished')->orderBy('created_at', 'desc')->get();
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('view-semi-material', Auth::user())) {
            return view('semi-finished-materials');
        } else {
            abort(403);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('add-semi-material', Auth::user())) {
            $uom = UomUnit::all();
            $category = Category::all();
            $commodity = Commodity::all();

            return view('new-semi-material', compact('uom', 'category', 'commodity'));
        } else {
            abort(403);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'description' => 'required|string|unique:materials,description,NULL,material_id,type,semi-finished',
            'uom_id' => 'required|exists:uom_units,uom_id',
            'commodity_id' => 'required|exists:commodities,commodity_id',
            'category_id' => 'required|exists:categories,category_id',
            'additional_notes' => 'nullable|string',
            'opening_balance' => 'required',
            're_order' => 'nullable',
            // 'mpn' => 'nullable',
            'raw' => 'nullable|array',
            'raw.*' => 'nullable|string',
            'quantity' => 'nullable|array',
            'quantity.*' => 'nullable|numeric',
        ]);


        $material = new Material($validatedData);
        $newPartCode = 'SF' . $this->generatePartCode($validatedData['commodity_id'], $validatedData['category_id']);
        $material->type = "semi-finished";
        $material->part_code = $newPartCode;
        $material->created_by = Auth::id();
        $material->updated_by = Auth::id();
        $material->save();

        if ($request->hasFile('photo')) {
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

        // Retrieve the newly created material_id
        $materialId = $material->material_id;

        // Create a record in the bom table
        $bom = new Bom();
        $bom->material_id = $materialId;
        $bom->uom_id = $request->input('uom_id');
        $bom->created_by = Auth::id();
        $bom->updated_by = Auth::id();
        $bom->save();

        $bomId = $bom->bom_id;
        $rawMaterials = $request->input('raw');
        $quantities = $request->input('quantity');

        if (count($rawMaterials) === count($quantities)) {
            // Create records in the bom_records table
            foreach ($rawMaterials as $index => $rawMaterialId) {
                if (!empty($rawMaterialId)) {
                    $bomRecord = new BomRecord();
                    $bomRecord->bom_id = $bomId;
                    $bomRecord->material_id = $rawMaterialId;
                    $bomRecord->quantity = $quantities[$index];
                    $bomRecord->save();
                }
            }
        }

        $stock = new Stock;
        $stock->material_id = $material->material_id;
        $stock->opening_balance = $validatedData['opening_balance'];
        $stock->receipt_qty = 0;
        $stock->issue_qty = 0;
        $stock->created_by = Auth::id();
        $stock->created_at = Carbon::now();
        $stock->save();

        return redirect()->route('semi')->with('success', 'Material added successfully.');
    }

    public function bulk()
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('add-semi-material', Auth::user())) {
            return view('bulk-semi-material');
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
        $import = new ExcelImportClass('semi-material', Auth::id());
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

    /**
     * Display the specified resource.
     */
    public function show(Material $material)
    {
        $used = [];
        $material = $material->fresh();
        $attachments = $material->attachments()->get();
        $uom = $material->uom()->first();
        $commodity = $material->commodity()->first();
        $category = $material->category()->first();
        $materialWithBomRecords = Material::with(['bom.bomRecords'])->find($material->material_id);
        if ($materialWithBomRecords->bom) {
            $bomRecords = $materialWithBomRecords->bom->bomRecords;
        } else {
            $bomRecords = null;
        }

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
        
        $context = [
            'material' => $material,
            'attachments' => $attachments,
            'commodity' => $commodity,
            'category' => $category,
            'uom' => $uom,
            'bomRecords' => $bomRecords,
            'used' => $used
        ];

        // return view('view-semi-material', $context);
        $returnHTML = view('view-semi-material', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Material $material)
    {
        $uoms = UomUnit::all();
        $categories = Category::all();
        $commodities = Commodity::all();

        $material = $material->fresh();
        $attachments = $material->attachments()->get();
        $uom = $material->uom()->first();
        $commodity = $material->commodity()->first();
        $category = $material->category()->first();

        $bomRecords = $material->bom?->bomRecords;

        $stock = Stock::where('material_id', $material->material_id)->first();

        $context = [
            'material' => $material,
            'attachments' => $attachments,
            'commodity' => $commodity,
            'category' => $category,
            'uom' => $uom,
            'bomRecords' => $bomRecords,
            'uoms' => $uoms,
            'categories' => $categories,
            'commodities' => $commodities,
            'stock' => $stock,
        ];

        $returnHTML = view('edit-semi-material', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Material $material)
    {
        $validatedData = $request->validate([
            'description' => 'required|string',
            'uom_id' => 'required|exists:uom_units,uom_id',
            'commodity_id' => 'required|exists:commodities,commodity_id',
            'category_id' => 'required|exists:categories,category_id',
            'additional_notes' => 'nullable|string',
            'opening_balance' => 'required',
            're_order' => 'nullable',
            // 'mpn' => 'nullable',
            'raw' => 'nullable|array',
            'raw.*' => 'nullable|string',
            'quantity' => 'nullable|array',
            'quantity.*' => 'nullable|numeric',
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

            // Update or insert BOM records
            if ($request->has('raw') && $request->has('quantity')) {
                $rawMaterials = $request->input('raw');
                $quantities = $request->input('quantity');

                if (count($rawMaterials) !== count($quantities)) {
                    return response()->json(['status' => false, 'message' => 'Raw materials and quantities count mismatch'], 400);
                }

                foreach ($rawMaterials as $index => $rawMaterialId) {
                    if (!empty($rawMaterialId)) {

                        if ($material->bom === null) {
                            // If Bom doesn't exist for the material, create a new one
                            $bom = new Bom();
                            $bom->material_id = $material->material_id;
                            $bom->uom_id = $material->uom->uom_id;
                            $bom->created_by = Auth::id();
                            $bom->save();
                            $material->bom()->associate($bom);
                            $material->save();
                        }

                        $bomRecord = BomRecord::where('bom_id', $material->bom->bom_id)
                            ->where('material_id', $rawMaterialId)
                            ->first();

                        if ($bomRecord) {
                            $bomRecord->quantity = $quantities[$index];
                            $bomRecord->save();
                        } else {
                            $material->bom->bomRecords()->create([
                                'material_id' => $rawMaterialId,
                                'quantity' => $quantities[$index],
                            ]);
                        }
                    }
                }

                //fetch all bom records and those not in the request should be deleted.
                $deleteBoms = BomRecord::where('bom_id', $material->bom->bom_id)->whereNotIn(
                    'material_id',
                    $rawMaterials
                )->get();
                foreach ($deleteBoms as $delBom) {
                    $delBom->delete();
                }
            } else {
                $material->bom->bomRecords()->delete();
            }

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
            return response()->json(['status' => true, 'message' => 'Semi Finished Material updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'message' => 'Failed to update material. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Material $material)
    {
        if ( Gate::allows('admin', Auth::user()) || Gate::allows('delete-semi-material', Auth::user())) {
            try {
                // Delete related Bom and BomRecord entries
                $material->bom?->bomRecords?->each(function ($bomRecord) {
                    $bomRecord->delete();
                });
                $material->bom?->delete();

                $material->purchases()->delete();
                $material->attachments()->delete();
                $material->delete();
                return redirect()->route('semi')->with('success', 'Material deleted successfully');
            } catch (\Exception $e) {
                // Log the exception
                \Log::error('Error deleting material: ' . $e->getMessage());

                // Add a flash message for the user (optional)
                return redirect()->route('semi')->with('error', 'An error occurred while deleting the Material');
            }
        } else {
            abort(403);
        }
    }

    /**
     * Get all the materials based on search criteria and return them as JSON data.
     */
    public function getRawMaterials(Request $request)
    {
        $searchTerm = $request->input('q');
        $selectedValues = $request->input('selected_values', []);

        $selectedValues = array_filter($selectedValues, function ($value) {
            return $value !== null;
        });

        $query = RawMaterial::select('materials.material_id', 'materials.description', 'materials.part_code')
            ->where('type', '=', 'raw')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('material_purchases')
                    ->whereColumn('material_purchases.material_id', 'materials.material_id');
            });

        if (!empty($searchTerm)) {
            $query->where(function ($subquery) use ($searchTerm) {
                $subquery->where('description', 'like', '%' . $searchTerm . '%')
                    ->orWhere('part_code', 'like', '%' . $searchTerm . '%');
            });
        }

        if (!empty($selectedValues)) {
            $query->whereNotIn('materials.material_id', $selectedValues);
        }

        $materials = $query->orderBy('description')->get();

        return response()->json($materials);
    }

    private function generatePartCode($commodity_id = '', $category_id = '')
    {
        if ($commodity_id && $category_id) {
            $commodityCode = str_pad(Commodity::find($commodity_id)->commodity_number, 2, '0', STR_PAD_LEFT);
            $categoryCode = str_pad(Category::find($category_id)->category_number, 3, '0', STR_PAD_LEFT);
            $lastMaterial = Material::where('type', '=', 'semi-finished')->latest()->first();
            $lastPartCode = $lastMaterial ? substr($lastMaterial->part_code, -5) + 1 : 1;
            $newPartCode = $commodityCode . $categoryCode . str_pad($lastPartCode, 5, '0', STR_PAD_LEFT);
            return $newPartCode;
        }
        return null;

    }

    public function fetchSemiMaterials(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')['value'];

        $order = $request->input('order');
        $columnIndex = $order[0]['column'];
        $columnName = $request->input('columns')[$columnIndex]['name'];
        $columnSortOrder = $order[0]['dir'];

        $semiMaterials = RawMaterial::with('uom', 'commodity', 'category')->where('type', 'semi-finished');

        if (!empty ($search)) {
            $semiMaterials->where(function ($query) use ($search) {
                $query->where('part_code', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('re_order', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('category_name', 'like', '%' . $search . '%');
                        $query->orWhere('category_number', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('commodity', function ($query) use ($search) {
                        $query->where('commodity_name', 'like', '%' . $search . '%');
                        $query->orWhere('commodity_number', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('uom', function ($query) use ($search) {
                        $query->where('uom_shortcode', 'like', '%' . $search . '%');
                        $query->orWhere('uom_text', 'like', '%' . $search . '%');
                    });
            });
        }

        $totalRecords = $semiMaterials->count();

        if (!in_array($columnName, ['serial', 'image', 'actions'])) {
            if ($columnName === 'uom_shortcode') {
                $semiMaterials->join('uom_units', 'materials.uom_id', '=', 'uom_units.uom_id')
                    ->orderBy('uom_units.uom_shortcode', $columnSortOrder);
            } else if ($columnName === 'commodity_name') {
                $semiMaterials->join('commodities', 'materials.commodity_id', '=', 'commodities.commodity_id')
                    ->orderBy('commodities.commodity_name', $columnSortOrder);
            } else if ($columnName === 'category_name') {
                $semiMaterials->join('categories', 'materials.category_id', '=', 'categories.category_id')
                    ->orderBy('categories.category_name', $columnSortOrder);
            } else {
                $semiMaterials->orderBy($columnName, $columnSortOrder);
            }
        }

        if ($length == -1) {
            $materials = $semiMaterials->get();
        } else {
            $materials = $semiMaterials->paginate($length, ['*'], 'page', ceil(($start + 1) / $length));
        }
        
        $data = [];
        foreach ($materials as $index => $material) {

            $currentPage = ($start / $length) + 1;
            $serial = ($currentPage - 1) * $length + $index + 1;

            $imageAttachment = $material->attachments()->where('type', 'image')->first();
            if ($imageAttachment) {
                $image = '<div class="text-center"><img src="' . asset('assets/uploads/materials/' . $imageAttachment->path) . '" class="mt-2" width="30px" height="30px"></div>';
            } else {
                $image = '<div class="text-center"><img src="' . asset('assets/img/default-image.jpg') . '" class="mt-2" width="30px" height="30px"></div>';
            }

            $actions = '<a href="#" role="button" data-matid="' . $material->material_id . '" data-partcode="' . $material->part_code . '" data-desc="' . $material->description . '" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalView"><i class="fas fa-eye" data-toggle="tooltip" data-placement="top" title="View"></i></a>'; 
            
            if ( Gate::allows('admin', Auth::user()) || Gate::allows('edit-semi-material', Auth::user())) {
                $actions .= ' / <a href="#" role="button" data-matid="' . $material->material_id . '"  class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalEdit"><i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit"></i></a>';
            }

            if ( Gate::allows('admin', Auth::user()) || Gate::allows('delete-semi-material', Auth::user())) {
                $actions .= ' / <form action="' . route('semi.destroy', $material->material_id) . '" method="post" style="display: inline;">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm(\'Are you sure you want to delete this record?\')"><i class="fas fa-trash" data-toggle="tooltip" data-placement="top" title="Delete"></i></button>
                </form>';
            }

            if ( Gate::allows('admin', Auth::user()) || Gate::allows('export-semi-material', Auth::user())) {
                $actions .= ' / <button role="button" data-matid="'.$material->material_id.'" class="btn btn-sm btn-link text-success p-0 btn-export-bom"><i class="fas fa-file-excel" data-toggle="tooltip" data-placement="top" title="Export BOM"></i></button>';
            }

            if ( Gate::allows('admin', Auth::user()) || Gate::allows('import-semi-material', Auth::user())) {
                $actions .= ' / <button role="button" data-desc="'. $material->description .'" data-matid="'.$material->material_id.'" data-toggle="modal" data-target="#modalUploadBOM" class="btn btn-sm btn-link text-warning p-0 btn-import-bom"><i class="fas fa-file-excel" data-toggle="tooltip" data-placement="top" title="Import BOM"></i></i></button>';
            }

            $data[] = [
                'serial' => $serial,
                'image' => $image,
                'part_code' => $material->part_code,
                'description' => $material->description,
                'unit' => $material?->uom?->uom_shortcode,
                'make' => $material->make,
                'mpn' => $material->mpn,
                're_order' => formatQuantity($material->re_order),
                'commodity_name' => $material->commodity->commodity_name,
                'category_name' => $material->category->category_name,
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
}
