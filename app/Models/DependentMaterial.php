<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DependentMaterial extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'dependent_materials';
    protected $primaryKey = 'dm_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'description',
        'frequency',
        'created_by',
        'updated_by',
    ];

    public function materials()
    {
        return $this->hasMany(Material::class, 'dm_id', 'dm_id');
    }
}
