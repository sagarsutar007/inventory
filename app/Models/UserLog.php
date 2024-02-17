<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'user_logs';

    protected $fillable = [
        'user_id', 
        'action_type', 
        'ip_address', 
        'mac_address',
        'client',
        'device_type'
    ];

    public $timestamps = false;
}
