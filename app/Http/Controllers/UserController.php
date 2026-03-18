<?php

namespace App\Http\Controllers;
use App\Models\Document;
use Carbon\Carbon;
use App\Models\Folder;
use Illuminate\Http\Request;
use App\Models\User;
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
        $users = User::where('id', '!=', auth()->id())->where('role', '!=', 'admin')->get();
        return view('user.folders.files', compact('folder', 'users'));
    }
    public function explorerView()
    {
        $users = User::where('id', '!=', auth()->id())->where('role', '!=', 'admin')->get();
        return view('user.explorer.index', compact('users'));
    }
    public function shareWithMeView(){
        return view('user.explorer.share-me');
    }
    public function manageAccess($id)
    {
        $document = Document::where('user_id', auth()->id())->findOrFail($id);
        $users = User::where('id', '!=', auth()->id())->where('role', '!=', 'admin')->get();
        return view('user.explorer.manage-access', compact('document', 'users'));
    }
}

