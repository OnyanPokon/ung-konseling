<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TiketResources extends JsonResource
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
            'nomor_tiket' => $this->nomor_tiket,
            'konseli' => [
                'id' => $this->konseli->id,
                'nim' => $this->konseli->nim,
                'phone' => $this->konseli->phone,
                'user' => [
                    'id' => $this->konseli->user->id,
                    'nama' => $this->konseli->user->name,
                    'email' => $this->konseli->user->email,
                ],
                'domisili' => $this->konseli->domisili,
                'jurusan' => $this->konseli->jurusan,
                'umur' => $this->konseli->umur,
                'jenis_kelamin' => $this->konseli->jenis_kelamin,

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
            'deskripsi_keluhan' => $this->deskripsi_keluhan,
            'status' => $this->status,
            'jenis_layanan' => $this->jenis_layanan,
            'jenis_keluhan' => $this->jenis_keluhan,
            'created_at' => $this->created_at->format('d F Y'),
            'updated_at' => $this->updated_at->format('d F Y'),
        ];
    }
}
