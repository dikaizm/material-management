<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialMasuk extends Model
{
    use HasFactory;

    protected $fillable = ['waktu', 'data_material_id', 'jumlah', 'satuan'];

    public function dataMaterial()
    {
        return $this->belongsTo(DataMaterial::class);
    }
}
