<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Census;
use App\Http\Resources\Census\CensusResource;
use App\Http\Resources\Census\CensusContentResource;
use App\Http\Resources\ListResource;
use App\Http\Resources\SelectorResource;
use App\Http\Requests\CensusRequest\StoreCensusRequest;
use App\Http\Requests\CensusRequest\UpdateCensusRequest;
use Illuminate\Support\Facades\Crypt;
use App\Models\CensusModule;
use App\Models\CensusSection;
use App\Models\CensusQuestion;
use App\Models\Assignment;
use Illuminate\Support\Facades\DB;
use Throwable;

class CensusController extends Controller
{
        /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SelectorResource::collection(Census::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCensusRequest $request)//ya esta validado con el StoreCensusRequest
    {
        try {
            Census::create($request->validated());
            return response()->json([
                'message' => 'Censo creado con exito',
            ], 201);
        } catch (\Throwable $e) {
           return response()->json([
                'message' => 'El censo no se pudo crear',
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)//muestra solo recursos activos
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $censo = Census::withTrashed()->find($decryptedId);

            if (!$censo) {
                return response()->json([
                    'message' => 'Censo no encontrado',
                ], 404);
            }

            return new CensusResource($censo);/* mandar la info del censo y poner los links de usrs asignados unidades y todo eso y la grafica */

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Censo no encontrado',
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCensusRequest $request, string $id)//actualiza con las mismas reglas que la creacion y solo recursos activos
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $censo = Census::find($decryptedId);

            if (!$censo) {
                return response()->json([
                    'message' => 'Censo no encontrado',
                ], 404);
            }

            if (empty($request->validated())) {
                return response()->json([
                    'message' => 'No se enviaron datos para actualizar.',
                ], 400);
            }

            $censo->update($request->validated());
            return response()->json([
                'message' => 'Censo actualizado con exito',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)//soft delete revisa en todos los registros activos
    {
         try {
            $decryptedId = Crypt::decryptString($id);
            
            $censo = Census::find($decryptedId);

            if (!$censo) {
                return response()->json([
                    'message' => 'Censo no encontrado',
                ], 404);
            }

            /* desactivamor el recurso */
            $censo->delete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Censo desactivado con exito',
            ], 200);


        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    public function restore(string $id)//reactivar un recurso eliminado y solo busca en los eliminados
    {
         try {
            $decryptedId = Crypt::decryptString($id);
            $censo = Census::onlyTrashed()->find($decryptedId);
 
            if (!$censo) {
                return response()->json([
                    'message' => 'Censo no encontrado',
                ], 404);
            }

            /* reactivamos el recurso */
            $censo->restore();
            //mensaje de exito 
            return response()->json([
                'message' => 'Censo activado con exito',
            ], 200);


        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    public function forceDelete(string $id)//busca solo en los eliminados para eliminar definitivamente
    {
         try {
            $decryptedId = Crypt::decryptString($id);
            $censo = Census::onlyTrashed()->find($decryptedId);

            if (!$censo) {
                return response()->json([
                    'message' => 'Censo no encontrado',
                ], 404);
            }

            /* eliminamos definitivamente el recurso */
            $censo->forceDelete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Censo eliminado definitivamente con exito',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    //metodo content para el soft delete
    public function content($content)/* agregar las fechas de inicio y fin */
    {
        try {
            switch ($content) {
                case 'active':
                    return CensusContentResource::collection(Census::paginate(9));
                    break;
                case 'inactive':
                    return CensusContentResource::collection(Census::onlyTrashed()->paginate(9));
                    break;
                case 'all':
                    return CensusContentResource::collection(Census::withTrashed()->paginate(9));
                    break;
                default:
                    return response()->json([
                        'message' => 'Contenido no válido. Use "active", "inactive" o "all".',
                    ], 400);
                }
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    public function clone(Request $request)
    {
        try {
            $sourceCensusId = Crypt::decryptString($request->source_census_id);
            $targetCensusId = Crypt::decryptString($request->target_census_id);

            $sourceCensus = Census::find($sourceCensusId);
            $targetCensus = Census::find($targetCensusId);

            if (!$sourceCensus || !$targetCensus) {
                return response()->json([
                    'message' => 'Uno o ambos censos no fueron encontrados.',
                ], 404);
            }

            $sourceModules = CensusModule::where('census_id', $sourceCensusId)
                ->with(['censusSections.censusQuestions', 'censusSections.assignments'])
                ->get();

            if ($sourceModules->isEmpty()) {
                return response()->json([
                    'message' => 'El censo origen no tiene módulos asignados.',
                ], 422);
            }

            DB::transaction(function () use ($sourceCensusId, $targetCensusId, $sourceModules) {
                // Borrar todo lo existente del target en cascada
                CensusModule::where('census_id', $targetCensusId)->delete();

                // Clonar módulos
                foreach ($sourceModules as $module) {
                    $newModule = CensusModule::create([
                        'census_id' => $targetCensusId,
                        'module_id' => $module->module_id,
                        'index_id'  => $module->index_id,
                    ]);

                    // Clonar secciones
                    foreach ($module->censusSections as $section) {
                        $newSection = CensusSection::create([
                            'census_module_id' => $newModule->id,
                            'section_id'       => $section->section_id,
                            'index_id'         => $section->index_id,
                        ]);

                        // Clonar preguntas
                        foreach ($section->censusQuestions as $question) {
                            CensusQuestion::create([
                                'census_section_id' => $newSection->id,
                                'question_id'       => $question->question_id,
                                'index_id'          => $question->index_id,
                            ]);
                        }

                        // Clonar assignments
                        foreach ($section->assignments as $assignment) {
                            Assignment::create([
                                'census_section_id' => $newSection->id,
                                'institution_id'    => $assignment->institution_id,
                            ]);
                        }
                    }
                }
            });

            return response()->json([
                'message' => 'Censo clonado correctamente.',
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al clonar el censo: ' . $e->getMessage(),
            ], 400);
        }
    }
}
