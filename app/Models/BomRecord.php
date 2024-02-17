<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomRecord extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'bom_record_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'bom_record_id', 'bom_id', 'material_id', 'quantity'
    ];

    public function bom()
    {
        return $this->belongsTo(Bom::class, 'bom_id', 'bom_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'material_id');
    }
}
