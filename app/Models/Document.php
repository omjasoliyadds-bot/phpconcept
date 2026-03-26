<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\DocumentUserPermission;
use Illuminate\Support\Facades\Cache;

class Document extends Model
{
    use HasFactory, SoftDeletes;
    protected static function booted()
    {
        static::created(function ($document) {
            if ($document->user) {
                $document->user->clearUsedStorageCache();
                Cache::forget("user_dashboard_stats_{$document->user_id}");
            }
        });

        static::deleted(function ($document) {
            if ($document->user) {
                $document->user->clearUsedStorageCache();
                Cache::forget("user_dashboard_stats_{$document->user_id}");
            }
        });

        static::restored(function ($document) {
            if ($document->user) {
                $document->user->clearUsedStorageCache();
                Cache::forget("user_dashboard_stats_{$document->user_id}");
            }
        });

        static::updated(function ($document) {
            if ($document->isDirty('size')) {
                $document->user->clearUsedStorageCache();
            }
        });
    }

    protected $fillable = [
        'user_id',
        'name',
        'folder_id',
        'path',
        'extension',
        'size',
        'mime_type',
        'is_public'
    ];

    protected $appends = ['icon'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class , 'folder_id');
    }
    public function permissionUsers()
    {
        return $this->hasMany(DocumentUserPermission::class);
    }
    public function sharedUsers()
    {
        return $this->belongsToMany(User::class , 'document_user_permissions')
            ->withPivot('permission')
            ->withTimestamps();
    }

    public function getIconAttribute()
    {
        $ext = strtolower($this->extension);

        $icon = 'fa-file text-secondary';

        if ($ext == 'pdf') {
            $icon = 'fa-file-pdf text-danger';
        }
        elseif (in_array($ext, ['doc', 'docx'])) {
            $icon = 'fa-file-word text-primary';
        }
        elseif (in_array($ext, ['xls', 'xlsx'])) {
            $icon = 'fa-file-excel text-success';
        }
        elseif (in_array($ext, ['ppt', 'pptx'])) {
            $icon = 'fa-file-powerpoint text-warning';
        }
        elseif (in_array($ext, ['png', 'jpg', 'jpeg'])) {
            $icon = 'fa-file-image text-info';
        }
        elseif ($ext == 'txt') {
            $icon = 'fa-file-lines text-muted';
        }
        elseif ($ext == 'zip') {
            $icon = 'fa-file-zipper text-warning';
        }

        return $icon;
    }
}
