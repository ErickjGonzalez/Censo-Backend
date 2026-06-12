<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\Crypt;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\PermissionRoleRequest\StorePermissionRoleRequest;
use App\Http\Requests\PermissionRoleRequest\UpdatePermissionRoleRequest;

class PermissionRoleController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(string $id)/* obtienes los permisos de un rol */
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $role = Role::select('id', 'name')->with(['permissions:id,name'])->findOrFail($decryptedId);
            /* $role = $role->id = $id; */
            $permissions = Permission::select('id', 'name')->get();

            return response()->json([
                'encrypted_role_id' => $id,
                'role' => $role,/* manda el rol y los ids de los permisos */
                'permissionsCollection' => $permissions,
            ], 200);  

        }catch (ModelNotFoundException $e) {/* ayuda a que si el registro no existe o tiene un id que no contiene nada solo marque que no tiene  un rol*/
            return response()->json([
                'message' => 'Rol no encontrado',
            ], 404);
        }
        catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }  
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRoleRequest $request)
    {
        try{
            $roleId = $request->validated('role_id');
            $permissions = $request->validated('permissions_ids');
            
            $role = Role::find($roleId);
            $role->permissions()->sync($permissions);
            return response()->json([
                'message' => 'Permisos asignados al rol correctamente.',
            ], 200);

        }catch(\Throwable $e){
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRoleRequest $request, string $id)/* id es el id del rol y el request es el request  */
    {
        //
        try{
            $roleId = Crypt::decryptString($id);/* el id se mandó por el url */
            $permissions = $request->validated('permissions_ids');
            
            $role = Role::find($roleId);

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

            $role->permissions()->sync($permissions);
            return response()->json([
                'message' => 'Permisos asignados al rol correctamente.',
            ], 200);

        }catch(\Throwable $e){
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

}
