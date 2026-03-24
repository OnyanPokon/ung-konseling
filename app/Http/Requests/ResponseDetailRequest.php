<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResponseDetailRequest extends FormRequest
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
                'response_id' => 'required|exists:responses,id',
                'question_id' => 'required|exists:questions,id',
                'score' => 'required|integer',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'response_id' => 'sometimes|required|exists:responses,id',
                'question_id' => 'sometimes|required|exists:questions,id',
                'score' => 'sometimes|required|integer',
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'response_id.required' => "Sesi respon wajib diisi.",
            'response_id.exists' => "Respon tidak valid.",
            'question_id.required' => "Pertanyaan wajib diisi.",
            'question_id.exists' => "Pertanyaan tidak valid.",
            'score.required' => "Skor wajib diisi.",
            'score.integer' => "Skor harus berupa angka.",
        ];
    }
}
