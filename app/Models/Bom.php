<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'bom_id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'bom_id', 'material_id', 'uom_id', 'created_by', 'updated_by'
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'material_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function bomRecords()
    {
        return $this->hasMany(BomRecord::class, 'bom_id', 'bom_id');
    }
}
