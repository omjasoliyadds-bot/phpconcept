<?php

namespace App\Http\Controllers;
use App\Models\Document;
use Carbon\Carbon;
use App\Models\Folder;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function loginView()
    {
        return view('login');
    }
    public function userDashboardView()
    {
        $user_id = auth()->id();
        $documentTotal = Document::where('user_id', $user_id)->count();
        $totalFolder = Folder::where('user_id', $user_id)->count();
        $uploadToday = Document::where('user_id', $user_id)->where('created_at', '>=', Carbon::today())->count();
        return view('user.dashboard', compact('documentTotal', 'uploadToday', 'totalFolder'));
    }
    public function userProfile()
    {
        return view('user.profile');
    }
    public function userFolders()
    {
        return view('user.folders.index');
    }
    public function folderFiles($id)
    {
        $folder = Folder::where('user_id', auth()->id())->findOrFail($id);
        return view('user.folders.files', compact('folder'));
    }
    public function explorerView()
    {
        return view('user.explorer.index');
    }
}

