<?php
namespace App\Observers;

use App\Models\Folder;

class FolderObserver
{
    public function created(Folder $folder)
    {
        auditLog(
            'create',
            'folder',
            "Created folder: {$folder->name}",
            null,
            $folder->toArray(),
            $folder->id,
            $folder->user_id
        );
    }

    public function updated(Folder $folder)
    {
        $changes = $folder->getChanges();

        if (empty($changes)) return;

        auditLog(
            'update',
            'folder',
            "Renamed folder to: {$folder->name}",
            $folder->getOriginal(),
            $folder->toArray(),
            $folder->id,
            $folder->user_id
        );
    }

    public function deleted(Folder $folder)
    {
        auditLog(
            'delete',
            'folder',
            "Deleted folder: {$folder->name}",
            $folder->toArray(),
            null,
            $folder->id,
            $folder->user_id
        );
    }
}