<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MaterialPurchase extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'material_purchases';
    protected $primaryKey = 'mat_pur_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'material_id',
        'price',
        'vendor_id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'price' => 'decimal:3',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'vendor_id');
    }
}
