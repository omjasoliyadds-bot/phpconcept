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
        $users = User::where('role', 'user')->count();
        $documents = Document::count();
        $totalSize = Document::sum('size');

        return view('admin.dashboard', compact('users', 'documents', 'totalSize'));
    }
}
