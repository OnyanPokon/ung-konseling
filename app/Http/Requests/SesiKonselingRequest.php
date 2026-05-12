<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SesiKonselingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        if ($this->isMethod('post')) {
            return [
                'tiket_id' => 'required|exists:tikets,id',
                'konselor_id' => 'required|exists:konselors,id',
                'hari_layanan_id' => 'required|exists:hari_layanans,id',
                'tanggal_konseling' => 'required|date',
                'jam_mulai' => 'required|date_format:H:i',
                'jam_selesai' => 'required|date_format:H:i',
                'tempat' => 'required|string',
                'status' => 'required|in:dijadwalkan,selesai,dijadwalkan_ulang,dibatalkan',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'tiket_id' => 'sometimes|required|exists:tikets,id',
                'konselor_id' => 'sometimes|required|exists:konselors,id',
                'hari_layanan_id' => 'sometimes|required|exists:hari_layanans,id',
                'tanggal_konseling' => 'sometimes|required|date',
                'jam_mulai' => 'sometimes|required|date_format:H:i',
                'jam_selesai' => 'sometimes|required|date_format:H:i',
                'tempat' => 'sometimes|required|string',
                'status' => 'sometimes|required|in:dijadwalkan,selesai,dijadwalkan_ulang,dibatalkan',
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'tiket_id.required' => 'ID tiket wajib diisi.',
            'tiket_id.exists' => 'ID tiket tidak ditemukan.',
            'konselor_id.required' => 'ID konselor wajib diisi.',
            'konselor_id.exists' => 'ID konselor tidak ditemukan.',
            'hari_layanan_id.required' => 'ID hari layanan wajib diisi.',
            'hari_layanan_id.exists' => 'ID hari layanan tidak ditemukan.',
            'tanggal_konseling.required' => 'Tanggal konseling wajib diisi.',
            'tanggal_konseling.date' => 'Format tanggal konseling tidak valid.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.date_format' => 'Format jam selesai tidak valid.',
            'tempat.required' => 'Tempat wajib diisi.',
            'tempat.string' => 'Tempat harus berupa string.',
            'catatan_konselor.required' => 'Catatan konselor wajib diisi.',
            'catatan_konselor.string' => 'Catatan konselor harus berupa string.',
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status harus salah satu dari: dijadwalkan, selesai, dijadwalkan_ulang, dibatalkan.',
        ];
    }
}
