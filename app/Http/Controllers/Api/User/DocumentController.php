<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Folder;
use App\Models\DocumentUserPermission;
use App\Mail\DocumentSharedMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class DocumentController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'document' => 'required|file|max:10240',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        }

        $userId = $user->id;
        $folderId = $request->folder_id;

        if ($folderId) {
            $folder = Folder::where('id', $folderId)->where('user_id', $userId)->first();
            if (!$folder) {
                return response()->json([
                    'status' => false,
                    'errors' => ['folder_id' => ['The selected folder is invalid or does not belong to you.']],
                ]);
            }
        }

        $file = $request->file('document');
        $fileSize = $file->getSize();
        $originalName = $file->getClientOriginalName();

        $duplicate = Document::where('user_id', $userId)
            ->where('name', $originalName)
            ->where('folder_id', $folderId)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'status' => false,
                'errors' => ['document' => ['A file with this name already exists in this location.']],
            ]);
        }

        $usedStorage = $user->documents()->sum('size');

        if (($usedStorage + $fileSize) > $user->storage_limit) {
            return response()->json([
                'status' => false,
                'message' => 'Storage limit exceeded. Please delete some files or upgrade your plan.',
                'data' => [
                    'used_mb' => round($usedStorage / 1024 / 1024, 2),
                    'total_mb' => round($user->storage_limit / 1024 / 1024, 2),
                    'remaining_mb' => round(($user->storage_limit - $usedStorage) / 1024 / 1024, 2),
                ]
            ], 403);
        }
        if ($usedStorage > $user->storage_limit) {
            return response()->json([
                'status' => false,
                'message' => 'Storage limit exceeded. Please delete some files or upgrade your plan.',
            ]);
        }
        $path = $file->store('documents/' . $userId, 'local');

        $document = Document::create([
            'user_id' => $userId,
            'folder_id' => $folderId,
            'name' => $originalName,
            'path' => $path,
            'extension' => $file->getClientOriginalExtension(),
            'size' => $fileSize,
            'mime_type' => $file->getMimeType(),
            'is_public' => false
        ]);

        return response()->json([
            'status' => true,
            'message' => 'File uploaded successfully',
            'data' => $document
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        }

        $userId = auth()->id();
        $document = Document::where('id', $id)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('permissions', function ($q) use ($userId) {
                        $q->where('user_id', $userId)
                            ->where('permission', 'edit');
                    });
            })->firstOrFail();

        $newName = $request->name;
        if (!Str::endsWith($newName, '.' . $document->extension)) {
            $newName .= '.' . $document->extension;
        }

        $duplicate = Document::where('user_id', $userId)
            ->where('name', $newName)
            ->where('folder_id', $document->folder_id)
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'status' => false,
                'errors' => ['name' => ['A file with this name already exists in this location.']],
            ]);
        }

        $document->name = $newName;
        $document->save();

        return response()->json([
            'status' => true,
            'message' => 'File renamed successfully',
            'document' => $document
        ]);
    }

    public function destroy($id)
    {
        $document = Document::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        if (Storage::disk('public')->exists($document->path)) {
            Storage::disk('public')->delete($document->path);
        }

        $document->delete();

        return response()->json([
            'status' => true,
            'message' => 'File deleted successfully'
        ]);
    }

    public function download($id)
    {
        $document = Document::where('id', $id)
            ->where(function ($query) {
                $query->where('user_id', auth()->id())
                    ->orWhere('is_public', true)
                    ->orWhereHas('permissions', function ($q) {
                        $q->where('user_id', auth()->id())
                            ->where('permission', 'download');
                    });
            })->first();

        if (!$document) {
            abort(403, 'Unauthorized access or document not found.');
        }
        auditLog('Download File', 'Document', "Downloaded file {$document->name}", null, null, $document->id);
        return Storage::disk('local')->download($document->path, $document->name);

    }

    public function share(Request $request, $id)
    {
        if (!auth()->user()->can_share) {
            return response()->json([
                'status' => false,
                'message' => 'Your sharing capability has been disabled by an administrator.'
            ], 403);
        }

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

        $document = Document::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $alreadyShared = [];

        foreach ($request->user_ids as $userId) {
            $user = User::where('id', $userId)->where('status', 1)->first();
            if (!$user)
                continue;
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

            Mail::to($user->email)->send(new DocumentSharedMail($document, auth()->user()));
        }

        $sharedWith = User::whereIn('id', $request->user_ids)->pluck('name')->toArray();
        $sharedWithNames = count($sharedWith) > 3
            ? implode(', ', array_slice($sharedWith, 0, 3)) . " and " . (count($sharedWith) - 3) . " others"
            : implode(', ', $sharedWith);

        auditLog('Share', 'Document', "Shared file \"{$document->name}\" with {$sharedWithNames}", null, ['user_ids' => $request->user_ids, 'permissions' => $request->permission], $document->id);

        return response()->json([
            'status' => true,
            'message' => !empty($alreadyShared)
                ? 'Shared successfully, but some permissions already existed'
                : 'File shared successfully',
            'already_shared_users' => array_unique($alreadyShared)
        ]);
    }

    public function getPermissions($id)
    {
        $document = Document::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $permissions = DocumentUserPermission::where('document_id', $id)->with('user:id,name,email')->get()->groupBy('user_id');

        $formattedPermissions = [];
        foreach ($permissions as $userId => $userPerms) {
            $formattedPermissions[] = [
                'user' => $userPerms->first()->user,
                'permissions' => $userPerms->pluck('permission')->toArray()
            ];
        }

        return response()->json(['status' => true, 'data' => $formattedPermissions]);
    }

    public function revokePermission(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        }

        $document = Document::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $targetUser = User::find($request->user_id);
        DocumentUserPermission::where('document_id', $id)->where('user_id', $request->user_id)->delete();
        auditLog('Revoke Access', 'Document', "Revoked access for user \"{$targetUser->name}\" on file \"{$document->name}\"", null, null, $document->id);

        return response()->json(['status' => true, 'message' => 'Access revoked successfully']);
    }

    public function sharedWithMe()
    {
        $userId = auth()->id();

        $documents = Document::whereHas('permissions', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with([
                'user',
                'permissions' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }
            ])
            ->get();

        return response()->json([
            'status' => true,
            'data' => $documents
        ]);
    }
}
