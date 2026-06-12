<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Http\Resources\Role\RoleResource;
use App\Http\Resources\ListResource;
use App\Http\Resources\SelectorResource;
use App\Http\Requests\RoleRequest\StoreRoleRequest;
use App\Http\Requests\RoleRequest\UpdateRoleRequest;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ModelSearchController;

class RoleController extends Controller
{
    /**
    * Display a listing of the resource.
    */
    public function index()/* listado para un selector */
    {
        return SelectorResource::collection(Role::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)//ya esta validado con el StoreRoleRequest
    {
        try {
            Role::create($request->validated());
            return response()->json([
                'message' => 'Rol creado con exito',
            ], 201);
        } catch (\Throwable $e) {
           return response()->json([
                'message' => 'El rol no se pudo crear',
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
            $role = Role::withTrashed()->find($decryptedId);

            if (!$role) {
                return response()->json([
                    'message' => 'Rol no encontrado',
                ], 404);
            }

            return new RoleResource($role);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Rol no encontrado',
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, string $id)//actualiza con las mismas reglas que la creacion y solo recursos activos
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $role = Role::find($decryptedId);

            if (!$role) {
                return response()->json([
                    'message' => 'Rol no encontrado',
                ], 404);
            }

            if (empty($request->validated())) {
                return response()->json([
                    'message' => 'No se enviaron datos para actualizar.',
                ], 400);
            }

            $role->update($request->validated());

            return response()->json([
                'message' => 'Rol actualizado con éxito.',
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
            
            $role = Role::find($decryptedId);

            if (!$role) {
                return response()->json([
                    'message' => 'Rol no encontrado',
                ], 404);
            }

            /* desactivamor el recurso */
            $role->delete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Rol desactivado con exito',
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
            $role = Role::onlyTrashed()->find($decryptedId);
 
            if (!$role) {
                return response()->json([
                    'message' => 'Rol no encontrado',
                ], 404);
            }

            /* reactivamos el recurso */
            $role->restore();
            //mensaje de exito 
            return response()->json([
                'message' => 'Rol activado con exito',
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
            $role = Role::onlyTrashed()->find($decryptedId);

            if (!$role) {
                return response()->json([
                    'message' => 'Rol no encontrado',
                ], 404);
            }

            /* eliminamos definitivamente el recurso */
            $role->forceDelete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Rol eliminado definitivamente con exito',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

  public function content(Request $request, $content)
{
    try {
        if (!in_array($content, ['active', 'inactive', 'all'])) {
            return response()->json([
                'message' => 'Contenido no válido. Use "active", "inactive" o "all".'
            ], 400);
        }

        $query = match($content) {
            'active' => Role::query(),
            'inactive' => Role::onlyTrashed(),
            'all' => Role::withTrashed(),
        };

        $roles = ModelSearchController::getPaginatedResults($query, $request);
        return ListResource::collection($roles);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Error al procesar la solicitud',
        ], 500);
    }
}

}