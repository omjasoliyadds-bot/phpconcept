<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use App\Models\Document;
use App\Models\User;

class DocumentDeleteNotification extends Notification
{
    use SerializesModels;
    public $documentName;
    public $user;

    public function __construct(string $documentName, User $user)
    {
        $this->documentName = $documentName;
        $this->user = $user;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'document_name' => $this->documentName,
            'performed_by' => $this->user->name,
            'user_id' => $this->user->id,
            'title' => 'Document Deleted',
            'message' => "{$this->user->name} has deleted a document: {$this->documentName}",
            'action_url' => route('admin.documents.view'),
            'type' => 'document_delete'
        ];
    }
}
