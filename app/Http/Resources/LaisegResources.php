<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LaisegResources extends JsonResource
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
            'q1_topik_sesuai' => $this->q1_topik_sesuai,
            'q2_pemahaman_baru' => $this->q2_pemahaman_baru,
            'q3_merasa_lebih_baik' => $this->q3_merasa_lebih_baik,
            'q4_rencana_tindakan' => $this->q4_rencana_tindakan,
            'q5_berkaitan_masalah' => $this->q5_berkaitan_masalah,
            'q6_manfaat_layanan' => $this->q6_manfaat_layanan,
            'q7_layanan_sudah_baik' => $this->q7_layanan_sudah_baik,
            'catatan_lainnya' => $this->catatan_lainnya,
            'sesi_konseling' => [
                'id' => $this->sesi_konseling->id,
                'tiket' => [
                    'id' => $this->sesi_konseling->tiket->id,
                    'nomor_tiket' => $this->sesi_konseling->tiket->nomor_tiket,
                    'deskripsi_keluhan' => $this->sesi_konseling->tiket->deskripsi_keluhan,
                    'status' => $this->sesi_konseling->tiket->status,
                    'jenis_layanan' => $this->sesi_konseling->tiket->jenis_layanan,
                    'jenis_keluhan' => $this->sesi_konseling->tiket->jenis_keluhan,
                    'created_at' => $this->sesi_konseling->tiket->created_at->format('d F Y'),
                    'konseli' => [
                        'id' => $this->tiket->konseli->id,
                        'nim' => $this->sesi_konseling->tiket->konseli->nim,
                        'phone' => $this->sesi_konseling->tiket->konseli->phone,
                        'user' => [
                            'id' => $this->sesi_konseling->tiket->konseli->user->id,
                            'nama' => $this->sesi_konseling->tiket->konseli->user->name,
                            'email' => $this->sesi_konseling->tiket->konseli->user->email,
                        ],
                    ],
                ],
                'konselor' => [
                    'id' => $this->sesi_konseling->konselor->id,
                    'is_active' => $this->sesi_konseling->konselor->is_active,
                    'user' => [
                        'id' => $this->sesi_konseling->konselor->user->id,
                        'nama' => $this->sesi_konseling->konselor->user->name,
                        'email' => $this->sesi_konseling->konselor->user->email,
                    ],
                ],
                'hari_layanan' => [
                    'id' => $this->sesi_konseling->hariLayanan->id,
                    'hari' => $this->sesi_konseling->hariLayanan->hari,
                ],
                'tanggal_konseling' => $this->sesi_konseling->tanggal_konseling,
                'jam_mulai' => $this->sesi_konseling->jam_mulai,
                'jam_selesai' => $this->sesi_konseling->jam_selesai,
                'tempat' => $this->sesi_konseling->tempat,
                'status' => $this->sesi_konseling->status,
            ],

        ];
    }
}
