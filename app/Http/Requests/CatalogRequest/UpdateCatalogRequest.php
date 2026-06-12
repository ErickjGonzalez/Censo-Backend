<?php

namespace App\Http\Requests\CatalogRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Validation\Rule;

class UpdateCatalogRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        try {
            if ($this->has('module_id') && $this->module_id) {
                if (Crypt::decryptString($this->module_id)) {
                    $this->merge([
                        'module_id' => Crypt::decryptString($this->module_id),
                    ]);
                }
            }
        } catch (DecryptException $e) {
            return response()->json([
                'message' => 'Ocurrió un error al procesar la solicitud.',
            ], 500);
        }
    }

    /**
     * Reglas de validación para actualizar un catálogo existente.
     */
    public function rules(): array
    {
        $id = $this->route('catalog');
        $decryptedId = null;

        try {
            $decryptedId = Crypt::decryptString($id);
        } catch (\Throwable $e) {
            // si falla, se queda null y no truena
        }

        return [
            'name' => [
                'sometimes',
                'string',
                'min:3',
                'max:125',
                Rule::unique('catalogs', 'name')->ignore($decryptedId),
            ],

            'slug' => [
                'sometimes',
                'string',
                'min:3',
                'max:50',
                Rule::unique('catalogs', 'slug')->ignore($decryptedId),
            ],

            'module_id' => [
                'sometimes',
                'integer',
                'exists:modules,id',
            ],
        ];
    }

    /**
     * Mensajes personalizados de validación.
     */
    public function messages(): array
    {
        return [
            'name.string' => 'El nombre del catálogo debe ser una cadena de texto.',
            'name.min'    => 'El nombre del catálogo debe tener al menos 3 caracteres.',
            'name.max'    => 'El nombre del catálogo no puede exceder los 125 caracteres.',
            'name.unique' => 'El nombre del catálogo ya está en uso.',

            'slug.string' => 'El slug debe ser una cadena de texto.',
            'slug.min'    => 'El slug debe tener al menos 3 caracteres.',
            'slug.max'    => 'El slug no puede exceder los 50 caracteres.',
            'slug.unique' => 'El slug ya está en uso.',

            'module_id.integer' => 'Ocurrió un error al procesar la solicitud',
            'module_id.exists'  => 'La unidad seleccionada no existe.',
        ];
    }
}


