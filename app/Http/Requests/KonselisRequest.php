<?php

namespace App\Http\Requests;

use App\Models\Konselis;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KonselisRequest extends FormRequest
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
        $id = $this->route('id');
        $konseli = $id ? Konselis::find($id) : null;

        if ($this->isMethod('post')) {
            return [
                'nama' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'nim' => 'required|string|unique:konselis,nim',
                'phone' => 'required|string',
                'domisili' => 'required|string|max:255',
                'jurusan' => 'required|string|max:255',
                'umur' => 'required|integer|min:15|max:100',
                'jenis_kelamin' => 'required|in:L,P',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'nama' => 'sometimes|required|string',

                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    Rule::unique('users', 'email')
                        ->ignore($konseli?->user_id),
                ],

                'password' => 'nullable|min:6',

                'nim' => [
                    'sometimes',
                    'required',
                    Rule::unique('konselis', 'nim')
                        ->ignore($konseli?->id),
                ],

                'phone' => 'sometimes|required|string',
                'domisili' => 'sometimes|required|string|max:255',
                'jurusan' => 'sometimes|required|string|max:255',
                'umur' => 'sometimes|required|integer|min:15|max:100',
                'jenis_kelamin' => 'sometimes|required|in:L,P',
            ];
        }


        return [];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.string' => 'NIM harus berupa teks.',
            'domisili.required' => 'Domisili wajib diisi.',
            'jurusan.required' => 'Jurusan wajib diisi.',
            'umur.required' => 'Umur wajib diisi.',
            'umur.integer' => 'Umur harus berupa angka.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',
        ];
    }
}
