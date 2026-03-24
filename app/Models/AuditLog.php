<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Exception;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'module',
        'action',
        'record_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'hash',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
    public function save(array $options = [])
    {
        if ($this->exists) {
            throw new Exception('Audit logs cannot be modified');
        }

        return parent::save($options);
    }

    public function delete()
    {
        throw new Exception('Audit logs cannot be deleted');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            $data = [
                'user_id' => $log->user_id,
                'module' => $log->module,
                'action' => $log->action,
                'record_id' => $log->record_id,
                'description' => $log->description,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
            ];

            $log->hash = hash('sha256', json_encode($data));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}