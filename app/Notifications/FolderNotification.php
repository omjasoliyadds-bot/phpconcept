<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use App\Models\Folder;
use App\Models\User;

class FolderNotification extends Notification
{
    use SerializesModels;
    public $folder;
    public $user;
    public $action; // 'created' or 'deleted'

    public function __construct(Folder $folder, User $user, $action = 'created')
    {
        $this->folder = $folder;
        $this->user = $user;
        $this->action = $action;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $title = $this->action === 'created' ? 'New Folder Created' : 'Folder Deleted';
        $message = $this->action === 'created' 
            ? "{$this->user->name} has created a new folder: {$this->folder->name}"
            : "{$this->user->name} has deleted folder: {$this->folder->name}";

        return [
            'folder_id' => $this->folder->id,
            'folder_name' => $this->folder->name,
            'performed_by' => $this->user->name,
            'user_id' => $this->user->id,
            'title' => $title,
            'message' => $message,
            'action_url' => route('admin.documents.view'),
            'type' => $this->action === 'created' ? 'folder_create' : 'folder_delete'
        ];
    }
}
