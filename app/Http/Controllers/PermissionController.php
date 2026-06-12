<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Resources\Permission\PermissionResource;
use App\Http\Resources\ListResource;
use App\Http\Resources\SelectorResource;
use App\Http\Requests\PermissionRequest\StorePermissionRequest;
use App\Http\Requests\PermissionRequest\UpdatePermissionRequest;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ModelSearchController;


class PermissionController extends Controller
{
    /**
    * Display a listing of the resource.
    */
    public function index()/* listado para un selector */
    {
        return SelectorResource::collection(Permission::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request)//ya esta validado con el StorePermissionRequest
    {
        try {
            Permission::create($request->validated());
            return response()->json([
                'message' => 'Permiso creado con exito',
            ], 201);
        } catch (\Throwable $e) {
           return response()->json([
                'message' => 'El permiso no se pudo crear',
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)//muestra incluso los desactivados
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $permission = Permission::withTrashed()->find($decryptedId);

            if (!$permission) {
                return response()->json([
                    'message' => 'Permiso no encontrado',
                ], 404);
            }

            return new PermissionResource($permission);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Permiso no encontrado',
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRequest $request, string $id)//actualiza con las mismas reglas que la creacion y solo recursos activos
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $permission = Permission::find($decryptedId);

            if (!$permission) {
                return response()->json([
                    'message' => 'Permiso no encontrado',
                ], 404);
            }

            if (empty($request->validated())) {
                return response()->json([
                    'message' => 'No se enviaron datos para actualizar.',
                ], 400);
            }

            $permission->update($request->validated());

            return response()->json([
                'message' => 'Permiso actualizado con éxito.',
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
            $permission = Permission::find($decryptedId);

            if (!$permission) {
                return response()->json([
                    'message' => 'Permiso no encontrado',
                ], 404);
            }

            /* desactivamos el recurso */
            $permission->delete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Permiso desactivado con exito',
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
            $permission = Permission::onlyTrashed()->find($decryptedId);
 
            if (!$permission) {
                return response()->json([
                    'message' => 'Permiso no encontrado',
                ], 404);
            }

            /* reactivamos el recurso */
            $permission->restore();
            //mensaje de exito 
            return response()->json([
                'message' => 'Permiso activado con exito',
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
            $permission = Permission::onlyTrashed()->find($decryptedId);

            if (!$permission) {
                return response()->json([
                    'message' => 'Permiso no encontrado',
                ], 404);
            }

            /* eliminamos definitivamente el recurso */
            $permission->forceDelete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Permiso eliminado definitivamente con exito',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Eror al procesar la solicitud',
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
            'active' => Permission::query(),
            'inactive' => Permission::onlyTrashed(),
            'all' => Permission::withTrashed(),
        };

        $permissions = ModelSearchController::getPaginatedResults($query, $request);
        return ListResource::collection($permissions);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Error al procesar la solicitud',
        ], 500);
    }
}
}
