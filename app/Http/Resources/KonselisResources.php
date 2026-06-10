<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KonselisResources extends JsonResource
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
            'nim' => $this->nim,
            'phone' => $this->phone,
            'domisili' => $this->domisili,
            'jurusan' => $this->jurusan,
            'umur' => $this->umur,
            'jenis_kelamin' => $this->jenis_kelamin,
            'created_at' => $this->created_at->format('d F Y'),
            'updated_at' => $this->updated_at->format('d F Y'),
            
        ];
    }
}
