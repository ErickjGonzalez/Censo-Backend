<?php

namespace App\Http\Requests\CensusRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCensusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'name' => 'sometimes|string|min:5|max:65|regex:/^[\pL\pM\pN\s\'\-\.\,\/\(\)]+$/u|unique:censuses,name',// Acepta mayúsculas, minúsculas, letras con acentos, números, espacios y signos básicos (',.-/())
            'description' => 'sometimes|string|min:10|max:200|regex:/^[\pL\pM\pN\s\'\-\.\,\/\(\)]+$/u',// Acepta mayúsculas, minúsculas, letras con acentos, números, espacios y signos básicos (',.-/())
            'init_date' => 'sometimes|date|after_or_equal:today',
            'deadline' => 'sometimes|date|after:init_date',
        ];
    }
    public function messages(): array
    {
        return [
            'name.string'       => 'El nombre debe ser una cadena de texto.',
            'name.min'          => 'El nombre debe tener al menos 5 caracteres.',
            'name.max'          => 'El nombre no puede exceder los 65 caracteres.',
            'name.regex'        => 'El campo nombre no puede contener emojis ni símbolos especiales.',
            'name.unique'       => 'El nombre ya esta en uso',

            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.min'    => 'La descripción debe tener al menos 10 caracteres.',
            'description.max'    => 'La descripción no puede exceder los 200 caracteres.',
            'description.regex'  => 'El campo descripción no puede contener emojis ni símbolos especiales.',

            'init_date.date'     => 'La fecha de inicio debe ser una fecha válida.',
            'init_date.after_or_equal' => 'La fecha de inicio no puede ser anterior a hoy.',

            'deadline.date'      => 'La fecha límite debe ser una fecha válida.',
            'deadline.after'     => 'La fecha límite debe ser posterior a la fecha de inicio.',
        ];    
    }

}
