<?php

namespace App\Services\ImportServices;
use Maatwebsite\Excel\Facades\Excel;

class ImportService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
       
    }

   /* field necesito que mande los valores que estaban en la tabla  */
   public function import($file, $importClass)
    {
        try {
            // 1. Instanciamos la clase manualmente
            $importInstance = new $importClass;

            // 2. Ejecutamos el import usando la instancia
            \Maatwebsite\Excel\Facades\Excel::import($importInstance, $file);

            // 3. Revisamos si hubo fallos de validación
            if ($importInstance->failures()->isNotEmpty()) {
                $failures = [];
                foreach ($importInstance->failures() as $failure) {
                // Obtenemos el nombre del campo que falló (ej. 'nombre_de_unidad')
                $field = $failure->attribute();
                
                // Obtenemos todos los valores de esa fila
                $values = $failure->values();

                $failures[] = [
                    'row'    => $failure->row(),
                    'field'  => $field,
                    'errors' => $failure->errors(),
                    // Aquí extraemos solo el valor específico que causó el problema
                    'invalid_value' => $values[$field] ?? 'Valor no encontrado', 
                ];
            }

                return response()->json([
                    'success' => false,
                    'message' => 'Algunos registros tienen errores de validación.',
                    'errors' => $failures
                ], 422); // Código 422 para errores de validación
            }

            return response()->json([
                'success' => true,
                'message' => 'Registros importados con éxito',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error crítico: ' . $e->getMessage(),
            ], 500);
        }
    }
}
