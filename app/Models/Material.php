<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'materials';
    protected $primaryKey = 'material_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'part_code',
        'description',
        'uom_id',
        'dm_id',
        'type',
        'make',
        'mpn',
        // 'opening_balance',
        'avg_price',
        'min_price',
        'max_price',
        're_order',
        'additional_notes',
        'commodity_id',
        'category_id',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'avg_price' => 'decimal:10,3',
        'min_price' => 'decimal:10,3',
        'max_price' => 'decimal:10,3',
        're_order' => 'decimal:10,3',
    ];

    public function attachments()
    {
        return $this->hasMany(MaterialAttachments::class, 'material_id', 'material_id');
    }

    public function purchases()
    {
        return $this->hasMany(MaterialPurchase::class, 'material_id', 'material_id');
    }

    public function uom()
    {
        return $this->belongsTo(UomUnit::class, 'uom_id', 'uom_id');
    }

    public function commodity()
    {
        return $this->belongsTo(Commodity::class, 'commodity_id', 'commodity_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class, 'material_id', 'material_id');
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'material_id', 'material_id');
    }

    public function bomRecord()
    {
        return $this->belongsTo(BomRecord::class, 'material_id', 'material_id');
    }

    public function dependant()
    {
        return $this->belongsTo(DependentMaterial::class, 'dm_id', 'dm_id');
    }

    public function vendors()
    {
        return $this->hasManyThrough(Vendor::class, MaterialPurchase::class, 'material_id', 'vendor_id', 'material_id', 'vendor_id');
    }
}
