<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use App\Enums\UserRole;

class UserController extends Controller
{
    public function getUsers(Request $request)
    {
        if ($request->ajax()) {
            $users = User::select(['id', 'name', 'email', 'status', 'can_share', 'storage_limit'])->where('role', UserRole::USER->value);
            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('status', function ($user) {
                $checked = $user->status ? 'checked' : '';
                return '
                        <div class="form-check form-switch">
                            <input class="form-check-input toggle-status" type="checkbox" data-id="' . $user->id . '" ' . $checked . '>
                            <span class="badge ' . ($user->status ? 'bg-success' : 'bg-danger') . ' px-2 py-1">
                                ' . ($user->status ? 'Active' : 'Inactive') . '
                            </span>
                        </div>
                    ';
            })
                ->addColumn('can_share', function ($user) {
                $checked = $user->can_share ? 'checked' : '';
                return '
                        <div class="form-check form-switch">
                            <input class="form-check-input toggle-sharing" type="checkbox" data-id="' . $user->id . '" ' . $checked . '>
                            <span class="badge ' . ($user->can_share ? 'bg-primary' : 'bg-warning') . ' px-2 py-1">
                                ' . ($user->can_share ? 'Allowed' : 'Restricted') . '
                            </span>
                        </div>
                    ';
            })
                ->addColumn('storage', function ($user) {
                $used = $user->used_storage ?? 0;
                $limit = $user->storage_limit;
                $percentage = $limit > 0 ? min(($used / $limit) * 100, 100) : 0;
                $colorClass = $percentage > 90 ? 'bg-danger' : ($percentage > 70 ? 'bg-warning' : 'bg-success');

                return '
                        <div class="d-flex flex-column" style="min-width: 150px;">
                            <div class="d-flex justify-content-between mb-1">
                                <small>' . formatBytes($used) . ' / ' . formatBytes($limit) . '</small>
                                <small class="fw-bold">' . round($percentage) . '%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar ' . $colorClass . '" role="progressbar" style="width: ' . $percentage . '%;"></div>
                            </div>
                            <button class="btn btn-sm btn-link p-0 text-start mt-1 edit-storage" data-id="' . $user->id . '" data-limit="' . $user->storage_limit . '">
                                <i class="fa fa-edit"></i> Edit Limit
                            </button>
                        </div>
                    ';
            })
                ->rawColumns(['status', 'can_share', 'storage'])
                ->make(true);
        }
    }

    public function toggleStatus(Request $request)
    {
        $user = User::findOrFail($request->id);
        $oldStatus = $user->status;
        $user->status = $user->status ? 0 : 1;
        $user->save();

        $statusLabel = $user->status ? 'activated' : 'deactivated';

        auditLog('Toggle Status', 'User', "User {$user->name} status changed to {$statusLabel}", ['status' => $oldStatus], ['status' => $user->status], $user->id);

        return response()->json([
            'status' => true,
            'message' => "User account {$statusLabel} successfully"
        ]);
    }

    public function toggleSharing(Request $request)
    {
        $user = User::findOrFail($request->id);
        $oldCanShare = $user->can_share;
        $user->can_share = $user->can_share ? 0 : 1;
        $user->save();

        $statusLabel = $user->can_share ? 'enabled' : 'disabled';

        auditLog('Toggle Sharing', 'User', "Sharing capability for {$user->name} changed to {$statusLabel}", ['can_share' => $oldCanShare], ['can_share' => $user->can_share], $user->id);

        return response()->json([
            'status' => true,
            'message' => "Sharing capability {$statusLabel} for user successfully"
        ]);
    }

    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $user = User::findOrFail($id);
        $oldData = ['name' => $user->name, 'email' => $user->email];
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        auditLog('Admin Update Profile', 'User', "Admin {$user->name} updated their own profile", $oldData, ['name' => $user->name, 'email' => $user->email], $user->id);

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

        auditLog('Admin Change Password', 'User', 'Admin changed their password', null, null, $user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully'
        ]);
    }
    public function updateStorageLimit(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'storage_limit' => 'required|numeric|min:0' // in bytes
        ]);

        $user = User::findOrFail($request->id);
        $oldLimit = $user->storage_limit;
        $user->storage_limit = $request->storage_limit;
        $user->save();

        auditLog('Update Storage Limit', 'User', "Updated storage limit for {$user->name} to " . formatBytes($user->storage_limit), ['limit' => $oldLimit], ['limit' => $user->storage_limit], $user->id);

        return response()->json([
            'status' => true,
            'message' => 'Storage limit updated successfully'
        ]);
    }

    public function getNotification(Request $request)
    {
        $user = auth()->user();
        $notifications = $user->notifications()->latest()->limit(5)->get();
        $unreadCount = $user->unreadNotifications()->where('read_at',null)->count();
        return response()->json([
            'status' => true,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
}
