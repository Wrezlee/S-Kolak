<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatVerifikasiNeraca extends Model
{
    protected $table = 'riwayat_verifikasi_neraca';

    protected $fillable = [
        'neraca_pangan_id',
        'verifikator_id',
        'status_lama',
        'status_baru',
        'catatan',
        'tanggal_verifikasi',
    ];

    protected $casts = [
        'tanggal_verifikasi' => 'datetime',
    ];

    public function neracaPangan(): BelongsTo
    {
        return $this->belongsTo(NeracaPangan::class);
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }
}
