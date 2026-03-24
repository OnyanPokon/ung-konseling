<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanKonseling extends Model
{
    protected $table = 'laporan_konseling';

    protected $fillable = [
        'sesi_konseling_id',
        'konselor_id',
        'nama_kegiatan',
        'jenis_layanan',
        'tujuan_kegiatan',
        'waktu_tempat',
        'jumlah_peserta',
        'uraian_kegiatan',
        'hasil_dampak',
        'rekomendasi',
        'html_content',
        'file_path',
        'status',
    ];

    public function sesi()
    {
        return $this->belongsTo(SesiKonselings::class, 'sesi_konseling_id');
    }

    public function konselor()
    {
        return $this->belongsTo(Konselors::class);
    }
}
