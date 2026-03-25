<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserRole;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'verification_token',
        'role',
        'status',
        'can_share',
        'storage_limit'
    ];

    public function isAdmin()
    {
        return $this->role === UserRole::ADMIN->value || $this->role === UserRole::ADMIN;
    }
    public function isUser()
    {
        return $this->role === UserRole::USER->value || $this->role === UserRole::USER;
    }
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    public function folders()
    {
        return $this->hasMany(Folder::class);
    }
    public function permissions()
    {
        return $this->hasMany(DocumentUserPermission::class);
    }
    public function getUsedStorageAttribute()
    {
        if ($this->relationLoaded('documents')) {
            return $this->documents->sum('size');
        }
        return $this->documents()->sum('size');
    }
    public function getRemainingStorageAttribute()
    {
        return max(0, $this->storage_limit - $this->used_storage);
    }
    public function sharedDocuments()
    {
        return $this->belongsToMany(Document::class , 'document_user_permissions')
            ->withPivot('permission')
            ->withTimestamps();
    }

    public function scopeActiveNonAdmin($query, $userId = null)
    {
        $userId = $userId ?? auth()->id();

        return $query->when($userId, function ($q) use ($userId) {
            return $q->where('id', '!=', $userId);
        })
            ->where('role', '!=', UserRole::ADMIN->value)
            ->where('status', 1);
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'can_share' => 'boolean',
        ];
    }
}
