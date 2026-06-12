<?php

namespace App\Http\Requests\UserRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class UpdateUserAdminRequest extends FormRequest
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
        // Desencriptar occupation_id
        if ($this->has('occupation_id') && $this->occupation_id) {
            try {
                $decryptedOccupationId = Crypt::decryptString($this->occupation_id);
                $this->merge([
                    'occupation_id' => $decryptedOccupationId
                ]);
            } catch (DecryptException $e) {
                $this->merge([
                    'occupation_id' => null
                ]);
            }
        }

        // Desencriptar dependency_id
        if ($this->has('dependency_id') && $this->dependency_id) {
            try {
                $decryptedAreaId = Crypt::decryptString($this->dependency_id);
                $this->merge([
                    'dependency_id' => $decryptedAreaId
                ]);
            } catch (DecryptException $e) { 
                $this->merge([
                    'dependency_id' => null
                ]);
            }
        }

        if ($this->has('institution_id') && $this->institution_id) {
            try {
                $decryptedInstitutionId = Crypt::decryptString($this->institution_id);
                $this->merge([
                    'institution_id' => $decryptedInstitutionId
                ]);
            } catch (DecryptException $e) {
                $this->merge([
                    'institution_id' => null
                ]);
            }
        }

        // Desencriptar role_id
        if ($this->has('role_id') && $this->role_id) {
            try {
                $decryptedRoleId = Crypt::decryptString($this->role_id);
                $this->merge([
                    'role_id' => $decryptedRoleId
                ]);
            } catch (DecryptException $e) {
                $this->merge([
                    'role_id' => null
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
            'email' => 'sometimes|string|email|min:10|max:55|unique:users,email',
            'password' => [
            'sometimes',
            'string',
            'min:8',
            'max:30',
            'confirmed',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+=\[{\]};:<>|\/.,?-])[A-Za-z\d!@#$%^&*()_+=\[{\]};:<>|\/.,?-]{8,30}$/',
            ],
            'occupation_id' => 'sometimes|integer|exists:occupations,id',
            'dependency_id' => 'sometimes|integer|exists:dependencies,id',
            'institution_id' => 'sometimes|integer|exists:institutions,id',
            'role_id' => 'sometimes|integer|exists:roles,id',
        ];
    }

    /* revisar mensajes */
    public function messages()
    {
        return [
            'email.string' => 'El campo correo electrónico debe ser una cadena de texto.',
            'email.email' => 'El campo correo electrónico debe ser una dirección de correo electrónico válida.',
            'email.min' => 'El campo correo electrónico debe tener al menos 8 caracteres.',
            'email.max' => 'El campo correo electrónico no debe exceder los 30 caracteres.',
            'email.unique' => 'El correo electrónico ya está en uso.',

            'password.string' => 'El campo contraseña debe ser una cadena de texto.',
            'password.min' => 'El campo contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'El campo contraseña no debe exceder los 30 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',/* revisar un mensaje más claro */

            'occupation_id.integer' => 'El campo ocupación proporcionado no es válido.',
            'occupation_id.exists' => 'La ocupación seleccionada no existe.',
            
            'dependency_id.integer' => 'El campo área proporcionado no es válido.',
            'dependency_id.exists' => 'El área seleccionada no existe.',

            'institution_id.integer' => 'El campo institución proporcionado no es válido.',
            'institution_id.exists' => 'La institución seleccionada no existe.',

            'role_id.integer' => 'El campo rol proporcionado no es válido.',
            'role_id.exists' => 'El rol seleccionado no existe.',
        ];
    }
}
