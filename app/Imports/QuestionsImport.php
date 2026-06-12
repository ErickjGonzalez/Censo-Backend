<?php

namespace App\Imports;

use App\Models\Question;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Validators\Failure;

class QuestionsImport implements ToCollection, WithValidation, WithHeadingRow, SkipsOnFailure, SkipsEmptyRows, WithEvents
{
    use SkipsFailures;

    public function rules(): array
    {
        return [
            
            'titulo_de_la_pregunta' => 
            [
                'required',
                'string',
                'min:5',
                'max:920',
                'regex:/^[\pL\pM\pN\s\r\n\t\\/\,.:;–—()\{\}\[\]¿?¡!#%´&|"“”\'\-]+$/u'
            ],

            'instrucciones_de_la_pregunta' => 
            [
                'nullable',
                'string',
                'min:10',
                'max:8000',
                'regex:/^[\pL\pM\pN\s\r\n\t\,\.\:\;\-–—\/\"“”‘’\_()º°´&%\[\]\|¿?¡!]+$/u'
            ],

            'comentarios_de_la_pregunta' => [
                'nullable',
                'boolean'
            ],

            'estructura_de_la_pregunta' => [
                'nullable',
                'string',
            ], 
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'titulo_de_la_pregunta.required' => 'El título de la pregunta es obligatorio.',
            'titulo_de_la_pregunta.string' => 'El título de la pregunta debe ser una cadena de texto.',
            'titulo_de_la_pregunta.min' => 'El título de la pregunta debe tener al menos 5 caracteres.',
            'titulo_de_la_pregunta.max' => 'El título de la pregunta no debe exceder los 920 caracteres.',
            'titulo_de_la_pregunta.regex' => 'El título de la pregunta contiene caracteres no permitidos.',

            'instrucciones_de_la_pregunta.string' => 'Las instrucciones deben ser una cadena de texto.',
            'instrucciones_de_la_pregunta.min' => 'Las instrucciones deben tener al menos 10 caracteres.',
            'instrucciones_de_la_pregunta.max' => 'Las instrucciones no deben exceder los 8000 caracteres.',
            'instrucciones_de_la_pregunta.regex' => 'Las instrucciones contienen caracteres no permitidos.',

            'comentarios_de_la_pregunta.boolean' => 'Los comentarios deben ser verdadero o falso.',

            'estructura_de_la_pregunta.string' => 'La estructura debe ser una cadena de texto.',
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function(BeforeSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- 1. Validación de encabezados ---
                $headers = [
                    $sheet->getCell('A1')->getValue(),
                    $sheet->getCell('B1')->getValue(),
                    $sheet->getCell('C1')->getValue(),
                    $sheet->getCell('D1')->getValue()
                ];
                
                $expectedHeaders = [
                    'Titulo de la pregunta',
                    'Instrucciones de la pregunta',
                    'Comentarios de la pregunta',
                    'Estructura de la pregunta'
                ]; 

                // Validar cada encabezado
                for ($i = 0; $i < count($expectedHeaders); $i++) {
                    if (trim(strtolower($headers[$i] ?? '')) !== strtolower($expectedHeaders[$i])) {
                        $headersList = implode("', '", $expectedHeaders);
                        $foundList = implode("', '", array_map(fn($h) => $h ?? 'vacío', $headers));
                        throw new \Exception("El archivo no es válido. Los encabezados deben ser '{$headersList}', pero se encontraron '{$foundList}'.");
                    }
                }

                // --- 2. Validación de archivo vacío ---
                $lastRow = $sheet->getHighestRow();
                $isEmpty = false;

                if ($lastRow < 2) {
                    $isEmpty = true;
                } else {
                    $valA2 = $sheet->getCell('A2')->getValue();
                    $valB2 = $sheet->getCell('B2')->getValue();
                    $valC2 = $sheet->getCell('C2')->getValue();
                    $valD2 = $sheet->getCell('D2')->getValue();

                    if (empty(trim($valA2 ?? '')) && empty(trim($valB2 ?? '')) && empty(trim($valC2 ?? '')) && empty(trim($valD2 ?? ''))) {
                        $isEmpty = true;
                    }
                }

                if ($isEmpty) {
                    throw new \Exception("El archivo está vacío. Por favor, asegúrese de que contiene datos.");
                }
            },
        ];
    }

    public function collection(Collection $collection)
    {
        // Arrays para rastrear duplicados
        $processedInFile = []; // Para detectar duplicados DENTRO del archivo
        $validRows = []; // Filas válidas para insertar

        // PASO 1: Recorrer todas las filas y detectar duplicados
        foreach ($collection as $index => $row) {
            $rowNumber = $index + 2;

            // Aplicar trim a los campos que se validan
            $nombre = trim($row['titulo_de_la_pregunta'] ?? '');
            $instrucciones = trim($row['instrucciones_de_la_pregunta'] ?? '');

            // Campos adicionales sin validación de duplicados
            $comentarios = trim($row['comentarios_de_la_pregunta'] ?? '');
            $estructura = trim($row['estructura_de_la_pregunta'] ?? '');

            // Si el nombre está vacío, saltar
            if ($nombre === '') {
                continue;
            }

            // Crear una clave única para la combinación de los 2 campos validados
            $uniqueKey = strtolower($nombre) . '|' . strtolower($instrucciones);

            // Verificar duplicados DENTRO del mismo archivo
            if (isset($processedInFile[$uniqueKey])) {
                $this->failures[] = new Failure(
                    $rowNumber,
                    'titulo_de_la_pregunta',
                    ["Esta combinación ya aparece en la fila {$processedInFile[$uniqueKey]} del archivo."],
                    [
                        'titulo_de_la_pregunta' => $nombre,
                        'instrucciones_de_la_pregunta' => $instrucciones
                    ]
                );
                continue;
            }

            // Verificar duplicados en la BASE DE DATOS
            if (empty($instrucciones)) {
                $existingQuestion = Question::whereRaw('TRIM(name) = ?', [$nombre])
                    ->where(function ($query) {
                        $query->whereNull('instructions')
                            ->orWhere('instructions', '');
                    })
                    ->first();
            } else {
                $existingQuestion = Question::whereRaw('TRIM(name) = ?', [$nombre])->whereRaw('TRIM(COALESCE(instructions, \'\')) = ?', [$instrucciones])->first();
            }

            if ($existingQuestion) {
                $this->failures[] = new Failure(
                    $rowNumber,
                    'titulo_de_la_pregunta',
                    ["Esta combinación de título e instrucciones ya existe en la base de datos."],
                    [
                        'titulo_de_la_pregunta' => $nombre,
                        'instrucciones_de_la_pregunta' => $instrucciones
                    ]
                );
                continue;
            }

            //FIX: Registrar la clave ANTES de agregar a validRows
            $processedInFile[$uniqueKey] = $rowNumber;

            // Guardar para inserción
            $validRows[] = [
                'name'               => $nombre,
                'instructions'       => $instrucciones ?: null,
                'commentaries'       => filter_var($comentarios, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'question_structure' => $estructura ?: null,
            ];
        }

        // PASO 2: Insertar solo las filas válidas
        foreach ($validRows as $data) {
            Question::create($data);
        }
    }
}