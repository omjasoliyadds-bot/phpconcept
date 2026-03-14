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

    protected $appends = ['total_size'];

    public function files()
    {
        return $this->hasMany(Document::class, 'folder_id');
    }

    public function getTotalSizeAttribute()
    {
        return $this->files()->sum('size');
    }
    
}
