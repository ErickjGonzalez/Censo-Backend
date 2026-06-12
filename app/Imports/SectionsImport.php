<?php

namespace App\Imports;

use App\Models\Section;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Validators\Failure;

class SectionsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithEvents, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    public function rules(): array
    {
        return [
            'nombre_de_la_seccion' => [
                'required',
                'string',
                'min:5',
                'max:180',
                'regex:/^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ0-9\s\/,.:\-( )%#´]+$/u'
            ],

            'instrucciones_de_la_seccion' => [
                'nullable',
                'string',
                'min:10',
                'max:7600',
                'regex:/^[\pL\pM\pN\s\r\n\t\,\.\:\;\-–—\/\"“”‘’\_()º°´&%\[\]\|¿?¡!]+$/u'
            ],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nombre_de_la_seccion.required' => 'El nombre es obligatorio.',
            'nombre_de_la_seccion.string' => 'El nombre debe ser una cadena de texto.',
            'nombre_de_la_seccion.min' => 'El nombre debe tener al menos 5 caracteres.',
            'nombre_de_la_seccion.max' => 'El nombre no puede exceder los 180 caracteres.',
            'nombre_de_la_seccion.regex' => 'El nombre no puede tener emojis ni otros símbolos.',

            'instrucciones_de_la_seccion.string' => 'La instrucción debe ser una cadena de texto.',
            'instrucciones_de_la_seccion.min' => 'La instrucción debe tener al menos 10 caracteres.',
            'instrucciones_de_la_seccion.max' => 'La instrucción no puede exceder los 7600 caracteres.',
            'instrucciones_de_la_seccion.regex' => 'La instrucción no puede tener emojis ni otros símbolos.',
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function(BeforeSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- 1. Tu validación de encabezados (igual que antes) ---
                $headers = [
                    $sheet->getCell('A1')->getValue(),
                    $sheet->getCell('B1')->getValue()
                ];
                $expectedHeaders = ['Nombre de la seccion', 'Instrucciones de la seccion']; 

                if (trim(strtolower($headers[0])) !== strtolower($expectedHeaders[0]) || 
                    trim(strtolower($headers[1])) !== strtolower($expectedHeaders[1])) {
                    throw new \Exception("El archivo no es válido. Los encabezados deben ser '{$expectedHeaders[0]}' y '{$expectedHeaders[1]}', pero se encontraron '{$headers[0]}' y '{$headers[1]}'.");
                }
                
                $lastRow = $sheet->getHighestRow();
                $isEmpty = false;

                if ($lastRow < 2) {
                    $isEmpty = true;
                } 
                
                else {
                    $valA2 = $sheet->getCell('A2')->getValue();
                    $valB2 = $sheet->getCell('B2')->getValue();

                    if (empty(trim($valA2)) && empty(trim($valB2))) {
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
            
            // Aplicar trim
            $nombre = trim($row['nombre_de_la_seccion'] ?? '');
            $instrucciones = trim($row['instrucciones_de_la_seccion'] ?? '');

            // Si el nombre está vacío, saltar
            if ($nombre === '') {
                continue;
            }

            // Crear una clave única para la combinación
            $uniqueKey = strtolower($nombre) . '|' . strtolower($instrucciones);

            // Verificar duplicados DENTRO del mismo archivo
            if (isset($processedInFile[$uniqueKey])) {
                $this->failures[] = new Failure(
                    $rowNumber,
                    'nombre_de_la_seccion',
                    ["Esta combinación ya aparece en la fila {$processedInFile[$uniqueKey]} del archivo."],
                    [
                        'nombre_de_la_seccion' => $nombre,
                        'instrucciones_de_la_seccion' => $instrucciones
                    ]
                );
                continue;
            }

            // Verificar duplicados en la BASE DE DATOS (compatible con PostgreSQL)
            if (empty($instrucciones)) {
                // Si instrucciones está vacía, buscar registros con instructions NULL o vacío
                $existingSection = Section::whereRaw('TRIM(name) = ?', [$nombre])
                    ->where(function($query) {
                        $query->whereNull('instructions')
                            ->orWhere('instructions', '');
                    })
                    ->first();
            } else {
                // Si instrucciones tiene valor, buscar coincidencia exacta
                $existingSection = Section::whereRaw('TRIM(name) = ?', [$nombre])
                    ->whereRaw('TRIM(COALESCE(instructions, \'\')) = ?', [$instrucciones])
                    ->first();
            }

            if ($existingSection) {
                $this->failures[] = new Failure(
                    $rowNumber,
                    'nombre_de_la_seccion',
                    ["Esta combinación de nombre e instrucciones ya existe en la base de datos."],
                    [
                        'nombre_de_la_seccion' => $nombre,
                        'instrucciones_de_la_seccion' => $instrucciones
                    ]
                );
                continue;
            }

            // Marcar como procesada y guardar para inserción
            $processedInFile[$uniqueKey] = $rowNumber;
            $validRows[] = [
                'name' => $nombre,
                'instructions' => $instrucciones,
            ];
        }

        // PASO 2: Insertar solo las filas válidas
        foreach ($validRows as $data) {
            Section::create($data);
        }
    }
    
}