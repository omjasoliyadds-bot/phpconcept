<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        return $this->hasMany(Document::class, 'folder_id');
    }

    public function subfolders()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function getTotalSizeAttribute()
    {
        $directFilesSize = $this->files()->sum('size') ?: 0;
        $subfoldersSize = 0;
        
        // Eager loading subfolders would help, but for now we follow the simple recursive approach
        foreach ($this->subfolders as $subfolder) {
            $subfoldersSize += $subfolder->total_size;
        }

        return $directFilesSize + $subfoldersSize;
    }

    public function getItemsCountAttribute()
    {
        $directFilesCount = $this->files()->count();
        $subfoldersCount = $this->subfolders()->count();
        
        foreach ($this->subfolders as $subfolder) {
            $directFilesCount += $subfolder->items_count;
        }

        return $directFilesCount + $subfoldersCount;
    }

    public function getSubfolderCountAttribute()
    {
        return $this->subfolders()->count();
    }
    
}
