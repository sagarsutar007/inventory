<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdOrdersMaterial extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'prod_orders_materials';
    protected $primaryKey = 'pom_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'po_id',
        'material_id',
        'quantity',
        'status',
        'created_by',
        'updated_by',
    ];

    // protected $casts = [
    //     'quantity' => 'decimal:10,3',
    // ];

    // Accessor for quantity
    // public function getQuantityAttribute($value)
    // {
    //     return (float) $value;
    // }

    // // Mutator for quantity
    // public function setQuantityAttribute($value)
    // {
    //     $this->attributes['quantity'] = (string) $value;
    // }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'material_id');
    }
}
