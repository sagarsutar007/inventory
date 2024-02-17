<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialAttachments extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'material_attachments';
    protected $primaryKey = 'mat_doc_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'path',
        'type',
        'material_id',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
