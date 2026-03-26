<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Document;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    public function index()
    { 
        $stats = Cache::remember('admin_dashboard_stats', 10, function () {
            return [
                'users' => User::where('role', 'user')->count(),
                'documents'=> Document::count(),
                'totalSize'=> Document::sum('size'),
            ];
        });
        $users = $stats['users'];
        $documents = $stats['documents'];
        $totalSize = $stats['totalSize'];

        return view('admin.dashboard', compact('users', 'documents', 'totalSize'));
    }
}
