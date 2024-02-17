<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category_name',
        'category_number',
        'created_by',
        'updated_by',
    ];

    public function materials() 
    {
        return $this->belongsTo(Material::class, 'category_id', 'category_id');
    }
}
