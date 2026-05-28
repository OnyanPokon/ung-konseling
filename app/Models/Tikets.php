<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tikets extends Model
{
    protected $fillable = [
        'nomor_tiket',
        'konseli_id',
        'konselor_id',
        'hari_layanan_id',
        'deskripsi_keluhan',
        'status',
        'jenis_keluhan',
        'jenis_layanan',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {

            $year = now()->format('Y'); // contoh: 2024

            do {
                $random = strtoupper(Str::random(8)); // 8 karakter alphanumeric
                $nomor = $year . $random; // 2024XXXXXXXX
            } while (self::where('nomor_tiket', $nomor)->exists());

            $model->nomor_tiket = $nomor;
        });
    }
    public function konseli()
    {
        return $this->belongsTo(konselis::class);
    }

    public function konselor()
    {
        return $this->belongsTo(konselors::class);
    }

    public function hariLayanan()
    {
        return $this->belongsTo(HariLayanans::class);
    }

    public function sesiKonseling()
    {
        return $this->hasOne(SesiKonselings::class);
    }
}
