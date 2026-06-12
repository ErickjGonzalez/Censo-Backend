<?php

namespace App\Http\Requests\IndexRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIndexRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para actualizar un índice existente.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       return [
          // Validación: solo índices tipo 1.1.2 (enteros positivos, sin 0 aislado, ni empezar o terminar con punto)
         'name' => 'required|string|min:1|max:45|regex:/^(?:[1-9]\d*)(?:\.(?:[1-9]\d*))*$/|       unique:indexs,name',
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
            'name.string' => 'El nombre del índice debe ser una cadena de texto.',
            'name.min'    => 'El nombre del índice debe tener al menos 1 caracteres.',
            'name.max'    => 'El nombre del índice no puede exceder los 45 caracteres.',
            'name.regex'    => 'Formato inválido (ej. 1.1.2)',
            'name.unique' => 'El nombre del índice ya está en uso.',
        ];
    }
}
