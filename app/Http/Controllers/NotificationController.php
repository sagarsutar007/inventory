<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userName = explode(' ', Auth::user()->name); // Split the full name into parts
        $firstName = $userName[0]; 

        $chatMessage = Notification::create([
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        return response()->json(['success' => true, 'message' => $chatMessage->message, 'name'=> Auth::user()->name, 'picture'=>'//via.placeholder.com/80x80/007bff/ffffff?text='.$firstName], 201);
    }
}
