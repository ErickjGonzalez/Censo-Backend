<?php

namespace App\Http\Requests\CensusQuestionRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class UpdateCensusQuestionRequest extends FormRequest
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
                    $item['census_question_id'] = isset($item['census_question_id'])
                        ? Crypt::decryptString($item['census_question_id'])
                        : null;
                } catch (DecryptException $e) {
                    $item['census_question_id'] = null;
                }

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
            'items'                        => 'required|array',
            'items.*.census_question_id'   => 'nullable|integer|exists:census_questions,id',
            'items.*.index_id'             => 'required|integer|exists:indexs,id',
            'items.*.question_id'          => 'required|integer|exists:questions,id',
        ];
    }

    public function messages()
    {
        return [
            'items.required' => 'Los items son obligatorios.',
            'items.array'    => 'Error en el formato de la información.',

            'items.*.census_question_id.integer' => 'La información proporcionada de la pregunta del censo no es válida.',
            'items.*.census_question_id.exists'  => 'La pregunta del censo especificada no existe.',

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
            }
        });
    }
}
