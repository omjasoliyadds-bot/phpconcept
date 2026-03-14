<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Folder;
use App\Models\Document;

class FolderController extends Controller
{
    public function folderCreate(Request $request)
    {
        // dd($request->all());
        // exit;
        $validator = Validator::make($request->all(), [
            "name" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors(),
            ]);
        }

        $folder = Folder::create([
            "name" => $request->name,
            "user_id" => auth()->user()->id,
            "parent_id" => $request->parent_id
        ]);

        return response()->json([
            "status" => true,
            'message' => 'Folder created successfully',
            "folder" => $folder,
        ]);
    }

    public function getAllFolders(Request $request)
    {
        $user_id = auth()->user()->id;
        $folders = Folder::where('user_id', auth()->user()->id)->get();
        return response()->json([
            "status" => true,
            "folders" => $folders,
        ]);
    }

    public function removeFolder($id)
    {
        $folder = Folder::find($id);

        if (!$folder) {
            return response()->json([
                "status" => false,
                "message" => "Folder not found"
            ]);
        }

        $folder->delete();

        return response()->json([
            "status" => true,
            "message" => "Folder deleted successfully"
        ]);
    }
    public function folderFiles($id)
    {
        $userId = auth()->id();

        $folder = Folder::where('id', $id)
            ->where('user_id', $userId)
            ->with('files')
            ->firstOrFail();
        $totalSize = $folder->files->sum('size');

        return response()->json([
            "status" => true,
            "files" => $folder->files,
            "totalSize"=> $totalSize
        ]);
    }

    public function updateFolderName(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors(),
            ]);
        }

        $folder = Folder::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$folder) {
            return response()->json([
                "status" => false,
                "message" => "Folder not found",
            ], 404);
        }

        $folder->name = $request->name;
        $folder->save();

        return response()->json([
            "status" => true,
            "message" => "Folder renamed successfully",
            "folder" => $folder
        ]);
    }

    public function getExplorerData()
    {
        $user_id = auth()->id();
        $folders = Folder::where('user_id', $user_id)->get();
        $files = Document::where('user_id', $user_id)
            ->whereNull('folder_id')
            ->get();

        return response()->json([
            "status" => true,
            "folders" => $folders,
            "files" => $files
        ]);
    }
}

