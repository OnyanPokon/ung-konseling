<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LaporanKonselingResource extends JsonResource
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
                'id' => $this->sesi?->tiket?->id,
                'konseli' => [
                    'id' => $this->sesi?->tiket?->konseli?->id,
                    'nim' => $this->sesi?->tiket?->konseli?->nim,
                    'phone' => $this->sesi?->tiket?->konseli?->phone,
                    'user' => [
                        'id' => $this->sesi?->tiket?->konseli?->user?->id,
                        'nama' => $this->sesi?->tiket?->konseli?->user?->name,
                        'email' => $this->sesi?->tiket?->konseli?->user?->email,
                    ],
                    'domisili' => $this->sesi?->tiket?->konseli?->domisili,
                    'jurusan' => $this->sesi?->tiket?->konseli?->jurusan,
                    'umur' => $this->sesi?->tiket?->konseli?->umur,
                    'jenis_kelamin' => $this->sesi?->tiket?->konseli?->jenis_kelamin,
                ],
            ],
            'konselor' =>  $this->konselor->user->name,

            'nama_kegiatan' => $this->nama_kegiatan,
            'jenis_layanan' => $this->jenis_layanan,
            'tujuan_kegiatan' => $this->tujuan_kegiatan,
            'waktu_tempat' => $this->waktu_tempat,
            'jumlah_peserta' => $this->jumlah_peserta,

            'uraian_kegiatan' => $this->uraian_kegiatan,
            'hasil_dampak' => $this->hasil_dampak,
            'rekomendasi' => $this->rekomendasi,

            'status' => $this->status,

            'file_url' => $this->file_path
                ? asset('storage/' . $this->file_path)
                : null,

            'created_at' => $this->created_at?->format('d F Y'),
            'updated_at' => $this->updated_at?->format('d F Y'),
        ];
    }
}
