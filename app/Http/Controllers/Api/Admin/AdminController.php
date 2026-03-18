<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class AdminController extends Controller
{
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->tokens()->delete();
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'status' => true,
            'message' => 'Admin logged out successfully'
        ]);
    }

    public function getUsers(Request $request)
    {
        if ($request->ajax()) {
            $users = User::select(['id', 'name', 'email', 'status'])->where('role', 'user');
            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('status', function ($user) {
                    $checked = $user->status ? 'checked' : '';
                    return '
                        <div class="form-check form-switch">
                        <input class="form-check-input toggle-status"
                        type="checkbox"
                            data-id="' . $user->id . '"
                        ' . $checked . '>
                        <span class="badge ' . ($user->status ? 'bg-success' : 'bg-danger') . ' px-3 py-2">
                            ' . ($user->status ? 'Activated' : 'Deactivated') . '
                        </span>
                        </div>
    ';
                })

                ->rawColumns(['status'])
                ->make(true);
        }
    }
    public function toggleStatus(Request $request)
    {
        $user = User::findOrFail($request->id);
        $user->status = $user->status ? 0 : 1;
        $user->save();

        $statusLabel = $user->status ? 'activated' : 'deactivated';
        return response()->json([
            'status' => true,
            'message' => "User account {$statusLabel} successfully"
        ]);
    }
    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully'
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password does not match'
            ], 422);
        }

        $user->update([
            'password' => $request->new_password
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully'
        ]);
    }
}
