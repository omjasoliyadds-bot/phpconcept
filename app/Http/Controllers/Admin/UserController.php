<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users');
    }

    public function profile(Request $request)
    {
        $user = auth()->user();
        return view('admin.profile', compact('user'));
    }
}
