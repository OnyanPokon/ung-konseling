<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laisegs extends Model
{
    protected $fillable = [
        'sesi_konseling_id',
        'q1_topik_sesuai',
        'q2_pemahaman_baru',
        'q3_merasa_lebih_baik',
        'q4_rencana_tindakan',
        'q5_berkaitan_masalah',
        'q6_manfaat_layanan',
        'q7_layanan_sudah_baik',
        'catatan_lainnya',
    ];

    public function sesiKonseling()
    {
        return $this->belongsTo(SesiKonselings::class, 'sesi_konseling_id');
    }
}
