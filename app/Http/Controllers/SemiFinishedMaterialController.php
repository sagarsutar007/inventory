<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use App\Models\Category;
use App\Models\Commodity;
use App\Models\RawMaterial;
use App\Models\MaterialAttachments;
use App\Models\MaterialPurchase;
use App\Models\UomUnit;
use App\Models\Bom;
use App\Models\BomRecord;

class SemiFinishedMaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $materials = Material::with('uom', 'commodity', 'category')->where('type', 'semi-finished')->orderBy('created_at', 'desc')->get();
        return view('semi-finished-materials', compact('materials'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $uom = UomUnit::all();
        $category = Category::all();
        $commodity = Commodity::all();
        
        return view('new-semi-material', compact('uom', 'category', 'commodity'));
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
            // 'mpn' => 'nullable',
            'raw' => 'nullable|array',
            'raw.*' => 'nullable|string',
            'quantity' => 'nullable|array',
            'quantity.*' => 'nullable|numeric',
        ]);

        
        $material = new Material($validatedData);
        $newPartCode = 'SF'.$this->generatePartCode($validatedData['commodity_id'], $validatedData['category_id']);
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

        return redirect()->route('semi')->with('success', 'Material added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Material $material)
    {
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
        $context = [
            'material' => $material,
            'attachments' => $attachments,
            'commodity' => $commodity,
            'category' => $category,
            'uom' => $uom,
            'boms' => $bomRecords,
        ];
        
        // return view('view-semi-material', $context);
        $returnHTML = view('view-semi-material', $context)->render();
        return response()->json(array('status' => true, 'html'=>$returnHTML));
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
        
        if ($material->bom) {
            $boms = $material->bom->with('bomRecords')->get();
        } else {
            $boms = null;
        }


        $context = [
            'material' => $material,
            'attachments' => $attachments,
            'commodity' => $commodity,
            'category' => $category,
            'uom' => $uom,
            'boms' => $boms,
            'uoms' => $uoms,
            'categories' => $categories,
            'commodities' => $commodities,
        ];

        $returnHTML = view('edit-semi-material', $context)->render();
        return response()->json(array('status' => true, 'html'=>$returnHTML));
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

                if (count($rawMaterials) === count($quantities)) {
                    foreach ($rawMaterials as $index => $rawMaterialId) {
                        if (!empty($rawMaterialId)) {
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
                } else {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'Raw materials and quantities count mismatch'], 400);
                }
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
        try {
             // Delete related Bom and BomRecord entries
            $material->bom->each(function ($bom) {
                $bom->bomRecords->each(function ($bomRecord) {
                    $bomRecord->delete();
                });
                $bom->delete();
            });

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
    }

    /**
     * Get all the materials based on search criteria and return them as JSON data.
     */
    public function getRawMaterials(Request $request)
    {
        $searchTerm = $request->input('q');

        if (empty($searchTerm)) {
            $materials = RawMaterial::select('material_id', 'description', 'part_code')->limit(10)->get();
        }else {
            $materials = Material::select('material_id', 'description', 'part_code')
            ->where('description', 'like', '%' . $searchTerm . '%')
            ->orWhere('part_code', 'like', '%' . $searchTerm . '%')
            ->where('type', '=', 'raw')
            ->orderBy('description')
            ->get();
        }
        return response()->json($materials);
    }

    private function generatePartCode($commodity_id='', $category_id='')
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
}
