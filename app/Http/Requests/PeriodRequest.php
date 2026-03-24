<?php

namespace App\Http\Requests;

use App\Models\Konselors;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PeriodRequest extends FormRequest
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
                'name' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'name' => 'sometimes|required|string',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date',
            ];
        }


        return [];
    }

    public function messages(): array
    {
        return [
            'name.required' => "Nama periode wajib diisi.",
            'name.string' => "Nama periode harus berupa teks.",
            'start_date.required' => "Tanggal mulai wajib diisi.",
            'start_date.date' => "Tanggal mulai harus berupa tanggal.",
            'end_date.required' => "Tanggal selesai wajib diisi.",
            'end_date.date' => "Tanggal selesai harus berupa tanggal.",
        ];
    }
}
