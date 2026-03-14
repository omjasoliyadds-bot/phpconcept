<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

        $file = $request->file('document');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        
        // Store in 'local' storage (private by default)
        // Path: storage/app/documents/{user_id}/{filename}
        $path = $file->store('documents/' . auth()->id());

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

        $document = Document::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        
        // Ensure extension stays the same
        $newName = $request->name;
        if (!Str::endsWith($newName, '.' . $document->extension)) {
            $newName .= '.' . $document->extension;
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
            ->where(function($query) {
                $query->where('user_id', auth()->id())
                      ->orWhereHas('sharedUsers', function($q) {
                          $q->where('users.id', auth()->id());
                      });
            })->firstOrFail();

        if (!Storage::exists($document->path)) {
            abort(404, 'File not found on disk');
        }

        return Storage::download($document->path, $document->name);
    }
}
