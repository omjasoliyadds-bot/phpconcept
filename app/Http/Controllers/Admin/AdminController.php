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

        return view('admin.dashboard', compact('users', 'documents', 'totalSizeMb'));
    }
}
