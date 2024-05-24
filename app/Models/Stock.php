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

    // protected $casts = [
    //     'opening_balance' => 'decimal:10,3',
    //     'receipt_qty' => 'decimal:10,3',
    //     'issue_qty' => 'decimal:10,3',
    //     'closing_balance' => 'decimal:10,3',
    // ];

    // public function getClosingBalanceAttribute($value)
    // {
    //     return (float) $value;
    // }

    // public function setClosingBalanceAttribute($value)
    // {
    //     $this->attributes['closing_balance'] = (string) $value;
    // }

    // public function getOpeningBalanceAttribute($value)
    // {
    //     return (float) $value;
    // }

    // public function setOpeningBalanceAttribute($value)
    // {
    //     $this->attributes['opening_balance'] = (string) $value;
    // }

    // public function getReceiptQtyAttribute($value)
    // {
    //     return (float) $value;
    // }

    // public function setReceiptQtyAttribute($value)
    // {
    //     $this->attributes['receipt_qty'] = (string) $value;
    // }

    // public function getIssueQtyAttribute($value)
    // {
    //     return (float) $value;
    // }

    // public function setIssueQtyAttribute($value)
    // {
    //     $this->attributes['issue_qty'] = (string) $value;
    // }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'material_id');
    }
}
