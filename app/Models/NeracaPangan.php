<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NeracaPangan extends Model
{
    protected $table = 'neraca_pangan';

    protected $fillable = [
        'komoditas_id',
        'periode',
        'stok_awal',
        'produksi',
        'masuk',
        'keluar',
        'kebutuhan_rumah_tangga',
        'kebutuhan_non_rumah_tangga',
        'status',
        'keterangan',
        'diinput_oleh',
        'diajukan_pada',
        'diverifikasi_oleh',
        'diverifikasi_pada',
    ];
    
    public function komoditas()
    {
        return $this->belongsTo(Komoditas::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function riwayatVerifikasi()
    {
        return $this->hasMany(RiwayatVerifikasiNeraca::class);
    }

}
