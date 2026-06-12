<?php

namespace App\Http\Requests\RoleRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
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
        return [// Acepta letras mayúsculas, minúsculas (A-Z, a-z) y espacios — sin soporte Unicode
            'name' => 'sometimes|string|min:5|max:25|regex:/^[A-Za-z\s]+$/|unique:roles,name',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string'       => 'El nombre debe ser una cadena de texto.',
            'name.min'          => 'El nombre debe tener al menos 5 caracteres.',
            'name.max'          => 'El nombre no puede exceder los 25 caracteres.',
            'name.regex'        => 'El campo nombre no puede contener emojis ni símbolos especiales.',
            'name.unique'       => 'El nombre ya esta en uso',
        ];    
    }
}
