<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laisegs extends Model
{
    protected $fillable = [
        'sesi_konseling_id',
        'q1_topik_sesuai',
        'q1_lainnya',
        'q2_pemahaman_baru',
        'q2_lainnya',
        'q3_merasa_lebih_baik',
        'q3_lainnya',
        'q4_rencana_tindakan',
        'q4_lainnya',
        'q5_berkaitan_masalah',
        'q5_lainnya',
        'q6_manfaat_layanan',
        'q6_lainnya',
        'q7_layanan_sudah_baik',
        'q7_lainnya',
    ];

    public function sesiKonseling()
    {
        return $this->belongsTo(SesiKonselings::class, 'sesi_konseling_id');
    }
}
