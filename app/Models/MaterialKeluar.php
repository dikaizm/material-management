<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialKeluar extends Model
{
    use HasFactory;

    protected $fillable = ['waktu', 'data_material_id', 'jumlah', 'satuan', 'created_by', 'record_id', 'status'];

    public function dataMaterial()
    {
        return $this->belongsTo(DataMaterial::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function record()
    {
        return $this->belongsTo(StokMaterialRecord::class, 'record_id');
    }
}
