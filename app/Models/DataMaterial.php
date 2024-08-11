<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_material',
        'kode_material',
        'created_by',
    ];

    public function materialMasuks()
    {
        return $this->hasMany(MaterialMasuk::class);
    }

    public function materialKeluars()
    {
        return $this->hasMany(MaterialKeluar::class);
    }

    public function stokMaterial()
    {
        return $this->hasOne(StokMaterial::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
