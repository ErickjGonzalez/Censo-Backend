<?php

namespace App\Http\Requests\AssignmentRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class StoreAssignmentRequest extends FormRequest
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
        if ($this->has('census_section_id') && $this->census_section_id) {
            try {
                $this->merge(['census_section_id' => Crypt::decryptString($this->census_section_id)]);
            } catch (DecryptException $e) {
                $this->merge(['census_section_id' => null]);
            }
        }

        if ($this->has('items') && is_array($this->items)) {
            $decryptedItems = collect($this->items)->map(function ($item) {
                try {
                    $item['institution_id'] = isset($item['institution_id'])
                        ? Crypt::decryptString($item['institution_id'])
                        : null;
                } catch (DecryptException $e) {
                    $item['institution_id'] = null;
                }
                return $item;
            })->toArray();

            $this->merge(['items' => $decryptedItems]);
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
            'census_section_id'            => 'required|integer|exists:census_sections,id',
            'items'                        => 'required|array',
            'items.*.institution_id'       => 'required|integer|exists:institutions,id',
        ];
    }

    public function messages()
    {
        return [
            'census_section_id.required' => 'La información de la sección del censo es obligatoria.',
            'census_section_id.integer'  => 'La información proporcionada de la sección no es válida.',
            'census_section_id.exists'   => 'La sección del censo especificada no existe.',

            'items.required'                   => 'Los items son obligatorios.',
            'items.array'                      => 'Error en el formato de la información.',
            'items.*.institution_id.required'  => 'La institución es obligatoria para cada registro.',
            'items.*.institution_id.integer'   => 'La información proporcionada de la institución no es válida.',
            'items.*.institution_id.exists'    => 'La institución especificada no existe.',
        ];
    }
}
