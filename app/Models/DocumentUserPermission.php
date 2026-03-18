<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentUserPermission extends Model
{
    use SoftDeletes;
    protected $table = "document_user_permissions";
    protected $fillable = [
        'document_id',
        'user_id',
        'permission'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}