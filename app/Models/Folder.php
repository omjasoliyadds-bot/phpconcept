<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
class Folder extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'user_id',
        'parent_id',

    ];

    protected $appends = ['total_size', 'items_count', 'subfolder_count'];

    public function files()
    {
        return $this->hasMany(Document::class , 'folder_id');
    }

    public function subfolders()
    {
        return $this->hasMany(Folder::class , 'parent_id');
    }
    public function parent()
    {
        return $this->belongsTo(Folder::class , 'parent_id');
    }
    public function getTotalSizeAttribute()
    {
        $directFilesSize = $this->relationLoaded('files') ? ($this->files->sum('size') ?: 0) : ($this->files()->sum('size') ?: 0);
        $subfoldersSize = 0;

        foreach ($this->subfolders as $subfolder) {
            $subfoldersSize += $subfolder->total_size;
        }

        return $directFilesSize + $subfoldersSize;
    }

    public function getItemsCountAttribute()
    {
        $filesCount = $this->relationLoaded('files') ? $this->files->count() : $this->files()->count();
        $foldersCount = $this->relationLoaded('subfolders') ? $this->subfolders->count() : $this->subfolders()->count();

        foreach ($this->subfolders as $subfolder) {
            $filesCount += $subfolder->items_count;
        }

        return $filesCount + $foldersCount;
    }

    public function getSubfolderCountAttribute()
    {
        return $this->relationLoaded('subfolders') ? $this->subfolders->count() : $this->subfolders()->count();
    }

    public static function deleteRecursive($folder)
    {
        // Recursively delete subfolders
        foreach ($folder->subfolders as $subfolder) {
            self::deleteRecursive($subfolder);
        }

        // Delete all documents in this folder
        foreach ($folder->files as $document) {
            // Delete physical file
            if (Storage::disk('local')->exists($document->path)) {
                Storage::disk('local')->delete($document->path);
            }

            $document->forceDelete();
        }

        // Finally delete the folder itself
        $folder->forceDelete();
    }
}
