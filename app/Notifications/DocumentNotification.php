<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use App\Models\Document;
use App\Models\User;

class DocumentNotification extends Notification
{
    use SerializesModels;
    public $document;
    public $user;
    /**
     * Create a new notification instance.
     */
    public function __construct(Document $document, User $user)
    {
        $this->document = $document;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'document_name' => $this->document->name,
            'uploaded_by' => $this->user->name,
            'user_id' => $this->user->id,
            'title' => 'New Document Uploaded',
            'message' => "{$this->user->name} has uploaded a new document: {$this->document->name}",
            'action_url' => route('admin.documents.view'),
            'type' => 'document_upload'
        ];
    }
}
