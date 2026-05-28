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
            'topik_pembahasan' => $this->topik_pembahasan,
            'pemahaman_baru' => $this->pemahaman_baru,
            'perasaan_setelah_layanan' => $this->perasaan_setelah_layanan,
            'rencana_setelah_layanan' => $this->rencana_setelah_layanan,
            'apakah_terkait_masalah' => $this->apakah_terkait_masalah,
            'keuntungan_jika_ya' => $this->keuntungan_jika_ya,
            'keuntungan_jika_tidak' => $this->keuntungan_jika_tidak,
            'saran_pesan' => $this->saran_pesan,
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
                        ]
                    ]
                ],
                'konselor' => [
                    'id' => $this->sesi_konseling->konselor->id,
                    'is_active' => $this->sesi_konseling->konselor->is_active,
                    'user' => [
                        'id' => $this->sesi_konseling->konselor->user->id,
                        'nama' => $this->sesi_konseling->konselor->user->name,
                        'email' => $this->sesi_konseling->konselor->user->email,
                    ]
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
