<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Vendor extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'vendors';
    protected $primaryKey = 'vendor_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'vendor_name',
        'vendor_address',
        'vendor_city',
        'created_at'
    ];

    public function materialPurchase()
    {
        return $this->belongsTo(MaterialPurchase::class, 'material_id', 'material_id');
    }
}
