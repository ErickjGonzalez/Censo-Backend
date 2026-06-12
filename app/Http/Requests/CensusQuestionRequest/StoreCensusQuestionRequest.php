<?php

namespace App\Http\Requests\CensusQuestionRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Models\CensusQuestion;

class StoreCensusQuestionRequest extends FormRequest
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
                    $item['index_id'] = isset($item['index_id'])
                        ? Crypt::decryptString($item['index_id'])
                        : null;
                } catch (DecryptException $e) {
                    $item['index_id'] = null;
                }

                try {
                    $item['question_id'] = isset($item['question_id'])
                        ? Crypt::decryptString($item['question_id'])
                        : null;
                } catch (DecryptException $e) {
                    $item['question_id'] = null;
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
            'census_section_id'      => 'required|integer|exists:census_sections,id',
            'items'                  => 'required|array',
            'items.*.index_id'       => 'required|integer|exists:indexs,id',
            'items.*.question_id'    => 'required|integer|exists:questions,id',
        ];
    }

    public function messages()
    {
        return [
            'census_section_id.required' => 'La información de la sección del censo es obligatoria.',
            'census_section_id.integer'  => 'La información proporcionada de la sección del censo no es válida.',
            'census_section_id.exists'   => 'La sección del censo especificada no existe.',

            'items.required' => 'Los items son obligatorios.',
            'items.array'    => 'Error en el formato de la información.',

            'items.*.index_id.required' => 'El índice es obligatorio para cada pregunta.',
            'items.*.index_id.integer'  => 'La información proporcionada del índice no es válida.',
            'items.*.index_id.exists'   => 'El índice especificado no existe.',

            'items.*.question_id.required' => 'La pregunta es obligatoria para cada registro.',
            'items.*.question_id.integer'  => 'La información proporcionada de la pregunta no es válida.',
            'items.*.question_id.exists'   => 'La pregunta especificada no existe.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('items') && is_array($this->items)) {
                $indexIds    = collect($this->items)->pluck('index_id');
                $questionIds = collect($this->items)->pluck('question_id');

                if ($indexIds->count() !== $indexIds->unique()->count()) {
                    $duplicateIndexes = $indexIds->countBy()
                        ->filter(fn($count) => $count > 1)
                        ->map(fn($count, $id) => "índice $id aparece $count veces")
                        ->implode(', ');

                    $validator->errors()->add('items', 'Índices repetidos en la solicitud: ' . $duplicateIndexes);
                }

                if ($questionIds->count() !== $questionIds->unique()->count()) {
                    $duplicateQuestions = $questionIds->countBy()
                        ->filter(fn($count) => $count > 1)
                        ->map(fn($count, $id) => "pregunta $id aparece $count veces")
                        ->implode(', ');

                    $validator->errors()->add('items', 'Preguntas repetidas en la solicitud: ' . $duplicateQuestions);
                }

                if ($this->has('census_section_id') && $this->census_section_id) {
                    $existingQuestionIds = CensusQuestion::where('census_section_id', $this->census_section_id)
                        ->pluck('question_id');

                    $duplicates = $questionIds->intersect($existingQuestionIds);

                    if ($duplicates->isNotEmpty()) {
                        $validator->errors()->add('items', 'Las siguientes preguntas ya están asignadas a esta sección: ' . $duplicates->implode(', '));
                    }
                }
            }
        });
    }
}
