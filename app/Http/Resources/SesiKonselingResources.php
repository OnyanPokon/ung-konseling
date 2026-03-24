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
                'urgensi' => $this->tiket->urgensi,
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
            'catatan_konselor' => $this->catatan_konselor,
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
        ];
    }
}
