<?php

namespace App\Http\Requests\PermissionRoleRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRoleRequest extends FormRequest
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
            'permissions_ids' => 'nullable|array',
            'permissions_ids.*' => 'integer|exists:permissions,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'permissions_ids.array' => 'Los IDs de los permisos deben ser un arreglo.',
            'permissions_ids.*.integer' => 'Cada ID de permiso debe ser un número entero.',
            'permissions_ids.*.exists' => 'Uno o más permisos especificados no existen.',
        ];

    }
}
