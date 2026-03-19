<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Document;
use App\Models\DocumentUserPermission;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    public function getAllDocuments(Request $request)
    {
        if ($request->ajax()) {
            $documents = Document::with('permissions.user:id,name,email')->get();
            return DataTables::of($documents)
                ->addIndexColumn()
                ->addColumn('permissions', function ($document) {
                    if ($document->permissions->isEmpty()) {
                        return '<span class="text-muted">No Access</span>';
                    }
                    $grouped = $document->permissions->groupBy('user_id');
                    $html = '';
                    foreach ($grouped as $perms) {
                        $user = $perms->first()->user;
                        $permissions = $perms->pluck('permission')->implode(', ');
                        $html .= "
                        <div class='mb-1'>
                            <strong>{$user->name}</strong> 
                        <span class='badge bg-info text-dark'>{$permissions}</span>
                        </div>
                    ";
                    }

                    return $html;
                })
                ->addColumn('action', function ($document) {
                    $manageUrl = route('admin.documents.manage-access', $document->id);
                    return '
                    <a href="' . $manageUrl . '" class="btn btn-sm btn-outline-primary" title="Manage Access"><i class="fa fa-users-cog me-1"></i></a>
                    <a href="javascript:void(0)" class="btn btn-sm btn-outline-danger revoke-permissions" data-id="' . $document->id . '" title="Revoke All Access"><i class="fa fa-user-minus me-1"></i></a>
                    <a href="javascript:void(0)" class="btn btn-sm btn-outline-danger delete-document" data-id="' . $document->id . '" title="Force Delete"><i class="fa fa-trash me-1"></i></a>
                    ';
                })
                ->rawColumns(['permissions', 'action'])
                ->make(true);
        }
    }

    public function revokeDocumentPermissions(Request $request, $id)
    {
        $document = Document::where('id', $id)->firstOrFail();
        $document->permissions()->delete();
        return response()->json([
            'status' => true,
            'message' => 'Access revoked successfully'
        ]);
    }

    public function forcedDeleteDocument(Request $request,$id)
    {
        $document = Document::where('id', $id)->firstOrFail();
        if(Storage::exists($document->path)){
            Storage::delete($document->path);
        }
        $document->forceDelete();
        return response()->json([
            'status' => true,
            'message' => 'File deleted successfully'
        ]);
    }

    public function getPermissions($id)
    {
        $document = Document::findOrFail($id);

        $permissions = DocumentUserPermission::where('document_id', $id)
            ->with('user:id,name,email')->get()->groupBy('user_id');

        $formattedPermissions = [];
        foreach ($permissions as $userId => $userPerms) {
            $formattedPermissions[] = [
                'user' => $userPerms->first()->user,
                'permissions' => $userPerms->pluck('permission')->toArray()
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $formattedPermissions
        ]);
    }

    public function share(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|exists:users,id,status,1',
            'permission' => 'required|array',
            'permission.*' => 'in:view,edit,download'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()
            ]);
        }

        $document = Document::findOrFail($id);

        $alreadyShared = [];

        foreach ($request->user_ids as $userId) {
            $user = User::find($userId);
            foreach ($request->permission as $perm) {
                $existingPerm = DocumentUserPermission::withTrashed()->where([
                    'document_id' => $id,
                    'user_id' => $userId,
                    'permission' => $perm
                ])->first();

                if ($existingPerm) {
                    if ($existingPerm->trashed()) {
                        $existingPerm->restore();
                    } else {
                        $alreadyShared[] = $userId;
                    }
                } else {
                    DocumentUserPermission::create([
                        'document_id' => $id,
                        'user_id' => $userId,
                        'permission' => $perm
                    ]);
                }
            }
        }
        return response()->json([
            'status' => true,
            'message' => !empty($alreadyShared)
                ? 'Permissions updated successfully'
                : 'Access granted successfully',
        ]);
    }

    public function revokePermission(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        }

        $document = Document::findOrFail($id);
        DocumentUserPermission::where('document_id', $id)
            ->where('user_id', $request->user_id)
            ->delete();

        return response()->json([
            'status' => true,
            'message' => 'User access revoked successfully'
        ]);
    }
}
