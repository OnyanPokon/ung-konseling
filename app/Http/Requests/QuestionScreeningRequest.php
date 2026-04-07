<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionScreeningRequest extends FormRequest
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
                'screening_id' => 'required|exists:screenings,id',
                'question_text' => 'required|string',
                'scale' => 'required|integer|min:1|max:10'
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'screening_id' => 'sometimes|required|exists:screenings,id',
                'question_text' => 'sometimes|required|string',
                'scale' => 'sometimes|integer|min:1|max:10'
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'screening_id.required' => "Assessment wajib diisi.",
            'screening_id.exists' => "Assessment tidak valid.",
            'question_text.required' => "Teks pertanyaan wajib diisi.",
            'question_text.string' => "Teks pertanyaan harus berupa teks.",
            'scale.required' => "Scale wajib diisi.",
            'scale.integer' => "Scale harus berupa angka.",

        ];
    }
}
