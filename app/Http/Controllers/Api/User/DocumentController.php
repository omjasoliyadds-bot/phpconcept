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
        $validator = Validator::make($request->all(), [
            'document' => 'required|file|max:10240',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        }

        $userId = auth()->id();
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

        $extension = $file->getClientOriginalExtension();
        $path = $file->store('documents/' . auth()->id(), 'local');

        $document = Document::create([
            'user_id' => auth()->id(),
            'folder_id' => $request->folder_id,
            'name' => $originalName,
            'path' => $path,
            'extension' => $extension,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'is_public' => false
        ]);

        return response()->json([
            'status' => true,
            'message' => 'File uploaded successfully',
            'document' => $document
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
        $document = Document::where('id', $id)->where('user_id', $userId)->firstOrFail();

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

        if (Storage::exists($document->path)) {
            Storage::delete($document->path);
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

        return Storage::disk('public')->download($document->path, $document->name);
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
            if (!$user) continue;
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

        Document::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        DocumentUserPermission::where('document_id', $id)->where('user_id', $request->user_id)->delete();

        return response()->json(['status' => true, 'message' => 'Access revoked successfully']);
    }

    public function sharedWithMe()
    {
        $userId = auth()->id();

        $documents = Document::whereHas('permissions', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with(['user', 'permissions' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
        ->get();

        return response()->json([
            'status' => true,
            'data' => $documents
        ]);
    }
}
