<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Komoditas extends Model
{
    protected $table = 'komoditas';

    protected $fillable = [
        'nama'
    ];

    public function neracaPangan()
    {
    return $this->hasMany(NeracaPangan::class);
    }
}
