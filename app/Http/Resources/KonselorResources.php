<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KonselorResources extends JsonResource
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
            'user' => [
                'id' => $this->user->id,
                'nama' => $this->user->name,
                'email' => $this->user->email,
            ],
            'nip' => $this->nip,
            'phone' => $this->phone,
            'jenis_kelamin' => $this->jenis_kelamin,
            'foto_profil' => $this->foto_profil,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->format('d F Y'),
            'updated_at' => $this->updated_at->format('d F Y'),
        ];
    }
}
