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
        $totalSize = Document::all()->sum('size');
        $totalSizeMb = number_format(($totalSize / (1024 * 1024)), 2, '.', ' ');
        return view('admin.dashboard', compact('users','documents','totalSizeMb'));
    }

    public function usersView()
    {
        return view('admin.users');
    }
  
    public function profile(Request $request){
        $user =auth()->user();
        return view('admin.profile', compact('user'));
    }
    public function documentsView(){
        return view('admin.document');
    }

    public function manageAccess($id)
    {
        $document = Document::findOrFail($id);
        $users = User::where('role', '!=', 'admin')->where('status', 1)->get();
        return view('admin.manage-access', compact('document', 'users'));
    }
}
