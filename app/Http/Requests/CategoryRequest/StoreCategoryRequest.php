<?php

namespace App\Http\Requests\CategoryRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
            'name' => [
                    'required',
                    'string',
                    'min:5',
                    'max:65',
                    'regex:/^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ\s\'\-\.,\/]+$/u',/* acepta letras, mayúsculas, minúsculas,comas,puntos,diagonales*/
                    'unique:categories,name',
                ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'El nombre es obligatorio.',
            'name.string'       => 'El nombre debe ser una cadena de texto.',
            'name.min'          => 'El nombre debe tener al menos 5 caracteres.',
            'name.max'          => 'El nombre no puede exceder los 65 caracteres.',
            'name.regex'        => 'No se permiten números ni emojis ni caracteres especiales.',
            'name.unique'       => 'El nombre ya esta en uso',
        ];    
    }
}
