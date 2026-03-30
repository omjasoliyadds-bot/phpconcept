<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Carbon\Carbon;
use App\Models\Folder;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    public function dashboard()
    {
        $user_id = auth()->id();
        $cacheKey = "user_dashboard_stats_{$user_id}";

        $stats = Cache::remember($cacheKey, 600, function () use ($user_id) {
            return [
                'documentTotal' => Document::where('user_id', $user_id)->count(),
                'totalFolder' => Folder::where('user_id', $user_id)->count(),
                'uploadToday' => Document::where('user_id', $user_id)->where('created_at', '>=', Carbon::today())->count(),
            ];
        });
        $documentTotal = $stats['documentTotal'];
        $totalFolder = $stats['totalFolder'];
        $uploadToday = $stats['uploadToday'];

        return view('user.dashboard', compact('documentTotal', 'uploadToday', 'totalFolder'));
    }

    public function profile()
    {
        return view('user.profile');
    }

    public function folders()
    {
        return view('user.folders.index');
    }

    public function folderFiles($id)
    {
        $user_id = auth()->id();
        $folder = Folder::where('user_id', $user_id)->where('id', $id)->firstOrFail();
        $users = User::getCachedActiveNonAdmin($user_id);
        return view('user.folders.files', compact('folder', 'users'));
    }

    public function explorer()
    {
        $users = User::getCachedActiveNonAdmin();
        return view('user.explorer.index', compact('users'));
    }

    public function sharedWithMe()
    {
        return view('user.explorer.share-me');
    }

    public function manageAccess($id)
    {
        $document = Document::where('user_id', auth()->id())->findOrFail($id);
        $users = User::getCachedActiveNonAdmin();
        return view('user.explorer.manage-access', compact('document', 'users'));
    }
    public function viewOtpPage(){
        $otpToken = session('otp_token');
        return view('otp', compact('otpToken'));
    }
}
