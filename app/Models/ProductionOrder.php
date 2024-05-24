<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'production_orders';
    protected $primaryKey = 'po_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'po_number',
        'material_id',
        'quantity',
        'status',
        'record_date',
        'created_by',
        'updated_by',
    ];

    // protected $casts = [
    //     'quantity' => 'decimal:10,3'
    // ];


    // // Accessor for quantity
    // public function getQuantityAttribute($value)
    // {
    //     return (float) $value;
    // }

    // // Mutator for quantity
    // public function setQuantityAttribute($value)
    // {
    //     $this->attributes['quantity'] = (string) $value;
    // }

    public function prod_order_materials()
    {
        return $this->hasMany(ProdOrdersMaterial::class, 'po_id', 'po_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'material_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
