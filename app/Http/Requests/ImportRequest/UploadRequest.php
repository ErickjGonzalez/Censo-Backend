<?php

namespace App\Http\Requests\ImportRequest;

use Illuminate\Foundation\Http\FormRequest;

class UploadRequest extends FormRequest
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
             'file' => 'required|mimes:xlsx,xls|max:10240'
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'El archivo es obligatorio.',
            'file.mimes' => 'El archivo debe ser un archivo de tipo: xlsx, xls.',
            'file.max' => 'El tamaño máximo del archivo es de 10MB.',
        ];
    }
}
