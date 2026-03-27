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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Notifications\DocumentNotification;
use App\Notifications\DocumentDeleteNotification;

class DocumentController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated'], 401);
        }
        $validator = Validator::make($request->all(), [
            'document' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $file = $request->file('document');
        $userId = $user->id;
        $folderId = $request->folder_id;

        if ($folderId) {
            $folder = Folder::where('id', $folderId)
                ->where('user_id', $userId)
                ->first();

            if (!$folder) {
                return response()->json([
                    'status' => false,
                    'errors' => [
                        'folder_id' => ['Invalid folder or unauthorized']
                    ]
                ]);
            }
        }

        $blockedExtensions = ['php', 'exe', 'sh', 'bat', 'js'];

        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, $blockedExtensions)) {
            return response()->json([
                'status' => false,
                'message' => 'This file type is not allowed'
            ], 403);
        }

        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        $mime = $file->getMimeType();

        if (!in_array($mime, $allowedMimes)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid file type (MIME check failed)'
            ], 422);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file->getPathname());

        if (!in_array($realMime, $allowedMimes)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid file content (magic bytes mismatch)'
            ], 422);
        }

        // Additional safety: Check for common script headers in non-executable files
        $content = file_get_contents($file->getPathname(), false, null, 0, 512);
        if (preg_match('/^#!|^<\?php|eval\(|base64_decode\(/i', $content)) {
            return response()->json([
                'status' => false,
                'message' => 'Potential script detected in file'
            ], 403);
        }

        $originalName = $file->getClientOriginalName();

        $duplicate = Document::where('user_id', $userId)
            ->where('name', $originalName)
            ->where('folder_id', $folderId)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'status' => false,
                'errors' => [
                    'document' => ['File already exists in this folder']
                ]
            ]);
        }

        $fileSize = $file->getSize();
        $usedStorage = $user->used_storage;

        if (($usedStorage + $fileSize) > $user->storage_limit) {
            return response()->json([
                'status' => false,
                'message' => 'Storage limit exceeded',
                'data' => [
                    'used_mb' => round($usedStorage / 1024 / 1024, 2),
                    'total_mb' => round($user->storage_limit / 1024 / 1024, 2),
                    'remaining_mb' => round(($user->storage_limit - $usedStorage) / 1024 / 1024, 2),
                ]
            ], 403);
        }
        $fileName = Str::uuid() . '.' . $extension;
        $path = $file->storeAs('documents/' . $userId, $fileName, 'local');

        $document = Document::create([
            'user_id' => $userId,
            'folder_id' => $folderId,
            'name' => $originalName,
            'path' => $path,
            'extension' => $extension,
            'size' => $fileSize,
            'mime_type' => $realMime,
            'is_public' => false
        ]);
        $currentUser = auth()->user();
        $admins = User::where('role', 'admin')->get();
        if ($admins->isNotEmpty()) {
            foreach ($admins as $admin) {
                if ($admin->id !== $currentUser->id) {
                    $admin->notify(new DocumentNotification($document, $currentUser));
                }
            }
        }

        auditLog(
            'Upload File',
            'Document',
            "Uploaded file {$originalName}",
            null,
            null,
            $document->id
        );

        Cache::forget("folders_data_{$userId}");
        Cache::forget("explorer_data_{$userId}");
        Cache::forget("user_dashboard_stats_{$userId}");

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
                    ->orWhereHas('permissionUsers', function ($q) use ($userId) {
                        $q->where('user_id', $userId)
                            ->where('permission', 'edit');
                    });
            })->firstOrFail();

        $newName = $request->name;
        if (!Str::endsWith($newName, '.' . $document->extension)) {
            $newName .= '.' . $document->extension;
        }

        $duplicate = Document::where('user_id', $document->user_id)
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

        $oldName = $document->name;
        $document->name = $newName;
        $document->save();

        auditLog('Rename File', 'Document', "Renamed file \"{$oldName}\" to \"{$newName}\"", ['name' => $oldName], ['name' => $newName], $document->id);

        Cache::forget("folders_data_{$document->user_id}");
        Cache::forget("explorer_data_{$document->user_id}");

        return response()->json([
            'status' => true,
            'message' => 'File renamed successfully',
            'document' => $document
        ]);
    }

    public function destroy($id)
    {
        $document = Document::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        if (Storage::disk('local')->exists($document->path)) {
            Storage::disk('local')->delete($document->path);
        }

        $documentName = $document->name;
        $documentId = $document->id;
        $document->delete();

        auditLog('Delete File', 'Document', "Deleted file \"{$documentName}\"", null, null, $documentId);

        Cache::forget("folders_data_{$document->user_id}");
        Cache::forget("explorer_data_{$document->user_id}");
        Cache::forget("user_dashboard_stats_{$document->user_id}");

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $admins = User::where('role', 'admin')->get();
        if ($admins->isNotEmpty()) {
            foreach ($admins as $admin) {
                if ($admin->id !== $user->id) {
                    $admin->notify(new DocumentDeleteNotification($documentName, $user));
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'File deleted successfully'
        ]);
    }

    public function download(Document $document)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }
        $hasPermission =
            $document->user_id === $user->id ||
            $document->is_public ||
            $document->permissionUsers()
                ->where('user_id', $user->id)
                ->where('permission', 'download')
                ->exists();

        if (!$hasPermission && !$user->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        if (!Storage::disk('local')->exists($document->path)) {
            abort(404, 'File not found');
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

        $documents = Document::whereHas('permissionUsers', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with([
                'user',
                'permissionUsers' => function ($query) use ($userId) {
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
