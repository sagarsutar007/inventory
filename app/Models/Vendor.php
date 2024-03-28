<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Vendor extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'vendors';
    protected $primaryKey = 'vendor_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'vendor_name',
        'vendor_address',
        'vendor_city',
        'created_at',
        'created_by',
        'updated_by',
        'vendor_code',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($vendor) {
            $latestVendor = self::orderBy('vendor_code', 'desc')->first();
            if ($latestVendor) {
                $lastCode = intval(substr($latestVendor->vendor_code, 4));
                $nextCode = $lastCode + 1;
            } else {
                $nextCode = 1;
            }
            $vendor->vendor_code = 'VND' . str_pad($nextCode, 5, '0', STR_PAD_LEFT);
        });
    }

    public function materialPurchase()
    {
        return $this->belongsTo(MaterialPurchase::class, 'material_id', 'material_id');
    }
}
