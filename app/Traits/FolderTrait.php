<?php

namespace App\Traits;

trait FolderTrait
{
    public function calculateTotalSize($folder)
    {
        $directFilesSize = $folder->files()->sum('size') ?: 0;
        $subfoldersSize = 0;
        
        foreach ($folder->subfolders as $subfolder) {
            $subfoldersSize += $this->calculateTotalSize($subfolder);
        }

        return $directFilesSize + $subfoldersSize;
    }

    public function calculateItemsCount($folder)
    {
        $directFilesCount = $folder->files()->count();
        $subfoldersCount = $folder->subfolders()->count();
        
        foreach ($folder->subfolders as $subfolder) {
            $directFilesCount += $this->calculateItemsCount($subfolder);
        }

        return $directFilesCount + $subfoldersCount;
    }

    public function calculateSubfolderCount($folder)
    {
        return $folder->subfolders()->count();
    }
}
