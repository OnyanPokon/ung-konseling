<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalKonselors extends Model
{
    protected $fillable = [
        'konselor_id',
        'hari_layanan_id',
    ];

    public function konselor()
    {
        return $this->belongsTo(Konselors::class);
    }

    public function hariLayanan()
    {
        return $this->belongsTo(HariLayanans::class);
    }
}
