<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


class SettingsController extends Controller
{
    public function index()
    {
        if (Gate::allows('admin', Auth::user())) {
            return view('settings.index');
        } else {
            abort(403);
        }
    }
}
