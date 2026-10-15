<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LaisegRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $answerRule = 'required|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju';

        if ($this->isMethod('post')) {
            return [
                'sesi_konseling_id' => 'required|exists:sesi_konselings,id',

                'q1_topik_sesuai' => $answerRule,
                'q2_pemahaman_baru' => $answerRule,
                'q3_merasa_lebih_baik' => $answerRule,
                'q4_rencana_tindakan' => $answerRule,
                'q5_berkaitan_masalah' => $answerRule,
                'q6_manfaat_layanan' => $answerRule,
                'q7_layanan_sudah_baik' => $answerRule,
                'catatan_lainnya' => 'nullable|string',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'sesi_konseling_id' => 'sometimes|exists:sesi_konselings,id',

                'q1_topik_sesuai' => 'sometimes|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju',
                'q2_pemahaman_baru' => 'sometimes|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju',
                'q3_merasa_lebih_baik' => 'sometimes|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju',
                'q4_rencana_tindakan' => 'sometimes|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju',
                'q5_berkaitan_masalah' => 'sometimes|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju',
                'q6_manfaat_layanan' => 'sometimes|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju',
                'q7_layanan_sudah_baik' => 'sometimes|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju',
                'catatan_lainnya' => 'nullable|string',
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'sesi_konseling_id.required' => 'Sesi konseling wajib diisi.',
            'sesi_konseling_id.exists' => 'Sesi konseling tidak ditemukan.',

            'q1_topik_sesuai.required' => 'Pertanyaan 1 wajib dijawab.',
            'q1_topik_sesuai.in' => 'Jawaban pertanyaan 1 tidak valid.',

            'q2_pemahaman_baru.required' => 'Pertanyaan 2 wajib dijawab.',
            'q2_pemahaman_baru.in' => 'Jawaban pertanyaan 2 tidak valid.',

            'q3_merasa_lebih_baik.required' => 'Pertanyaan 3 wajib dijawab.',
            'q3_merasa_lebih_baik.in' => 'Jawaban pertanyaan 3 tidak valid.',

            'q4_rencana_tindakan.required' => 'Pertanyaan 4 wajib dijawab.',
            'q4_rencana_tindakan.in' => 'Jawaban pertanyaan 4 tidak valid.',

            'q5_berkaitan_masalah.required' => 'Pertanyaan 5 wajib dijawab.',
            'q5_berkaitan_masalah.in' => 'Jawaban pertanyaan 5 tidak valid.',

            'q6_manfaat_layanan.required' => 'Pertanyaan 6 wajib dijawab.',
            'q6_manfaat_layanan.in' => 'Jawaban pertanyaan 6 tidak valid.',

            'q7_layanan_sudah_baik.required' => 'Pertanyaan 7 wajib dijawab.',
            'q7_layanan_sudah_baik.in' => 'Jawaban pertanyaan 7 tidak valid.',

            'catatan_lainnya.string' => 'Catatan lainnya harus berupa teks.',
        ];
    }
}
