<?php

namespace App\Http\Requests\CensusSectionRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Models\CensusSection;

class UpdateCensusSectionRequest extends FormRequest
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
        if ($this->has('items') && is_array($this->items)) {
            $decryptedItems = collect($this->items)->map(function ($item) {

                try {
                    $item['census_section_id'] = isset($item['census_section_id'])
                        ? Crypt::decryptString($item['census_section_id'])
                        : null;
                } catch (DecryptException $e) {
                    $item['census_section_id'] = null;
                }

                try {
                    $item['index_id'] = isset($item['index_id'])
                        ? Crypt::decryptString($item['index_id'])
                        : null;
                } catch (DecryptException $e) {
                    $item['index_id'] = null;
                }

                try {
                    $item['section_id'] = isset($item['section_id'])
                        ? Crypt::decryptString($item['section_id'])
                        : null;
                } catch (DecryptException $e) {
                    $item['section_id'] = null;
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
            'items'                       => 'required|array',
            'items.*.census_section_id'   => 'nullable|integer|exists:census_sections,id',
            'items.*.index_id'            => 'required|integer|exists:indexs,id',
            'items.*.section_id'          => 'required|integer|exists:sections,id',
        ];
    }

    public function messages()
    {
        return [
            'items.required' => 'Los items son obligatorios.',
            'items.array'    => 'Error en el formato de la información.',

            'items.*.census_section_id.integer' => 'La información proporcionada de la sección del censo no es válida.',
            'items.*.census_section_id.exists'  => 'La sección del censo especificada no existe.',

            'items.*.index_id.required' => 'El índice es obligatorio para cada sección.',
            'items.*.index_id.integer'  => 'La información proporcionada del índice no es válida.',
            'items.*.index_id.exists'   => 'El índice especificado no existe.',

            'items.*.section_id.required' => 'La sección es obligatoria para cada registro.',
            'items.*.section_id.integer'  => 'La información proporcionada de la sección no es válida.',
            'items.*.section_id.exists'   => 'La sección especificada no existe.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('items') && is_array($this->items)) {
                $indexIds   = collect($this->items)->pluck('index_id');
                $sectionIds = collect($this->items)->pluck('section_id');

                if ($indexIds->count() !== $indexIds->unique()->count()) {
                    $duplicateIndexes = $indexIds->countBy()
                        ->filter(fn($count) => $count > 1)
                        ->map(fn($count, $id) => "índice $id aparece $count veces")
                        ->implode(', ');

                    $validator->errors()->add('items', 'Índices repetidos en la solicitud: ' . $duplicateIndexes);
                }

                if ($sectionIds->count() !== $sectionIds->unique()->count()) {
                    $duplicateSections = $sectionIds->countBy()
                        ->filter(fn($count) => $count > 1)
                        ->map(fn($count, $id) => "sección $id aparece $count veces")
                        ->implode(', ');

                    $validator->errors()->add('items', 'Secciones repetidas en la solicitud: ' . $duplicateSections);
                }
            }
        });
    }
}
