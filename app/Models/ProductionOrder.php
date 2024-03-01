<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'po_id';

    public function prod_order_materials()
    {
        return $this->hasMany(ProdOrdersMaterial::class, 'po_id', 'po_id');
    }
}
