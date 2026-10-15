<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SesiKonselingResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tiket' => [
                'id' => $this->tiket->id,
                'nomor_tiket' => $this->tiket->nomor_tiket,
                'deskripsi_keluhan' => $this->tiket->deskripsi_keluhan,
                'status' => $this->tiket->status,
                'jenis_layanan' => $this->tiket->jenis_layanan,
                'jenis_keluhan' => $this->tiket->jenis_keluhan,
                'created_at' => $this->tiket->created_at->format('d F Y'),
                'konseli' => [
                    'id' => $this->tiket->konseli->id,
                    'nim' => $this->tiket->konseli->nim,
                    'phone' => $this->tiket->konseli->phone,
                    'domisili' => $this->tiket->konseli->domisili,
                    'jurusan' => $this->tiket->konseli->jurusan,
                    'umur' => $this->tiket->konseli->umur,
                    'jenis_kelamin' => $this->tiket->konseli->jenis_kelamin,

                    'user' => [
                        'id' => $this->tiket->konseli->user->id,
                        'nama' => $this->tiket->konseli->user->name,
                        'email' => $this->tiket->konseli->user->email,
                    ],
                ],
            ],
            'konselor' => [
                'id' => $this->konselor->id,
                'is_active' => $this->konselor->is_active,
                'user' => [
                    'id' => $this->konselor->user->id,
                    'nama' => $this->konselor->user->name,
                    'email' => $this->konselor->user->email,
                ],
            ],
            'hari_layanan' => [
                'id' => $this->hariLayanan->id,
                'hari' => $this->hariLayanan->hari,
            ],
            'tanggal_konseling' => $this->tanggal_konseling,
            'jam_mulai' => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'tempat' => $this->tempat,
            'status' => $this->status,
            'created_at' => $this->created_at->format('d F Y'),
            'updated_at' => $this->updated_at->format('d F Y'),
            'laporan' => [
                'id' => $this->laporan?->id,
                'status' => $this->laporan?->status,
                'file_url' => $this->laporan?->file_path
                    ? asset('storage/'.$this->laporan->file_path)
                    : null,
            ],
            'laiseg' => [
                'id' => $this->laiseg?->id,
                'q1_topik_sesuai' => $this->laiseg?->q1_topik_sesuai,
                'q1_lainnya' => $this->laiseg?->q1_lainnya,
                'q2_pemahaman_baru' => $this->laiseg?->q2_pemahaman_baru,
                'q2_lainnya' => $this->laiseg?->q2_lainnya,
                'q3_merasa_lebih_baik' => $this->laiseg?->q3_merasa_lebih_baik,
                'q3_lainnya' => $this->laiseg?->q3_lainnya,
                'q4_rencana_tindakan' => $this->laiseg?->q4_rencana_tindakan,
                'q4_lainnya' => $this->laiseg?->q4_lainnya,
                'q5_berkaitan_masalah' => $this->laiseg?->q5_berkaitan_masalah,
                'q5_lainnya' => $this->laiseg?->q5_lainnya,
                'q6_manfaat_layanan' => $this->laiseg?->q6_manfaat_layanan,
                'q6_lainnya' => $this->laiseg?->q6_lainnya,
                'q7_layanan_sudah_baik' => $this->laiseg?->q7_layanan_sudah_baik,
                'q7_lainnya' => $this->laiseg?->q7_lainnya,
            ],
        ];
    }
}
