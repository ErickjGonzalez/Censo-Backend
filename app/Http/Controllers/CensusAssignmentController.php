<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Institution;
use App\Models\Assignment;
use App\Http\Resources\SelectorResource;
use Illuminate\Support\Facades\Crypt;
use App\Models\CensusSection;
use App\Http\Requests\AssignmentRequest\StoreAssignmentRequest;
use App\Http\Requests\AssignmentRequest\UpdateAssignmentRequest;

class CensusAssignmentController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(string $id)/* id de la seccion para asignaselo a una institucion */
    {
        try {
            $decryptedId = Crypt::decryptString($id);

            $section = CensusSection::select('id', 'section_id', 'index_id')
            ->with('section:id,name', 'index:id,name')
            ->find($decryptedId);

            if (!$section) {
                return response()->json([
                    'message' => 'Sección no encontrada',
                ], 404);
            }

            $section = [
                'section_id' => $id,
                'index'      => $section->index->name,
                'name'  => $section->section->name,
            ];

            /* cargamos todas las instituciones */

            /* cargamos todas las relaciones predefinidas de esta seccion de la tabla assigments*/
            $assignments = Assignment::where('census_section_id', $decryptedId)
            ->with('institution:id,name')
            ->get()
            ->map(function ($assignment) {
                return [
                    'id'             => Crypt::encryptString($assignment->id),
                    'institution_id' => Crypt::encryptString($assignment->institution_id),
                    'name'           => $assignment->institution->name,
                ];
            });
            $institutions = SelectorResource::collection(Institution::all());

            return response()->json([
                'section' => $section,
                'institutions' => $institutions,
                'assignments' => $assignments,
            ], 200);


        } catch (\Throwable $e) {
           return response()->json([
                'message' => 'Error al obtener el censo'.$e->getMessage(),
            ], 400);
        }
    }

    public function store(StoreAssignmentRequest $request)
    {
            try {
                $validated = $request->validated();

                $censusSection = CensusSection::find($validated['census_section_id']);

                $institutionIds = collect($validated['items'])->pluck('institution_id')->toArray();

                $censusSection->institutions()->sync($institutionIds);

                return response()->json([
                    'message' => 'Instituciones asignadas correctamente.',
                ], 201);

            } catch (\Throwable $e) {
                return response()->json([
                    'message' => 'Error al asignar las instituciones.',
                ], 400);
            }
    }

        public function update(UpdateAssignmentRequest $request, string $id)
        {
            try {
                $decryptedId = Crypt::decryptString($id);
                $validated = $request->validated();
    
                $censusSection = CensusSection::find($decryptedId);
    
                if (!$censusSection) {
                    return response()->json([
                        'message' => 'Sección no encontrada.',
                    ], 404);
                }
    
                $institutionIds = collect($validated['items'])->pluck('institution_id')->toArray();
    
                $censusSection->institutions()->sync($institutionIds);
    
                return response()->json([
                    'message' => 'Instituciones actualizadas correctamente.',
                ], 200);
    
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => 'Error al actualizar las instituciones.',
                ], 400);
            }
        }
}
