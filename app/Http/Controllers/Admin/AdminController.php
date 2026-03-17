<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Document;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::all()->where('role', 'user')->count();
        $documents = Document::all()->count();
        return view('admin.dashboard', compact('users','documents'));
    }

    public function usersView()
    {
        return view('admin.users');
    }
  
}
