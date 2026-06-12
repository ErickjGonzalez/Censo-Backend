<?php

namespace App\Imports;

use App\Models\Module;
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

// Agregamos SkipsOnFailure a la lista de implementaciones
class ModulesImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithEvents, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    private $customFailures = [];

    public function rules(): array
    {
        return [
            'nombre_de_la_unidad' => 'required|string|min:5|max:65|unique:modules,name|regex:/^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ\s\'\-\.,\/]+$/u',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nombre_de_la_unidad.required' => 'El nombre es obligatorio.',
            'nombre_de_la_unidad.min' => 'El nombre debe tener al menos 5 caracteres.',
            'nombre_de_la_unidad.max' => 'El nombre no puede exceder los 65 caracteres.',
            'nombre_de_la_unidad.regex' => 'Formato inválido (solo letras y caracteres permitidos).',
            'nombre_de_la_unidad.unique' => 'El nombre ya existe en el sistema.',
        ];
    }

    /* valida nombre de las celdas */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $header = $event->sheet->getDelegate()->getCell('A1')->getValue();
                $expectedHeaders = ['Nombre de la unidad'];

                if (trim(strtolower($header)) !== strtolower($expectedHeaders[0])) {
                    throw new \Exception("El archivo no es válido. El encabezado A1 debe ser '$expectedHeaders[0]'.");
                }
            },
        ];
    }

    public function collection(Collection $rows)
    {
        // 1. Preparación y Normalización
        // Filtramos y limpiamos los datos. Nota: Las filas que violaron rules() (ej. min:5)
        // ya fueron eliminadas automáticamente por Laravel Excel gracias a SkipsOnFailure,
        // así que aquí llegan mayormente limpias.

        $nombresEnArchivo = $rows->map(function ($row) {
            return isset($row['nombre_de_la_unidad']) ? strtolower(trim($row['nombre_de_la_unidad'])) : null;
        })->filter();

        $nuevosRegistros = [];
        $nombresProcesadosEnEsteArchivo = [];
        $now = now();

        foreach ($rows as $index => $row) {
            $filaExcel = $index + 2;

            // Doble chequeo por seguridad, aunque rules() ya debió atrapar los vacíos
            if (!isset($row['nombre_de_la_unidad']) || trim($row['nombre_de_la_unidad']) === '') {
                continue;
            }

            $nombreOriginal = trim($row['nombre_de_la_unidad']);
            $nombreNormalizado = strtolower($nombreOriginal);

            // B. ¿Está duplicado en este mismo archivo?
            if (isset($nombresProcesadosEnEsteArchivo[$nombreNormalizado])) {
                $this->addCustomFailure($filaExcel, 'nombre_de_la_unidad', 'Registro duplicado dentro del archivo.', $nombreOriginal);
                continue;
            }

            // Si pasa todo, lo preparamos para insertar
            $nombresProcesadosEnEsteArchivo[$nombreNormalizado] = true;

            $nuevosRegistros[] = [
                'name' => $nombreOriginal,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // 3. Inserción Masiva
        if (count($nuevosRegistros) > 0) {
            foreach (array_chunk($nuevosRegistros, 1000) as $chunk) {
                Module::insert($chunk);
            }
        }
    }

    private function addCustomFailure($row, $attribute, $message, $value)
    {
        $this->customFailures[] = new Failure($row, $attribute, [$message], [$attribute => $value]);
    }

    public function failures(): Collection
    {
        // Fusionamos los errores de validación estándar (rules) con los errores lógicos (duplicados)
        return (new Collection($this->failures))->merge($this->customFailures);
    }
}