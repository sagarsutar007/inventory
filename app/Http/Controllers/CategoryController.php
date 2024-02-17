<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Imports\ExcelImportClass;
use Illuminate\Support\Facades\Auth;

use Excel;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount([
            'materials as raw_count' => function ($query) {
                $query->where('type', 'raw');
            },
            'materials as semi_finished_count' => function ($query) {
                $query->where('type', 'semi-finished');
            },
            'materials as finished_count' => function ($query) {
                $query->where('type', 'finished');
            }
        ])->orderBy('category_number', 'desc')->get();
        return view('categories', compact('categories'));
    }

    public function add()
    {
        return view('new-category');
    }

    public function bulk()
    {
        return view('bulk-category');
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
        
        $file = $request->file('file');

        $import =  new ExcelImportClass('category', Auth::id());
        Excel::import($import, $file);

        $importedRows = $import->getImportedCount();
 
        return redirect()->back()->with('success', $importedRows . ' records imported successfully!');
    }

    public function store(Request $request)
    {

        $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'required|string',
        ],[
            'categories.*.required' => 'Enter Category name or remove the blank field.',
        ]);

        $categoryData = $request->input('categories');
        $code = $this->getNextCategoryCode();
        
        foreach ($categoryData as $categoryName) {
            Category::create([
                'category_name' => $categoryName,
                'category_number' => $code,
            ]);
            $code++;
        }

        return redirect()->route('categories.add')->with('success', 'Categories added successfully');
    }

    public function edit(Category $category)
    {
        if (!$category) {
            return response()->json(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['category' => $category]);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
        ]);

        $category->update([
            'category_name' => $request->input('category_name'),
        ]);

        return response()->json(['message' => 'Category updated successfully']);
    }


    protected function getNextCategoryCode()
    {
        $category = Category::orderBy('category_number', 'desc')->first();
        if ($category) {
            $categoryNumber = $category->category_number + 1;
        } else {
            $categoryNumber = 10;
        }
        return $categoryNumber;
    }

    public function destroy(Category $category)
    {
        if ($category->materials()->exists()) {
            return redirect()->route('categories')->with('error', 'Category cannot be deleted because it is in use');
        }

        $category->delete();

        return redirect()->route('categories')->with('success', 'Category deleted successfully');
    }

    public function save(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|unique:categories,category_name',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false, 'errors' => $validator->errors()], 422);
        }

        $code = $this->getNextCategoryCode();
        
        $category = new Category();
        $category->category_name = $request->category_name;
        $category->category_number = $code;
        $category->created_by = Auth::id();
        $category->save();

        $output = [
            'id' => $category->category_id,
            'text' => $category->category_name,
        ];

        return response()->json(['status'=>true, 'message' => 'Category created successfully', 'category' => $output], 200);
    }

    public function search(Request $request) 
    {
        $searchTerm = $request->input('q');

        $categories = Category::select('category_id as id', 'category_name as text')
        ->when($searchTerm, function ($query) use ($searchTerm) {
            $query->where('category_name', 'like', '%' . $searchTerm . '%')
                ->orWhere('category_number', 'like', '%' . $searchTerm . '%');
        })
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

        return response()->json([
            'status'=>true, 
            'message' => 'Categories fetched successfully!', 
            'results' => $categories
        ], 200);
    }
}
