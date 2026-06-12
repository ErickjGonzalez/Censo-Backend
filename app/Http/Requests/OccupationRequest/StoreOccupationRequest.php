<?php

namespace App\Http\Requests\OccupationRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class StoreOccupationRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para crear una nueva ocupación.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:5',
                'max:45',
                'regex:/^[\pL\pM\s\'\-\.\,]+$/u',
                'unique:occupations,name',
            ],
        ];
    }

    /**
     * Mensajes personalizados de validación.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'        => 'El nombre de la ocupación es obligatorio.',
            'name.string'          => 'El nombre de la ocupación debe ser una cadena de texto.',
            'name.min'             => 'El nombre de la ocupación debe tener al menos 5 caracteres.',
            'name.max'             => 'El nombre de la ocupación no puede exceder los 45 caracteres.',
            'name.regex'           => 'El campo nombre no puede contener emojis numeros ni símbolos especiales.',
            'name.unique'          => 'El nombre de la ocupación ya está en uso.',
        ];
    }
}