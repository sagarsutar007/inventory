<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserPermissions extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'user_permissions';
    protected $primaryKey = 'up_id';
    public $incrementing = false;
    protected $keyType = 'string';


    protected $fillable = [
        'up_id',
        'permission_id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'user_id');
    }
}
