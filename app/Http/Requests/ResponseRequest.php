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
            'age' => 'required|integer|min:1|max:100',
            'parent_job' => 'required|string|max:255',
            'domisili' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'job' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.score' => 'required|integer|min:1|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'assessment_id.required' => 'Assessment wajib diisi.',
            'assessment_id.exists' => 'Assessment tidak valid.',
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'age.required' => 'Umur wajib diisi.',
            'age.integer' => 'Umur harus berupa angka bulat.',
            'age.min' => 'Umur minimal 1 tahun.',
            'age.max' => 'Umur maksimal 100 tahun.',
            'parent_job.required' => 'Pekerjaan orang tua wajib diisi.',
            'parent_job.string' => 'Pekerjaan orang tua harus berupa teks.',
            'parent_job.max' => 'Pekerjaan orang tua maksimal 255 karakter.',
            'domisili.required' => 'Domisili wajib diisi.',
            'domisili.string' => 'Domisili harus berupa teks.',
            'domisili.max' => 'Domisili maksimal 255 karakter.',
            'gender.required' => 'Jenis kelamin wajib diisi.',
            'gender.string' => 'Jenis kelamin harus berupa teks.',
            'gender.max' => 'Jenis kelamin maksimal 255 karakter.',
            'job.required' => 'Pekerjaan wajib diisi.',
            'job.string' => 'Pekerjaan harus berupa teks.',
            'job.max' => 'Pekerjaan maksimal 255 karakter.',
            'institution.required' => 'Institusi wajib diisi.',
            'institution.string' => 'Institusi harus berupa teks.',
            'institution.max' => 'Institusi maksimal 255 karakter.',
            'answers.required' => 'Jawaban wajib diisi.',
            'answers.array' => 'Format data jawaban tidak valid.',
            'answers.*.question_id.required' => 'ID pertanyaan wajib diisi pada setiap jawaban.',
            'answers.*.question_id.exists' => 'Pertanyaan pada jawaban tidak valid.',
            'answers.*.score.required' => 'Skor wajib diisi pada setiap jawaban.',
            'answers.*.score.integer' => 'Skor harus berupa angka bulat.',
            'answers.*.score.min' => 'Skor minimal adalah 1.',
            'answers.*.score.max' => 'Skor maksimal adalah 10.',
        ];
    }
}
