<?php

namespace App\Imports;

use App\Models\Index;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Validators\Failure;

class IndexsImport implements ToCollection, WithValidation, WithHeadingRow, SkipsOnFailure, SkipsEmptyRows, WithEvents
{
    
    private array $failures = [];

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->failures[] = $failure;
        }
    }

    public function failures(): Collection
    {
        return collect($this->failures);
    }

    public function prepareForValidation(array $data, int $index): array
    {
        if (isset($data['nombre_del_indice'])) {
            $valor = $data['nombre_del_indice'];

            if (is_float($valor) && floor($valor) == $valor) {
                $valor = (string)(int)$valor;
            } else {
                $valor = (string)$valor;
            }

            $data['nombre_del_indice'] = trim($valor);
        }

        return $data;
    }

    public function collection(Collection $rows)
    {
        $nuevosRegistros = [];
        $nombresProcesadosEnEsteArchivo = [];
        $now = now();

        foreach ($rows as $index => $row) {
            $filaExcel = $index + 2;

            if (!isset($row['nombre_del_indice']) || trim($row['nombre_del_indice']) === '') {
                continue;
            }

            $nombreOriginal = trim($row['nombre_del_indice']);
            $nombreNormalizado = strtolower($nombreOriginal);

            if (isset($nombresProcesadosEnEsteArchivo[$nombreNormalizado])) {
                $this->failures[] = new Failure($filaExcel, 'nombre_del_indice', ['Registro duplicado dentro del archivo.'], ['nombre_del_indice' => $nombreOriginal]);
                continue;
            }

            $nombresProcesadosEnEsteArchivo[$nombreNormalizado] = true;

            $nuevosRegistros[] = [
                'name' => $nombreOriginal,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (count($nuevosRegistros) > 0) {
            foreach (array_chunk($nuevosRegistros, 1000) as $chunk) {
                Index::insert($chunk);
            }
        }
    }

    public function rules(): array
    {
        return [
            'nombre_del_indice' => [
                'required',
                'string',
                'min:1',
                'max:45',
                'regex:/^(?:[1-9]\d*)(?:\.(?:[1-9]\d*))*$/',
                'unique:indexs,name'
            ],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nombre_del_indice.required' => 'El nombre del índice es obligatorio.',
            'nombre_del_indice.string'   => 'El nombre del índice debe ser una cadena de texto.',
            'nombre_del_indice.min'      => 'El nombre del índice debe tener al menos 1 caracteres.',
            'nombre_del_indice.max'      => 'El nombre del índice no puede exceder los 45 caracteres.',
            'nombre_del_indice.regex'    => 'Formato inválido (ej. 1.1.2)',
            'nombre_del_indice.unique'   => 'El nombre del índice ya está en uso.',
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $header = $event->sheet->getDelegate()->getCell('A1')->getValue();
                $expectedHeader = 'Nombre del Indice';

                if (trim(strtolower((string)$header)) !== strtolower($expectedHeader)) {
                    throw new \Exception("El archivo no es válido. El encabezado debe ser '$expectedHeader', pero se encontró '$header'.");
                }
            },
        ];
    }
}