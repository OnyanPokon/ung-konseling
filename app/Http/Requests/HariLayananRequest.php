<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HariLayananRequest extends FormRequest
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

        return [
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu,minggu|unique:hari_layanans,hari',
        ];
    }

    public function messages(): array
    {
        return [
            'hari.required' => 'Hari wajib diisi.',
            'hari.in' => 'Hari harus salah satu dari: senin, selasa, rabu, kamis, jumat, sabtu, minggu.',
            'hari.unique' => 'Hari sudah digunakan.',
        ];
    }
}
