<?php

namespace App\Http\Requests\PermissionRoleRequest;

use Illuminate\Foundation\Http\FormRequest;

class PermissionRole extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }
    public function prepare_for_validation(): void
    {
        /* aqui se tiene que descencriptar el role_id y los permission_ids */
        /* aqui falta un try catch */
        $this->merge([
            'role_id' => decrypt($this->role_id),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role_id' => 'required|exists:roles,id',
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ];
    }
}
