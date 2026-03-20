<?php
namespace App\Observers;

use App\Models\Document;

class DocumentObserver
{
    public function created(Document $document)
    {
        auditLog(
            'create',
            'document',
            'Document created',
            null,
            $document->toArray(),
            $document->id,
            auth()->id() 
        );
    }

    public function updated(Document $document)
    {
        $changes = $document->getChanges();

        if (empty($changes)) {
            return;
        }

        auditLog(
            'update',
            'document',
            'Document updated',
            $document->getOriginal(),
            $document->toArray(),
            $document->id,
            auth()->id()
        );
    }

    public function deleted(Document $document)
    {
        auditLog(
            'delete',
            'document',
            'Document deleted',
            $document->toArray(),
            null,
            $document->id,
            auth()->id()
        );
    }
}