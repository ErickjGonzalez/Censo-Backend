<?php

namespace App\Http\Requests\SectionRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    /* Name: Mayusculas y minúsculas 
            acentos y comas
            y diagonales / numeros 
        Instructions: Mayusculas y minúsculas 
            acentos y comas
            y numeros y. Estos caracteres especiales “” , / . : ; - _
    */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'name' => [
                'sometimes',
                'string',
                'min:10',
                'max:180',
                'regex:/^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ0-9\s\/,\.]+$/u',// Acepta mayúsculas, minúsculas, letras con acentos, ñ, números, espacios, comas, diagonales (/) y puntos (.)
            ],

            'instructions' => [
                'sometimes',
                'string',
                'min:10',
                'max:4200',
                'regex:/^[\pL\pM\pN\s\,\.\:\;\-\/\"“”_()º°]+$/u',// Acepta letras (mayús., minús., con acentos), números, espacios y signos básicos (, . : ; - / " ( ) º °)
                'unique:sections,instructions',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.min' => 'El nombre debe tener al menos 10 caracteres.',
            'name.max' => 'El nombre no puede exceder los 180 caracteres.',
            'name.regex' => 'El nombre no puede tener emojis ni otros símbolos.',

            'instructions.string' => 'La instrucción debe ser una cadena de texto.',
            'instructions.min' => 'La instrucción debe tener al menos 10 caracteres.',
            'instructions.max' => 'La instrucción no puede exceder los 4200 caracteres.',
            'instructions.regex' => 'La instrucción no puede tener emojis ni otros símbolos.',
            'instructions.unique' => 'La instrucción ya está en uso.',
        ];
    }
}
