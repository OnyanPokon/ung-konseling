<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laisegs extends Model
{

    protected $fillable = [
        'sesi_konseling_id',
        'topik_pembahasan',
        'pemahaman_baru',
        'perasaan_setelah_layanan',
        'rencana_setelah_layanan',
        'apakah_terkait_masalah',
        'keuntungan_jika_ya',
        'keuntungan_jika_tidak',
        'saran_pesan',
    ];

    public function sesiKonseling()
    {
        return $this->belongsTo(SesiKonselings::class, 'sesi_konseling_id');
    }
}
