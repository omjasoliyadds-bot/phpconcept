<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Folder;
use App\Models\Document;

class FolderController extends Controller
{
    public function folderCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "parent_id" => "nullable|exists:folders,id",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors(),
            ]);
        }

        $userId = auth()->id();
        $parentId = $request->parent_id;

        if ($parentId) {
            $parentFolder = Folder::where('id', $parentId)->where('user_id', $userId)->first();
            if (!$parentFolder) {
                return response()->json([
                    "status" => false,
                    "errors" => ["parent_id" => ["The selected parent folder is invalid or does not belong to you."]],
                ]);
            }
        }

        $duplicate = Folder::where('user_id', $userId)->where('name', $request->name)->where('parent_id', $parentId)->exists();
        if ($duplicate) {
            return response()->json([
                "status" => false,
                "errors" => ["name" => ["A folder with this name already exists in this location."]],
            ]);
        }

        $folder = Folder::create([
            "name" => $request->name,
            "user_id" => $userId,
            "parent_id" => $parentId
        ]);

        auditLog('Create Folder', 'Folder', "Created folder \"{$request->name}\"", null, ['name' => $request->name], $folder->id);

        return response()->json([
            "status" => true,
            'message' => 'Folder created successfully',
            "folder" => $folder,
        ]);
    }

    public function getAllFolders(Request $request)
    {
        $user_id = auth()->id();
        $folders = Folder::where('user_id', $user_id)->whereNull('parent_id')->with('subfolders')->get();
        $outsideFiles = Document::where('user_id', $user_id)->whereNull('folder_id')->get();
        return response()->json([
            "status" => true,
            "folders" => $folders,
            "outsideFiles" => $outsideFiles
        ]);
    }

    public function removeFolder($id)
    {
        $userId = auth()->id();
        $folder = Folder::where('id', $id)->where('user_id', $userId)->first();
        if (!$folder) {
            return response()->json([
                "status" => false,
                "message" => "Folder not found or you don't have permission to delete it"
            ]);
        }

        $folderName = $folder->name;
        $folderId = $folder->id;
        Folder::deleteRecursive($folder);

        auditLog('Delete Folder', 'Folder', "Deleted folder \"{$folderName}\" and all its contents", null, null, $folderId);
        return response()->json(["status" => true, "message" => "Folder and its contents deleted successfully"]);
    }

    public function folderFiles($id)
    {
        $userId = auth()->id();
        $folder = Folder::where('id', $id)->where('user_id', $userId)->with(['files', 'subfolders'])->firstOrFail();

        return response()->json([
            "status" => true,
            "folder" => $folder,
            "subfolders" => $folder->subfolders,
            "files" => $folder->files,
            "totalSize" => $folder->total_size
        ]);
    }

    public function updateFolderName(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => false, "errors" => $validator->errors()]);
        }

        $userId = auth()->id();
        $folder = Folder::where('id', $id)->where('user_id', $userId)->first();

        if (!$folder) {
            return response()->json(["status" => false, "message" => "Folder not found"], 404);
        }

        $duplicate = Folder::where('user_id', $userId)->where('name', $request->name)->where('parent_id', $folder->parent_id)->where('id', '!=', $id)->exists();
        if ($duplicate) {
            return response()->json([
                "status" => false,
                "errors" => ["name" => ["A folder with this name already exists in this location."]],
            ]);
        }

        $oldName = $folder->name;
        $folder->name = $request->name;
        $folder->save();

        auditLog('Rename Folder', 'Folder', "Renamed folder \"{$oldName}\" to \"{$request->name}\"", ['name' => $oldName], ['name' => $request->name], $folder->id);

        return response()->json([
            "status" => true,
            "message" => "Folder renamed successfully",
            "folder" => $folder
        ]);
    }

    public function getExplorerData()
    {
        $user_id = auth()->id();
        $folders = Folder::where('user_id', $user_id)->whereNull('parent_id')->get();
        $files = Document::where('user_id', $user_id)->whereNull('folder_id')->get();
        $allFolders = Folder::where('user_id', $user_id)->get();
        return response()->json([
            "status" => true,
            "folders" => $folders,
            "files" => $files,
            "allFolders" => $allFolders
        ]);
    }
}
