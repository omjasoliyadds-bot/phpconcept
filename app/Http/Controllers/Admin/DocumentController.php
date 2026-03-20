<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\User;

class DocumentController extends Controller
{
    public function index()
    {
        return view('admin.document');
    }

    public function manageAccess($id)
    {
        $document = Document::findOrFail($id);
        $users = User::where('role', '!=', 'admin')->where('status', 1)->get();
        return view('admin.manage-access', compact('document', 'users'));
    }
}
