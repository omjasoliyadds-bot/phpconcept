<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Folder;

class DocumentController extends Controller
{
    /**
     * Upload a new document (CREATE)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document' => 'required|file|max:10240', // Max 10MB
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        }

        // Additional check for folder_id ownership if provided
        $userId = auth()->id();
        $folderId = $request->folder_id;

        if ($folderId) {
            $folder = Folder::where('id', $folderId)
                ->where('user_id', $userId)
                ->first();

            if (!$folder) {
                return response()->json([
                    'status' => false,
                    'errors' => ['folder_id' => ['The selected folder is invalid or does not belong to you.']],
                ]);
            }
        }

        $file = $request->file('document');
        $originalName = $file->getClientOriginalName();

        // Check for duplicate file name in the same folder
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

        // Store in 'local' storage (private by default)
        // Path: storage/app/private/documents/{user_id}/{filename}
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

    /**
     * Rename a document (UPDATE)
     */
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

        // Ensure extension stays the same
        $newName = $request->name;
        if (!Str::endsWith($newName, '.' . $document->extension)) {
            $newName .= '.' . $document->extension;
        }

        // Check for duplicate file name (excluding current document) in the same folder
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

    /**
     * Delete a document (DELETE)
     */
    public function destroy($id)
    {
        $document = Document::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        // Delete from physical storage
        if (Storage::exists($document->path)) {
            Storage::delete($document->path);
        }

        $document->delete();

        return response()->json([
            'status' => true,
            'message' => 'File deleted successfully'
        ]);
    }

    /**
     * Securely Download the file
     */
    public function download($id)
    {
        $document = Document::where('id', $id)
            ->where(function ($query) {
                $query->where('user_id', auth()->id())
                    ->orWhere('is_public', true)
                    ->orWhereHas('sharedUsers', function ($q) {
                        $q->where('users.id', auth()->id());
                    });
            })
            ->firstOrFail();

        if (!Storage::disk('local')->exists($document->path)) {
            abort(404, 'File not found on disk');
        }

        return Storage::disk('local')->download(
            $document->path,
            $document->name
        );
    }
    public function share(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|exists:users,id',
            'permission' => 'required|in:view,edit,download'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()
            ]);
        }

        $document = Document::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // already shared users
        $alreadyShared = $document->sharedUsers()
            ->whereIn('users.id', $request->user_ids)
            ->pluck('users.id')
            ->toArray();

        $newUsers = array_diff($request->user_ids, $alreadyShared);

        $syncData = [];
        foreach ($newUsers as $user_id) {
            $syncData[$user_id] = [
                'permission' => $request->permission
            ];
        }

        if (!empty($syncData)) {
            $document->sharedUsers()->syncWithoutDetaching($syncData);
        }
        if(count($alreadyShared) > 0){
           return response()->json([
               'status'=> false,
               'message' => 'Some users already have access'
           ]);
        }
        return response()->json([
            'status' => true,
            'message' => 'File shared successfully',
            'already_shared_users' => $alreadyShared
        ]);
    }
}
