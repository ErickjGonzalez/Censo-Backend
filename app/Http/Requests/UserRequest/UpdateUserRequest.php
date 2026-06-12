<?php

namespace App\Http\Requests\UserRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class UpdateUserRequest extends FormRequest
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
            'name' => 'sometimes|string|min:3|max:45|regex:/^[\pL\pM\s\'\-\.\,]+$/u',
            'lastname' => 'sometimes|string|min:4|max:45|regex:/^[\pL\pM\s\'\-\.\,]+$/u',
            'phone' => [
                'sometimes', 
                'string', 
                'regex:/^\+?[0-9]{10,15}$/', 
                'unique:users,phone'
            ],
            'mobile' => [
                'sometimes', 
                'string', 
                'regex:/^\+?[0-9]{10,15}$/', 
                'unique:users,mobile'
            ],
            'address' => 'sometimes|string|min:5|max:150|regex:/^[\pL\pM\pN\s\'\-\.\,\#]+$/u|regex:/\pL/u',
            'email' => 'sometimes|string|email|max:45|unique:users,email',
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

    public function messages()
    {
        return [
            'name.string' => 'El nombre debe ser de tipo texto.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre no puede superar los 45 caracteres.',
            'name.regex' => 'El nombre contiene caracteres no permitidos.',

            'lastname.string' => 'El apellido debe ser de tipo texto.',
            'lastname.min' => 'El apellido debe tener al menos 4 caracteres.',
            'lastname.max' => 'El apellido no puede superar los 45 caracteres.',
            'lastname.regex' => 'El apellido contiene caracteres no permitidos.',

            'phone.string' => 'El teléfono debe ser de tipo texto.',
            'phone.regex' => 'El formato del teléfono no es válido. Debe contener solo números y puede incluir un signo "+" al inicio. La longitud debe ser entre 10 y 15 caracteres.',
            'phone.unique' => 'El teléfono ya se encuentra registrado.',

            'mobile.string' => 'El número móvil debe ser de tipo texto.',
            'mobile.regex' => 'El formato del número móvil no es válido. Debe contener solo números y puede incluir un signo "+" al inicio. La longitud debe ser entre 10 y 15 caracteres.',
            'mobile.unique' => 'El número móvil ya se encuentra registrado.',

            'address.string' => 'La dirección debe ser de tipo texto.',
            'address.min' => 'La dirección debe tener al menos 5 caracteres.',
            'address.max' => 'La dirección no puede superar los 150 caracteres.',
            'address.regex' => 'La dirección contiene caracteres no permitidos.',

            'email.string' => 'El correo electrónico debe ser válido.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.max' => 'El correo electrónico no puede superar los 45 caracteres.',
            'email.unique' => 'El correo electrónico ya se encuentra registrado.',

            'password.string' => 'La contraseña debe ser de tipo texto.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La contraseña no puede superar los 30 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex' => 'La contraseña debe incluir al menos una mayúscula, una minúscula, un número y un símbolo especial.',

            /* mensajes de las llaves */
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
