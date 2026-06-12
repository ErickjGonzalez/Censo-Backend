<?php

namespace App\Http\Requests\PermissionRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para actualizar un permiso existente.
     */
    public function rules(): array
    {
        // Desencriptamos el ID que viene de la ruta para ignorarlo en la validación unique
        try {
            $decryptedId = Crypt::decryptString($this->route('id'));
        } catch (\Throwable $e) {
            $decryptedId = null;
        }

        return [
            'name' => [
                'sometimes',
                'string',
                'min:4',
                'max:45',
                'regex:/^[\pL\pM\pN]+\.[\pL\pM\pN]+$/u',
              
                Rule::unique('permissions', 'name')->ignore($decryptedId),
            ],
        ];
    }

    /**
     * Mensajes personalizados de validación.
     */
    public function messages(): array
    {
        return [
            'name.string' => 'El nombre del permiso debe ser una cadena de texto.',
            'name.min'    => 'El nombre del permiso debe tener al menos 4 caracteres.',
            'name.max'    => 'El nombre del permiso no puede exceder los 45 caracteres.',
            'name.regex'  => 'El formato debe ser "texto.texto" (ejemplo: usuario.editar). No se permiten espacios ni símbolos especiales excepto el punto central.',
            'name.unique' => 'El nombre del permiso ya está en uso.',
        ];
    }
}