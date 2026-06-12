<?php

namespace App\Http\Requests\QuestionRequest;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
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
        // Desencriptar category_id
        if ($this->has('category_id') && $this->category_id) {
            try {
                $decryptedCategoryId = Crypt::decryptString($this->category_id);
                $this->merge([
                    'category_id' => $decryptedCategoryId
                ]);
            } catch (DecryptException $e) {
                $this->merge([
                    'category_id' => null
                ]);
            }
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
            'name' => [
                'sometimes',
                'string',
                'min:5',
                'max:920',
                'regex:/^[\pL\pM\pN\s\r\n\t\\/\,.:;–—()\{\}\[\]¿?¡!#%´&|"“”\'\-]+$/u'
            ],
            'instructions' => [
                'sometimes',
                'string',
                'min:10',
                'max:8000',
                'regex:/^[\pL\pM\pN\s\r\n\t\,\.\:\;\-–—\/\"“”‘’\_()º°´&%\[\]\|¿?¡!]+$/u'
            ],
            'commentaries' => 'sometimes|boolean',
            'question_structure' => 'sometimes|string',
            'category_id' => 'sometimes|integer|exists:categories,id',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        // El método 'after' se ejecuta después de que pasan las reglas de arriba
        $validator->after(function ($validator) {
            
            // Obtenemos los datos limpios que el usuario está enviando
            $nombre = $this->input('name');
            $instrucciones = $this->input('instructions');

            // Tu lógica exacta de base de datos
            if (empty($instrucciones)) {
                $existingQuestion = Question::whereRaw('TRIM(name) = ?', [$nombre])
                    ->where(function ($query) {
                        $query->whereNull('instructions')
                            ->orWhere('instructions', '');
                    })
                    ->first();
            } else {
                $existingQuestion = Question::whereRaw('TRIM(name) = ?', [$nombre])
                    ->whereRaw('TRIM(COALESCE(instructions, \'\')) = ?', [$instrucciones])
                    ->first();
            }

            // Si existe, inyectamos el error manualmente al validador
            if ($existingQuestion) {
                // Puedes atar el error al campo 'name' o crear uno general
                $validator->errors()->add(
                    'name', 
                    'Esta combinación de título e instrucciones ya existe en la base de datos.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.string' => 'La pregunta debe ser una cadena de texto.',
            'name.min' => 'La pregunta debe tener al menos :min caracteres.',
            'name.max' => 'La pregunta no debe exceder de :max caracteres.',
            'name.regex' => 'La pregunta contiene caracteres no permitidos.',

            'instructions.string' => 'Las instrucciones deben ser una cadena de texto.',
            'instructions.min' => 'Las instrucciones deben tener al menos :min caracteres.',
            'instructions.max' => 'Las instrucciones no deben exceder de :max caracteres.',
            'instructions.regex' => 'Las instrucciones contienen caracteres no permitidos.',

            'commentaries.boolean' => 'El campo comentarios debe ser verdadero o falso.',

            'question_structure.string' => 'La estructura de la pregunta debe ser una cadena de texto válida.',

            'category_id.integer' => 'El campo categoría proporcionado no es válido.',
            'category_id.exists' => 'La categoría seleccionada no existe.',

        ];
    }

}
