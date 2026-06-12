<?php

namespace App\Http\Requests\CatalogItemRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Validation\Rule;

class UpdateCatalogItemRequest extends FormRequest
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
            if ($this->has('catalog_id') && $this->catalog_id) {
                if (Crypt::decryptString($this->catalog_id)) {
                    $this->merge([
                        'catalog_id' => Crypt::decryptString($this->catalog_id),
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
     * Reglas de validación para actualizar un item de catálogo existente.
     */
    public function rules(): array
    {
        $id = $this->route('catalog_item');
        $decryptedId = null;

        try {
            $decryptedId = Crypt::decryptString($id);
        } catch (\Throwable $e) {
           
        }

        return [
            'value' => [
                'sometimes',
                'string',
                'min:1',
                'max:10',
            ],

            'label' => [
                'sometimes',
                'string',
                'min:3',
                'max:165',
            ],

            'catalog_id' => [
                'sometimes',
                'integer',
                'exists:catalogs,id',
            ],
        ];
    }

    /**
     * Mensajes personalizados de validación.
     */
    public function messages(): array
    {
        return [
            'value.string' => 'El valor debe ser una cadena de texto.',
            'value.min'    => 'El valor debe tener al menos 1 caracter.',
            'value.max'    => 'El valor no puede exceder los 10 caracteres.',

            'label.string' => 'La etiqueta debe ser una cadena de texto.',
            'label.min'    => 'La etiqueta debe tener al menos 3 caracteres.',
            'label.max'    => 'La etiqueta no puede exceder los 165 caracteres.',

            'catalog_id.integer' => 'Ocurrió un error al procesar la solicitud',
            'catalog_id.exists'  => 'El catálogo seleccionado no existe.',
        ];
    }
}

