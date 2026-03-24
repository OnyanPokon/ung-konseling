<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionRequest extends FormRequest
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
                'assessment_id' => 'required|exists:assessments,id',
                'question_text' => 'required|string',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'assessment_id' => 'sometimes|required|exists:assessments,id',
                'question_text' => 'sometimes|required|string',
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'assessment_id.required' => "Assessment wajib diisi.",
            'assessment_id.exists' => "Assessment tidak valid.",
            'question_text.required' => "Teks pertanyaan wajib diisi.",
            'question_text.string' => "Teks pertanyaan harus berupa teks.",
        ];
    }
}
