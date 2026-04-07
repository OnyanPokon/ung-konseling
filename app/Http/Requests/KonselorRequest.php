<?php

namespace App\Http\Requests;

use App\Models\Konselors;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KonselorRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // kosong jadi null
        foreach ($this->all() as $key => $value) {
            if ($value === '') {
                $this->merge([$key => null]);
            }
        }

        // normalize boolean
        if ($this->has('is_active')) {
            $value = strtolower($this->input('is_active'));

            if (in_array($value, ['true', '1', 'yes', 'on'])) {
                $this->merge(['is_active' => true]);
            }

            if (in_array($value, ['false', '0', 'no', 'off'])) {
                $this->merge(['is_active' => false]);
            }
        }

        // normalize jenis_kelamin
        if ($this->has('jenis_kelamin')) {
            $this->merge([
                'jenis_kelamin' => strtoupper($this->input('jenis_kelamin'))
            ]);
        }
    }

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
            'nama' => 'sometimes|nullable|string',

            'email' => 'sometimes|nullable|email',

            'password' => 'sometimes|nullable|min:6',

            'nip' => 'sometimes|nullable',

            'phone' => 'sometimes|nullable',

            'jenis_kelamin' => 'sometimes|nullable|in:L,P',

            'foto_profil' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',

            'is_active' => 'sometimes|nullable|boolean',
        ];
    }
}
