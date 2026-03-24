<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssessmentRequest extends FormRequest
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
                'period_id' => 'required|exists:periods,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_published' => 'required|boolean',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'period_id' => 'sometimes|required|exists:periods,id',
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'is_published' => 'sometimes|required|boolean',
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'period_id.required' => "Periode wajib diisi.",
            'period_id.exists' => "Periode tidak valid.",
            'title.required' => "Judul assessment wajib diisi.",
            'title.string' => "Judul assessment harus berupa teks.",
            'title.max' => "Judul assessment maksimal 255 karakter.",
            'description.string' => "Deskripsi harus berupa teks.",
            'is_published.required' => "Status publish wajib diisi.",
            'is_published.boolean' => "Status publish harus berupa boolean.",
        ];
    }
}
