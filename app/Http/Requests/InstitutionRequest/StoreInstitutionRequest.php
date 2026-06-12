<?php

namespace App\Http\Requests\InstitutionRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstitutionRequest extends FormRequest
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
            'name' => 'required|string|min:5|max:90|regex:/^[\pL\pM\s]+$/u|unique:institutions,name',//acepta mayusculas y minusculas, espacios y letras con acentos
            'geocode' => 'required|digits_between:5,15',
            'municipality' => 'required|string|min:5|max:40|regex:/^[\pL\pM\s,]+$/u',//acepta mayusculas y minusculas, espacios y letras con acentos,comas 
            'typeinst' => 'required|string|min:5|max:20|regex:/^[\pL\pM\s,]+$/u',//acepta mayusculas y minusculas, espacios y letras con acentos,comas 
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'El nombre es obligatorio.',
            'name.string'       => 'El nombre debe ser una cadena de texto.',
            'name.min'          => 'El nombre debe tener al menos 5 caracteres.',
            'name.max'          => 'El nombre no puede exceder los 90 caracteres.',
            'name.regex'        => 'El campo nombre no puede contener emojis ni símbolos especiales.',
            'name.unique'       => 'El nombre ya esta en uso',

            'geocode.required'  => 'El geocódigo es obligatorio.',
            'geocode.numeric'   => 'El geocódigo debe ser un valor numérico.',
            'geocode.digits_between' => 'El geocódigo debe tener entre 5 y 15 dígitos.',

            'municipality.required'     => 'El municipio es obligatorio.',
            'municipality.string'       => 'El municipio debe ser una cadena de texto.',
            'municipality.min'          => 'El municipio debe tener al menos 5 caracteres.',
            'municipality.max'          => 'El municipio no puede exceder los 40 caracteres.',
            'municipality.regex'        => 'El campo municipio no puede contener emojis ni símbolos especiales.',
            
            'typeinst.required'     => 'El tipo de institución es obligatorio.',
            'typeinst.string'       => 'El tipo de institución debe ser una cadena de texto.',
            'typeinst.min'          => 'El tipo de institución debe tener al menos 5 caracteres.',
            'typeinst.max'          => 'El tipo de institución no puede exceder los 20 caracteres.',
            'typeinst.regex'        => 'El campo tipo de institución no puede contener emojis ni símbolos especiales.',
        ];    
    }
}
