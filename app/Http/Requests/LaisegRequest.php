<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LaisegRequest extends FormRequest
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
                'sesi_konseling_id' => 'required|exists:sesi_konselings,id',

                'topik_pembahasan' => 'required|string',

                'pemahaman_baru' => 'required|string',

                'perasaan_setelah_layanan' => 'required|string',

                'rencana_setelah_layanan' => 'required|string',

                'apakah_terkait_masalah' => 'required|boolean',

                'keuntungan_jika_ya' => 'required|string',

                'keuntungan_jika_tidak' => 'required|string',

                'saran_pesan' => 'required|string',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'sesi_konseling_id.required' => 'Sesi konseling wajib diisi.',
                'sesi_konseling_id.exists' => 'Sesi konseling tidak ditemukan.',

                'topik_pembahasan.required' => 'Topik pembahasan wajib diisi.',
                'topik_pembahasan.string' => 'Topik pembahasan harus berupa teks.',

                'pemahaman_baru.required' => 'Pemahaman baru wajib diisi.',
                'pemahaman_baru.string' => 'Pemahaman baru harus berupa teks.',

                'perasaan_setelah_layanan.required' => 'Perasaan setelah layanan wajib diisi.',
                'perasaan_setelah_layanan.string' => 'Perasaan setelah layanan harus berupa teks.',

                'rencana_setelah_layanan.required' => 'Rencana setelah layanan wajib diisi.',
                'rencana_setelah_layanan.string' => 'Rencana setelah layanan harus berupa teks.',

                'apakah_terkait_masalah.required' => 'Pilihan terkait masalah wajib diisi.',
                'apakah_terkait_masalah.boolean' => 'Pilihan terkait masalah tidak valid.',

                'keuntungan_jika_ya.required' => 'Keuntungan jika ya wajib diisi.',
                'keuntungan_jika_ya.string' => 'Keuntungan jika ya harus berupa teks.',

                'keuntungan_jika_tidak.required' => 'Keuntungan jika tidak wajib diisi.',
                'keuntungan_jika_tidak.string' => 'Keuntungan jika tidak harus berupa teks.',

                'saran_pesan.required' => 'Saran dan pesan wajib diisi.',
                'saran_pesan.string' => 'Saran dan pesan harus berupa teks.',
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'sesi_layanan_id.required' => 'Sesi layanan wajib diisi.',
            'sesi_layanan_id.exists' => 'Sesi layanan tidak valid.',

            'topik_pembahasan.string' => 'Topik pembahasan harus berupa teks.',

            'pemahaman_baru.string' => 'Pemahaman baru harus berupa teks.',

            'perasaan_setelah_layanan.string' => 'Perasaan setelah layanan harus berupa teks.',

            'rencana_setelah_layanan.string' => 'Rencana setelah layanan harus berupa teks.',

            'apakah_terkait_masalah.boolean' => 'Field terkait masalah harus berupa true atau false.',

            'keuntungan_jika_ya.string' => 'Keuntungan jika ya harus berupa teks.',

            'keuntungan_jika_tidak.string' => 'Keuntungan jika tidak harus berupa teks.',

            'saran_pesan.string' => 'Saran atau pesan harus berupa teks.',
        ];
    }
}
