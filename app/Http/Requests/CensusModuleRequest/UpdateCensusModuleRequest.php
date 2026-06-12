<?php

namespace App\Http\Requests\CensusModuleRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Models\CensusModule;


class UpdateCensusModuleRequest extends FormRequest
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
                    $item['census_module_id'] = isset($item['census_module_id'])
                        ? Crypt::decryptString($item['census_module_id'])
                        : null;
                } catch (DecryptException $e) {
                    $item['census_module_id'] = null;
                }

                try {
                    $item['index_id'] = isset($item['index_id'])
                        ? Crypt::decryptString($item['index_id'])
                        : null;
                } catch (DecryptException $e) {
                    $item['index_id'] = null;
                }

                try {
                    $item['module_id'] = isset($item['module_id'])
                        ? Crypt::decryptString($item['module_id'])
                        : null;
                } catch (DecryptException $e) {
                    $item['module_id'] = null;
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
            'items'                    => 'required|array',
            'items.*.census_module_id' => 'nullable|integer|exists:census_modules,id',
            'items.*.index_id'         => 'required|integer|exists:indexs,id',
            'items.*.module_id'        => 'required|integer|exists:modules,id',
        ];
    }

    public function messages()
    {
        return [
            'items.required' => 'Los items son obligatorios.',
            'items.array'    => 'Error en el formato de la información.',

            'items.*.census_module_id.integer' => 'La información proporcionada del módulo del censo no es válida.',
            'items.*.census_module_id.exists'  => 'El módulo del censo especificado no existe.',

            'items.*.index_id.required' => 'El índice es obligatorio para cada módulo.',
            'items.*.index_id.integer'  => 'La información proporcionada del índice no es válida.',
            'items.*.index_id.exists'   => 'El índice especificado no existe.',

            'items.*.module_id.required' => 'El módulo es obligatorio para cada registro.',
            'items.*.module_id.integer'  => 'La información proporcionada del módulo no es válida.',
            'items.*.module_id.exists'   => 'El módulo especificado no existe.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('items') && is_array($this->items)) {
                $indexIds  = collect($this->items)->pluck('index_id');
                $moduleIds = collect($this->items)->pluck('module_id');

                if ($indexIds->count() !== $indexIds->unique()->count()) {
                    $duplicateIndexes = $indexIds->countBy()
                        ->filter(fn($count) => $count > 1)
                        ->map(fn($count, $id) => "índice $id aparece $count veces")
                        ->implode(', ');

                    $validator->errors()->add('items', 'Índices repetidos en la solicitud: ' . $duplicateIndexes);
                }

                if ($moduleIds->count() !== $moduleIds->unique()->count()) {
                    $duplicateModules = $moduleIds->countBy()
                        ->filter(fn($count) => $count > 1)
                        ->map(fn($count, $id) => "módulo $id aparece $count veces")
                        ->implode(', ');

                    $validator->errors()->add('items', 'Módulos repetidos en la solicitud: ' . $duplicateModules);
                }
            }
        });
    }
}
