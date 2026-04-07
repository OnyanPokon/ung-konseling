<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScreeningResponseDetailRequest extends FormRequest
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
                'screening_response_id' => 'required|exists:screening_responses,id',
                'screening_question_id' => 'required|exists:question_screenings,id',
                'score' => 'required|integer|min:1|max:10',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'screening_response_id' => 'sometimes|required|exists:screening_responses,id',
                'screening_question_id' => 'sometimes|required|exists:question_screenings,id',
                'score' => 'sometimes|required|integer|min:1|max:10',
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'screening_response_id.required' => "Sesi respon wajib diisi.",
            'screening_response_id.exists' => "Respon tidak valid.",
            'screening_question_id.required' => "Pertanyaan wajib diisi.",
            'screening_question_id.exists' => "Pertanyaan tidak valid.",
            'score.required' => "Skor wajib diisi.",
            'score.integer' => "Skor harus berupa angka.",
        ];
    }
}
