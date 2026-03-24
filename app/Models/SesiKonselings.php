<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SesiKonselings extends Model
{
    protected $fillable = [
        'tiket_id',
        'konselor_id',
        'hari_layanan_id',
        'tanggal_konseling',
        'jam_mulai',
        'jam_selesai',
        'tempat',
        'catatan_konselor',
        'status'
    ];

    public function tiket()
    {
        return $this->belongsTo(tikets::class);
    }

    public function konselor()
    {
        return $this->belongsTo(Konselors::class);
    }

    public function hariLayanan()
    {
        return $this->belongsTo(HariLayanans::class);
    }

    public function laporan()
    {
        return $this->hasOne(LaporanKonseling::class, 'sesi_konseling_id');
    }
}
