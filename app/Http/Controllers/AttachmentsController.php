<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\MaterialAttachments;

class AttachmentsController extends Controller
{
    public function destroy(Request $request, MaterialAttachments $attachment)
    {
        try {
            if ($attachment->type == 'doc') {
                @unlink('assets/uploads/doc/' . $attachment->path);
            } else if ($attachment->type == 'image') {
                @unlink('assets/uploads/materials/' . $attachment->path);
            } else if ($attachment->type == 'pdf') {
                @unlink('assets/uploads/pdf/' . $attachment->path);
            }
            
            $attachment->delete();
            return response()->json(['status'=>true, 'message' => 'Material attachment deleted successfully'], 200);
        } catch (\Exception $e) {
            \Log::error('Error deleting material: ' . $e->getMessage());
            return response()->json(['status'=>false, 'message' => 'An error occurred while deleting the material attachment'], 500);
        }
    }
}
