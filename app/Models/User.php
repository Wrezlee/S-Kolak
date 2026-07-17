<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'login_id',
    'name',
    'password',
    'role',
    'status',
    'last_login_at',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Attribute casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Menggunakan login_id sebagai username untuk autentikasi.
     */
    public function getAuthIdentifierName(): string
    {
        return 'login_id';
    }

    /**
     * Data neraca yang diinput oleh operator.
     */
    public function dataNeraca(): HasMany
    {
        return $this->hasMany(NeracaPangan::class, 'diinput_oleh');
    }

    /**
     * Data neraca yang diverifikasi oleh verifikator.
     */
    public function verifikasiNeraca(): HasMany
    {
        return $this->hasMany(NeracaPangan::class, 'diverifikasi_oleh');
    }

    /**
     * Notifikasi milik user.
     */
    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class);
    }

    /**
     * Riwayat verifikasi yang dilakukan user.
     */
    public function riwayatVerifikasi(): HasMany
    {
        return $this->hasMany(RiwayatVerifikasiNeraca::class, 'verifikator_id');
    }
}