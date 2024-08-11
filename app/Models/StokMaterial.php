<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokMaterial extends Model
{
    use HasFactory;

    protected $fillable = ['data_material_id', 'stok', 'maksimum_stok', 'status'];

    public function dataMaterial()
    {
        return $this->belongsTo(DataMaterial::class);
    }
}
