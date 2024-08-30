<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokMaterialRecord extends Model
{
    use HasFactory;

    protected $fillable = ['data_material_id', 'stok', 'waktu', 'created_by', 'status'];

    public function dataMaterial()
    {
        return $this->belongsTo(DataMaterial::class);
    }
}
