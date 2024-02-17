<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Commodity extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'commodities';
    protected $primaryKey = 'commodity_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'commodity_name',
        'commodity_number',
        'created_by',
        'updated_by',
    ];

    public function materials() 
    {
        return $this->belongsTo(Material::class, 'commodity_id', 'commodity_id');
    }
}
