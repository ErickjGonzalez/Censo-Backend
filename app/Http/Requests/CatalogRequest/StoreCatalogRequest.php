<?php

namespace App\Http\Requests\CatalogRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class StoreCatalogRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Preparar datos antes de validar (desencriptar module_id)
     */
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
                'message' => 'Ocurrió un error al procesar la solicitud.'.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reglas de validación para crear un nuevo catálogo.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:125',
                'unique:catalogs,name',
            ],

            'slug' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'unique:catalogs,slug',
            ],

            'module_id' => [
                'required',
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
            'name.required' => 'El nombre del catálogo es obligatorio.',
            'name.string'   => 'El nombre del catálogo debe ser texto.',
            'name.min'      => 'El nombre del catálogo debe tener al menos 3 caracteres.',
            'name.max'      => 'El nombre del catálogo no puede exceder los 125 caracteres.',
            'name.unique'   => 'El nombre del catálogo ya está registrado.',

            'slug.required' => 'El slug del catálogo es obligatorio.',
            'slug.string'   => 'El slug debe ser texto.',
            'slug.min'      => 'El slug debe tener al menos 3 caracteres.',
            'slug.max'      => 'El slug no puede exceder los 50 caracteres.',
            'slug.unique'   => 'El slug ya está registrado.',

            'module_id.required' => 'Debe seleccionar una modulo.',
            
            'module_id.exists'   => 'La unidad seleccionada no existe.',
        ];
    }
}
