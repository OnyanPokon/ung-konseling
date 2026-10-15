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
        $answerRule = 'required|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju,lainnya';

        if ($this->isMethod('post')) {
            return [
                'sesi_konseling_id' => 'required|exists:sesi_konselings,id',

                'q1_topik_sesuai' => $answerRule,
                'q1_lainnya' => 'nullable|required_if:q1_topik_sesuai,lainnya|string',

                'q2_pemahaman_baru' => $answerRule,
                'q2_lainnya' => 'nullable|required_if:q2_pemahaman_baru,lainnya|string',

                'q3_merasa_lebih_baik' => $answerRule,
                'q3_lainnya' => 'nullable|required_if:q3_merasa_lebih_baik,lainnya|string',

                'q4_rencana_tindakan' => $answerRule,
                'q4_lainnya' => 'nullable|required_if:q4_rencana_tindakan,lainnya|string',

                'q5_berkaitan_masalah' => $answerRule,
                'q5_lainnya' => 'nullable|required_if:q5_berkaitan_masalah,lainnya|string',

                'q6_manfaat_layanan' => $answerRule,
                'q6_lainnya' => 'nullable|required_if:q6_manfaat_layanan,lainnya|string',

                'q7_layanan_sudah_baik' => $answerRule,
                'q7_lainnya' => 'nullable|required_if:q7_layanan_sudah_baik,lainnya|string',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'sesi_konseling_id' => 'sometimes|exists:sesi_konselings,id',

                'q1_topik_sesuai' => 'sometimes|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju,lainnya',
                'q1_lainnya' => 'nullable|required_if:q1_topik_sesuai,lainnya|string',

                'q2_pemahaman_baru' => 'sometimes|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju,lainnya',
                'q2_lainnya' => 'nullable|required_if:q2_pemahaman_baru,lainnya|string',

                'q3_merasa_lebih_baik' => 'sometimes|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju,lainnya',
                'q3_lainnya' => 'nullable|required_if:q3_merasa_lebih_baik,lainnya|string',

                'q4_rencana_tindakan' => 'sometimes|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju,lainnya',
                'q4_lainnya' => 'nullable|required_if:q4_rencana_tindakan,lainnya|string',

                'q5_berkaitan_masalah' => 'sometimes|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju,lainnya',
                'q5_lainnya' => 'nullable|required_if:q5_berkaitan_masalah,lainnya|string',

                'q6_manfaat_layanan' => 'sometimes|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju,lainnya',
                'q6_lainnya' => 'nullable|required_if:q6_manfaat_layanan,lainnya|string',

                'q7_layanan_sudah_baik' => 'sometimes|in:sangat_setuju,setuju,tidak_setuju,sangat_tidak_setuju,lainnya',
                'q7_lainnya' => 'nullable|required_if:q7_layanan_sudah_baik,lainnya|string',
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
            'q1_lainnya.required_if' => 'Jawaban lainnya pertanyaan 1 wajib diisi.',

            'q2_pemahaman_baru.required' => 'Pertanyaan 2 wajib dijawab.',
            'q2_pemahaman_baru.in' => 'Jawaban pertanyaan 2 tidak valid.',
            'q2_lainnya.required_if' => 'Jawaban lainnya pertanyaan 2 wajib diisi.',

            'q3_merasa_lebih_baik.required' => 'Pertanyaan 3 wajib dijawab.',
            'q3_merasa_lebih_baik.in' => 'Jawaban pertanyaan 3 tidak valid.',
            'q3_lainnya.required_if' => 'Jawaban lainnya pertanyaan 3 wajib diisi.',

            'q4_rencana_tindakan.required' => 'Pertanyaan 4 wajib dijawab.',
            'q4_rencana_tindakan.in' => 'Jawaban pertanyaan 4 tidak valid.',
            'q4_lainnya.required_if' => 'Jawaban lainnya pertanyaan 4 wajib diisi.',

            'q5_berkaitan_masalah.required' => 'Pertanyaan 5 wajib dijawab.',
            'q5_berkaitan_masalah.in' => 'Jawaban pertanyaan 5 tidak valid.',
            'q5_lainnya.required_if' => 'Jawaban lainnya pertanyaan 5 wajib diisi.',

            'q6_manfaat_layanan.required' => 'Pertanyaan 6 wajib dijawab.',
            'q6_manfaat_layanan.in' => 'Jawaban pertanyaan 6 tidak valid.',
            'q6_lainnya.required_if' => 'Jawaban lainnya pertanyaan 6 wajib diisi.',

            'q7_layanan_sudah_baik.required' => 'Pertanyaan 7 wajib dijawab.',
            'q7_layanan_sudah_baik.in' => 'Jawaban pertanyaan 7 tidak valid.',
            'q7_lainnya.required_if' => 'Jawaban lainnya pertanyaan 7 wajib diisi.',
        ];
    }
}
