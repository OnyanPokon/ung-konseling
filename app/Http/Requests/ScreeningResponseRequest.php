<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScreeningResponseRequest extends FormRequest
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
            'screening_id' => 'required|exists:screenings,id',
            'email' => 'required|email|max:255',
            'name' => 'required|string|max:255',
            'nim' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'major' => 'required|string|max:255',

            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:question_screenings,id',
            'answers.*.score' => 'required|integer|min:1|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'screening_id.required' => "Screening wajib diisi.",
            'screening_id.exists' => "Screening tidak valid.",
            'email.required' => "Email wajib diisi.",
            'email.email' => "Email tidak valid.",
            'email.max' => "Email maksimal 255 karakter.",
            'name.required' => "Nama wajib diisi.",
            'name.string' => "Nama harus berupa teks.",
            'name.max' => "Nama maksimal 255 karakter.",
            'nim.required' => "NIM wajib diisi.",
            'nim.string' => "NIM harus berupa teks.",
            'nim.max' => "NIM maksimal 255 karakter.",
            'institution.required' => "Institusi wajib diisi.",
            'institution.string' => "Institusi harus berupa teks.",
            'institution.max' => "Institusi maksimal 255 karakter.",
            'major.required' => "Jurusan wajib diisi.",
            'major.string' => "Jurusan harus berupa teks.",
            'major.max' => "Jurusan maksimal 255 karakter.",
            'answers.required' => "Jawaban wajib diisi.",
            'answers.array' => "Format data jawaban tidak valid.",
            'answers.*.question_id.required' => "ID pertanyaan wajib diisi pada setiap jawaban.",
            'answers.*.question_id.exists' => "Pertanyaan pada jawaban tidak valid.",
            'answers.*.score.required' => "Skor wajib diisi pada setiap jawaban.",
            'answers.*.score.integer' => "Skor harus berupa angka bulat.",
            'answers.*.score.min' => "Skor minimal adalah 1.",
            'answers.*.score.max' => "Skor maksimal adalah 10.",
        ];
    }
}
