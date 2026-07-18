<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Scope: hanya data milik satu operator (diinput_oleh = $operatorId).
     * Dipakai di halaman "Data Neraca Saya".
     */
    public function scopeMilikOperator(Builder $query, int $operatorId): Builder
    {
        return $query->where('diinput_oleh', $operatorId);
    }

    /**
     * Scope: data yang sudah diajukan untuk verifikasi (bukan draft).
     */
    public function scopeSudahDiajukan(Builder $query): Builder
    {
        return $query->where('status', '!=', 'draft');
    }

    /**
     * Scope: data yang sudah selesai diverifikasi (valid atau perlu revisi).
     * Dipakai di halaman "Riwayat Verifikasi" milik verifikator.
     */
    public function scopeSudahDiverifikasi(Builder $query): Builder
    {
        return $query->whereIn('status', ['valid', 'revisi']);
    }

    /**
     * Nilai neraca: stok awal + produksi + masuk - keluar - kebutuhan RT - kebutuhan non-RT.
     * Accessor supaya bisa dipakai langsung sebagai $neraca->nilai_neraca di Blade,
     * selain lewat App\Http\Controllers\Admin\DataNeracaController::hitungNilaiNeraca().
     */
    public function getNilaiNeracaAttribute(): float
    {
        return (float) $this->stok_awal
            + (float) $this->produksi
            + (float) $this->masuk
            - (float) $this->keluar
            - (float) $this->kebutuhan_rumah_tangga
            - (float) $this->kebutuhan_non_rumah_tangga;
    }
}