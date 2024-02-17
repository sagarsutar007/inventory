<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Warehouse extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'vendors';
    protected $primaryKey = 'vendor_id';
    public $incrementing = false;
    protected $keyType = 'string';
}
