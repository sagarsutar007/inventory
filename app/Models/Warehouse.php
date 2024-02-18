<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Warehouse extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'warehouse';
    protected $primaryKey = 'warehouse_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'warehouse_id',
        'part_code',
        'quantity',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'material_id');
    }

    public function uom()
    {
        return $this->belongsTo(UomUnit::class, 'uom_id', 'uom_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function records()
    {
        return $this->hasMany(WarehouseRecord::class, 'warehouse_id', 'warehouse_id');
    }
}
