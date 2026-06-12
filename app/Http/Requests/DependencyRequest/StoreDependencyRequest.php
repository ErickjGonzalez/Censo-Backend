<?php

namespace App\Http\Requests\DependencyRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDependencyRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para crear una nueva área.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:5',
                'max:45',
                'regex:/^[\pL\pM\pN\s\'\-\.\,]+$/u',
                'unique:dependencies,name',
            ],

        ];
    }

    /**
     * Mensajes personalizados de validación.
     */
    public function messages(): array
    {
        return [
            'name.required'   => 'El nombre del área es obligatorio.',
            'name.string'     => 'El nombre del área debe ser una cadena de texto.',
            'name.min'        => 'El nombre del área debe tener al menos 5 caracteres.',
            'name.max'        => 'El nombre del área no puede exceder los 45 caracteres.',
            'name.regex'      => 'El nombre del área no puede contener emojis ni símbolos especiales.',
            'name.unique'     => 'El nombre del área ya está registrado.',
        ];
    }
}
