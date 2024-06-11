<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'stocks';
    protected $primaryKey = 'stock_id';
    public $incrementing = false;
    protected $keyType = 'string';


    protected $fillable = [
        'opening_balance',
        'receipt_qty',
        'issue_qty',
        'material_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];


    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'material_id');
    }
}
