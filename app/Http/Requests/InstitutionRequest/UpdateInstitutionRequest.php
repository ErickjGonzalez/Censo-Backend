<?php

namespace App\Http\Requests\InstitutionRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstitutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|min:5|max:90|regex:/^[\pL\pM\s]+$/u|unique:institutions,name',

            'geocode' => 'sometimes|digits_between:5,15',

            
            'municipality' => 'sometimes|string|min:5|max:40|regex:/^[\pL\pM\s,]+$/u',

            'typeinst' => 'sometimes|string|min:5|max:20|regex:/^[\pL\pM\s,]+$/u',

           
            'lat' => 'sometimes|nullable|numeric|between:-90,90',
            'lon' => 'sometimes|nullable|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.min' => 'El nombre debe tener al menos 5 caracteres.',
            'name.max' => 'El nombre no puede exceder los 90 caracteres.',
            'name.regex' => 'El campo nombre no puede contener emojis ni símbolos especiales.',
            'name.unique' => 'El nombre ya esta en uso',

            'geocode.digits_between' => 'El geocódigo debe tener entre 5 y 15 dígitos.',

            'municipality.string' => 'El municipio debe ser una cadena de texto.',
            'municipality.min' => 'El municipio debe tener al menos 5 caracteres.',
            'municipality.max' => 'El municipio no puede exceder los 40 caracteres.',
            'municipality.regex' => 'El campo municipio no puede contener emojis ni símbolos especiales.',

            'typeinst.string' => 'El tipo de institución debe ser una cadena de texto.',
            'typeinst.min' => 'El tipo de institución debe tener al menos 5 caracteres.',
            'typeinst.max' => 'El tipo de institución no puede exceder los 20 caracteres.',
            'typeinst.regex' => 'El campo tipo de institución no puede contener emojis ni símbolos especiales.',

           
            'lat.numeric' => 'La latitud debe ser un número.',
            'lat.between' => 'La latitud debe estar entre -90 y 90.',
            'lon.numeric' => 'La longitud debe ser un número.',
            'lon.between' => 'La longitud debe estar entre -180 y 180.',
        ];
    }
}
