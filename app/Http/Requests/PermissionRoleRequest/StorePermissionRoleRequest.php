<?php

namespace App\Http\Requests\PermissionRoleRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException as ValidatorException;

class StorePermissionRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->filled('role_id')) {
            try {
                $this->merge([
                    'role_id' => Crypt::decryptString($this->role_id),
                ]);
            } catch (\Exception $e) {
                throw ValidatorException::withMessages([
                    'role_id' => 'La solicitud ha fallado.',
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role_id' => 'required|integer|exists:roles,id',
            'permissions_ids' => 'required|array',
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
            'role_id.required' => 'El rol es obligatorio.',
            'role_id.integer' => 'El rol no es válido.',
            'role_id.exists' => 'El rol especificado no existe.',
            'permissions_ids.required' => 'Los IDs de los permisos son obligatorios.',
            'permissions_ids.array' => 'Los IDs de los permisos deben ser un arreglo.',
            'permissions_ids.*.integer' => 'Cada ID de permiso debe ser un número entero.',
            'permissions_ids.*.exists' => 'Uno o más permisos especificados no existen.',
        ];
    }
}
