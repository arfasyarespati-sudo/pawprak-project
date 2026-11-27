<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'avatar' => ['required', 'string', 'in:kucing,dino,raccoon,ghost,blob,robot'],
        ];
    }

    /**
     * Get custom validation messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama tampilan wajib diisi.',
            'name.min' => 'Nama tampilan minimal 2 karakter.',
            'name.max' => 'Nama tampilan maksimal 50 karakter.',
            'avatar.required' => 'Pilih salah satu avatar terlebih dahulu.',
            'avatar.in' => 'Pilihan avatar tidak valid.',
        ];
    }
}
