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
                    'user' => [
                        'id' => $this->tiket->konseli->user->id,
                        'nama' => $this->tiket->konseli->user->name,
                        'email' => $this->tiket->konseli->user->email,
                    ]
                ]
            ],
            'konselor' => [
                'id' => $this->konselor->id,
                'is_active' => $this->konselor->is_active,
                'user' => [
                    'id' => $this->konselor->user->id,
                    'nama' => $this->konselor->user->name,
                    'email' => $this->konselor->user->email,
                ]
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
                    ? asset('storage/' . $this->laporan->file_path)
                    : null,
            ],
            'laiseg' => [
                'id' => $this->laiseg?->id,
                'topik_pembahasan' => $this->laiseg?->topik_pembahasan,
                'pemahaman_baru' => $this->laiseg?->pemahaman_baru,
                'perasaan_setelah_layanan' => $this->laiseg?->perasaan_setelah_layanan,
                'rencana_setelah_layanan' => $this->laiseg?->rencana_setelah_layanan,
                'apakah_terkait_masalah' => $this->laiseg?->apakah_terkait_masalah,
                'keuntungan_jika_ya' => $this->laiseg?->keuntungan_jika_ya,
                'keuntungan_jika_tidak' => $this->laiseg?->keuntungan_jika_tidak,
                'saran_pesan' => $this->laiseg?->saran_pesan,
            ],
        ];
    }
}
