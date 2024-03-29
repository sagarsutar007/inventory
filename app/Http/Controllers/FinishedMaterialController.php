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
// use App\Models\MaterialPurchase;
use App\Models\UomUnit;
use App\Models\Bom;
use App\Models\BomRecord;
use App\Models\Stock;

class FinishedMaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $materials = Material::with('uom', 'commodity', 'category')->where('type', 'finished')->orderBy('created_at', 'desc')->get();
        return view('finished-goods.finished-materials', compact('materials'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $uom = UomUnit::all();
        $category = Category::all();
        $commodity = Commodity::all();

        return view('finished-goods.new-finished-material', compact('uom', 'category', 'commodity'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'part_code' => 'required|string|unique:materials,part_code,NULL,material_id,type,finished',
            'description' => 'required|string|unique:materials,description,NULL,material_id,type,finished',
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
        // $newPartCode = 'FG'.$this->generatePartCode($validatedData['commodity_id'], $validatedData['category_id']);
        $material->type = "finished";
        $material->created_by = Auth::id();
        $material->updated_by = Auth::id();
        // $material->opening_balance = 0;
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
                if (!empty ($rawMaterialId)) {
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

        return redirect()->route('finished')->with('success', 'Material added successfully.');
    }

    public function checkPartcode(Request $request)
    {
        $partCode = $request->input('part_code');
        $existingMaterial = Material::where('part_code', $partCode)->where('type', 'finished')->first();
        if ($existingMaterial) {
            return response()->json(['exists' => true]);
        } else {
            return response()->json(['exists' => false]);
        }
    }


    public function suggestPartcode(Request $request)
    {
        $commodityId = $request->input('commodity_id');
        $categoryId = $request->input('category_id');
        $suggestedPartCode = 'FG' . $this->generatePartCode($commodityId, $categoryId);
        $existingMaterial = Material::where('part_code', $suggestedPartCode)->where('type', 'finished')->first();

        if ($existingMaterial) {
            $lastChar = substr($suggestedPartCode, -1);
            if (is_numeric($lastChar)) {
                $lastDigit = intval($lastChar);
                $suggestedPartCode = substr($suggestedPartCode, 0, -1) . ($lastDigit + 1);
            } else {
                $suggestedPartCode .= '_1';
            }
        }

        return response()->json(['suggested_part_code' => $suggestedPartCode]);
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

        $returnHTML = view('finished-goods.view-finished-material', $context)->render();
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

        $stock = Stock::where('material_id', $material->material_id)->first();

        if ($material->bom) {
            $bomRecords = $material->bom->bomRecords;
        } else {
            $bomRecords = [];
        }

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

        $returnHTML = view('finished-goods.edit-finished-material', $context)->render();
        return response()->json(array('status' => true, 'html' => $returnHTML));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Material $material)
    {
        try {
            $validatedData = $request->validate([
                'description' => 'required|string',
                'uom_id' => 'required|exists:uom_units,uom_id',
                'commodity_id' => 'required|exists:commodities,commodity_id',
                'category_id' => 'required|exists:categories,category_id',
                'additional_notes' => 'nullable|string',
                'opening_balance' => 'required',
                're_order' => 'nullable',
                'raw' => 'nullable|array',
                'raw.*' => 'nullable|string',
                'quantity' => 'nullable|array',
                'quantity.*' => 'nullable|numeric',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }

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
                        if (!empty ($rawMaterialId)) {

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
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'Materials and quantities count mismatch'], 400);
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
            return response()->json(['status' => true, 'message' => 'Finished Material updated successfully'], 200);
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
            $bom = $material->bom;
            if ($bom) {
                $bom->delete();
            }

            $material->purchases()->delete();
            $material->attachments()->delete();
            $material->delete();
            return redirect()->route('finished')->with('success', 'Finished Material deleted successfully');
        } catch (\Exception $e) {
            \Log::error('Error deleting material: ' . $e->getMessage());
            return redirect()->route('finished')->with('error', 'An error occurred while deleting the Material');
        }
    }

    public function getMaterials(Request $request)
    {
        $searchTerm = $request->input('q');
        $selectedValues = $request->input('selected_values', []);

        $selectedValues = array_filter($selectedValues, function ($value) {
            return $value !== null;
        });

        $query = RawMaterial::with('uom');

        if (!empty($searchTerm)) {
            $query->where(function ($query) use ($searchTerm) {
                $query->where('description', 'like', '%' . $searchTerm . '%')
                    ->orWhere('part_code', 'like', '%' . $searchTerm . '%');
            });
        }

        $query->whereIn('type', ['raw', 'semi-finished'])
            ->orderBy('description')
            ->limit(10);

        if (!empty($selectedValues)) {
            $query->whereNotIn('material_id', $selectedValues);
        }

        $materials = $query->get();

        $materials = $materials->map(function ($material) {
            return [
                'material_id' => $material->material_id,
                'description' => $material->description,
                'part_code' => $material->part_code,
                'uom_shortcode' => $material->uom->uom_shortcode ?? null
            ];
        });

        return response()->json($materials);
    }



    private function generatePartCode($commodity_id = '', $category_id = '')
    {
        if ($commodity_id && $category_id) {
            $commodityCode = str_pad(Commodity::find($commodity_id)->commodity_number, 2, '0', STR_PAD_LEFT);
            $categoryCode = str_pad(Category::find($category_id)->category_number, 3, '0', STR_PAD_LEFT);
            $lastMaterial = Material::where('type', '=', 'finished')->latest()->first();
            $lastPartCode = $lastMaterial ? substr($lastMaterial->part_code, -5) + 1 : 1;
            $newPartCode = $commodityCode . $categoryCode . str_pad($lastPartCode, 5, '0', STR_PAD_LEFT);
            return $newPartCode;
        }
        return null;
    }
}
