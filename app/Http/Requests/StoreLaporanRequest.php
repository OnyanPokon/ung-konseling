<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanRequest extends FormRequest
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
    public function rules()
    {
        return [
            'jenis_layanan' => 'required|in:dasar,responsif,perencanaan_individual,dukungan_sistem',
            'tujuan_kegiatan' => 'nullable|string',
            'uraian_kegiatan' => 'nullable|string',
            'hasil_dampak' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_layanan.required' => 'Jenis layanan wajib diisi.',
            'jenis_layanan.in' => 'Jenis layanan tidak valid.',
            'tujuan_kegiatan.string' => 'Tujuan kegiatan harus berupa teks.',
            'uraian_kegiatan.string' => 'Uraian kegiatan harus berupa teks.',
            'hasil_dampak.string' => 'Hasil dampak harus berupa teks.',
            'rekomendasi.string' => 'Rekomendasi harus berupa teks.',
        ];
    }
}
