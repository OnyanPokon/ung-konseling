<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResponseRequest extends FormRequest
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
            'assessment_id' => 'required|exists:assessments,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'institution' => 'nullable|string|max:255',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.score' => 'required|integer|min:1|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'assessment_id.required' => "Assessment wajib diisi.",
            'assessment_id.exists' => "Assessment tidak valid.",
            'name.required' => "Nama wajib diisi.",
            'name.string' => "Nama harus berupa teks.",
            'name.max' => "Nama maksimal 255 karakter.",
            'email.email' => "Format email tidak valid.",
            'institution.string' => "Institusi harus berupa teks.",
            'institution.max' => "Institusi maksimal 255 karakter.",
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
