<?php

namespace App\Http\Requests;

use App\Models\Konselis;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArtikelRequest extends FormRequest
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
        $docRule = $this->isMethod('POST') ? 'required' : 'nullable';


        if ($this->isMethod('post')) {
            return [
                'judul' => 'required|string',
                'konten' => 'required|string',
                'thumbnail' => "$docRule|mimes:jpg,png,jpeg|max:2048",
                'status' => 'required|string',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'judul' => 'sometimes|required|string',
                'konten' => 'sometimes|required|string',
                'thumbnail' => "$docRule|mimes:jpg,png,jpeg|max:2048",
                'status' => 'sometimes|required|string',
            ];
        }


        return [];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul wajib diisi.',
            'judul.string' => 'Judul harus berupa teks.',
            'konten.required' => 'Konten wajib diisi.',
            'konten.string' => 'Konten harus berupa teks.',
            'thumbnail.required' => 'Thumbnail wajib diisi.',
            'thumbnail.mimes' => 'Thumbnail harus berupa file gambar.',
            'thumbnail.max' => 'Thumbnail maksimal 2MB.',
            'status.required' => 'Status wajib diisi.',
            'status.string' => 'Status harus berupa teks.',
        ];
    }
}
