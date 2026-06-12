<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Institution;
use App\Http\Resources\Institution\InstitutionResource;
use App\Http\Resources\SelectorResource;
use App\Http\Requests\InstitutionRequest\StoreInstitutionRequest;
use App\Http\Requests\InstitutionRequest\UpdateInstitutionRequest;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ModelSearchController;


class InstitutionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()/* listado para un selector */
    {
        return SelectorResource::collection(Institution::all());
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInstitutionRequest $request)//ya esta validado con el StoreInstitutionRequest
    {
        try {
            Institution::create($request->validated());
            return response()->json([
                'message' => 'Institución creada con exito',
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'La Institución no se pudo crear',
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
            $institution = Institution::withTrashed()->find($decryptedId);

            if (!$institution) {
                return response()->json([
                    'message' => 'Institución no encontrada',
                ], 404);
            }

            return new InstitutionResource($institution);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Institución no encontrada',
            ], 400);
        }
    }
  
     public function getMap(string $id)
{
    try {
        $decryptedId = Crypt::decryptString($id);

        $institution = Institution::withTrashed()
            ->select('geocode', 'municipality', 'lat', 'lon')
            ->find($decryptedId);

        if (!$institution) {
            return response()->json([
                'message' => 'Institución no encontrada',
            ], 404);
        }

        return response()->json($institution, 200);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Error al procesar la solicitud',
        ], 400);
    }
}

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInstitutionRequest $request, string $id)//actualiza con las mismas reglas que la creacion y solo recursos activos
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $institution = Institution::find($decryptedId);

            if (!$institution) {
                return response()->json([
                    'message' => 'Institución no encontrada',
                ], 404);
            }

            if (empty($request->validated())) {
                return response()->json([
                    'message' => 'No se enviaron datos para actualizar.',
                ], 400);
            }

            $institution->update($request->validated());

            return response()->json([
                'message' => 'Institución actualizada con exito',
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
            $institution = Institution::find($decryptedId);

            if (!$institution) {
                return response()->json([
                    'message' => 'Institución no encontrada',
                ], 404);
            }

            /* desactivamor el recurso */
            $institution->delete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Institución desactivada con exito',
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
             $institution = Institution::onlyTrashed()->find($decryptedId);
 
            if (!$institution) {
                return response()->json([
                    'message' => 'Institución no encontrada',
                ], 404);
            }

            /* reactivamos el recurso */
            $institution->restore();
            //mensaje de exito 
            return response()->json([
                'message' => 'Institución activada con exito',
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
            $institution = Institution::onlyTrashed()->find($decryptedId);

            if (!$institution) {
                return response()->json([
                    'message' => 'Institución no encontrada',
                ], 404);
            }

            /* eliminamos definitivamente el recurso */
            $institution->forceDelete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Institución eliminada definitivamente con exito',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    //metodo content para el soft delete
   public function content(Request $request, $content)
{
    try {
        if (!in_array($content, ['active', 'inactive', 'all'])) {
            return response()->json([
                'message' => 'Contenido no válido. Use "active", "inactive" o "all".',
            ], 400);
        }

        $query = match($content) {
            'active' => Institution::query(),
            'inactive' => Institution::onlyTrashed(),
            'all' => Institution::withTrashed(),
        };

        $institutions = ModelSearchController::getPaginatedResults($query, $request);
        return InstitutionResource::collection($institutions);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Error al procesar la solicitud',
        ], 500);
    }
}
}
