<?php

namespace App\Http\Requests\PermissionRequest;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para crear un nuevo permiso.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:4',
                'max:45',
                'regex:/^[\pL\pM\pN]+\.[\pL\pM\pN]+$/u',
                'unique:permissions,name'
            ],
        ];
    }

    /**
     * Mensajes personalizados de validación.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del permiso es obligatorio.',
            'name.string'   => 'El nombre del permiso debe ser una cadena de texto.',
            'name.min'      => 'El nombre del permiso debe tener al menos 4 caracteres.',
            'name.max'      => 'El nombre del permiso no puede exceder los 45 caracteres.',
            'name.regex'    => 'El formato debe ser "texto.texto" (ejemplo: usuario.crear). No se permiten espacios, emojis ni símbolos especiales excepto el punto central.',
            'name.unique'   => 'El nombre del permiso ya está en uso.',
        ];
    }
}