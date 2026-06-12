<?php

namespace App\Http\Requests\LoginRequest;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'email'    => 'required|string|email|min:10|max:55|exists:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:30',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El campo correo electrónico es obligatorio.',
            'email.string'   => 'El campo correo electrónico debe ser una cadena de texto.',
            'email.email'    => 'El correo electrónico no tiene un formato válido.',
            'email.min'      => 'El campo correo electrónico debe tener al menos 10 caracteres.',
            'email.max'      => 'El campo correo electrónico no debe exceder los 55 caracteres.',
            'email.exists'   => 'El correo electrónico no está registrado.',

            'password.required' => 'El campo contraseña es obligatorio.',
            'password.string'   => 'El campo contraseña debe ser una cadena de texto.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max'      => 'La contraseña no debe exceder los 30 caracteres.',
        ];
    }
}
