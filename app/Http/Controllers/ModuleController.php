<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use App\Http\Resources\Module\ModuleResource;
use App\Http\Resources\ListResource;
use App\Http\Resources\SelectorResource;
use App\Http\Requests\ModuleRequest\StoreModuleRequest;
use App\Http\Requests\ModuleRequest\UpdateModuleRequest;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\ImportRequest\UploadRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ModuleImport;
use App\Services\ImportServices\ImportService;
use App\Http\Controllers\ModelSearchController;

class ModuleController extends Controller
{

    /* mandar a llamar el service */
    protected ImportService $importService;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SelectorResource::collection(Module::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreModuleRequest $request)
    {
        try {
            Module::create($request->validated());
            return response()->json([
                'message' => 'Módulo creado con exito',
            ], 201);
        } catch (\Throwable $e) {
           return response()->json([
                'message' => 'El módulo no se pudo crear',
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $module = Module::withTrashed()->find($decryptedId);

            if (!$module) {
                return response()->json([
                    'message' => 'Módulo no encontrado',
                ], 404);
            }

            return new ModuleResource($module);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateModuleRequest $request, string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $module = Module::find($decryptedId);

            if (!$module) {
                return response()->json([
                    'message' => 'Módulo no encontrado',
                ], 404);
            }

            if (empty($request->validated())) {
                return response()->json([
                    'message' => 'No se enviaron datos para actualizar.',
                ], 400);
            }

            $module->update($request->validated());

            return response()->json([
                'message' => 'Módulo actualizado con éxito.',
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
    public function destroy(string $id)
    {
         try {
            $decryptedId = Crypt::decryptString($id);
            
            $module = Module::find($decryptedId);

            if (!$module) {
                return response()->json([
                    'message' => 'Módulo no encontrado',
                ], 404);
            }

            /* desactivamor el recurso */
            $module->delete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Módulo desactivado con exito',
            ], 200);


        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }
  
    public function restore(string $id)
    {
         try {
            $decryptedId = Crypt::decryptString($id);
            $module = Module::onlyTrashed()->find($decryptedId);
 
            if (!$module) {
                return response()->json([
                    'message' => 'Módulo no encontrado',
                ], 404);
            }

            /* reactivamos el recurso */
            $module->restore();
            //mensaje de exito 
            return response()->json([
                'message' => 'Módulo activado con exito',
            ], 200);


        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    public function forceDelete(string $id)
    {
         try {
            $decryptedId = Crypt::decryptString($id);
            $module = Module::onlyTrashed()->find($decryptedId);

            if (!$module) {
                return response()->json([
                    'message' => 'Módulo no encontrado',
                ], 404);
            }

            /* eliminamos definitivamente el recurso */
            $module->forceDelete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Módulo eliminado definitivamente con exito',
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
            'active' => Module::query(),
            'inactive' => Module::onlyTrashed(),
            'all' => Module::withTrashed(),
        };

        $units = ModelSearchController::getPaginatedResults($query, $request);
        return ListResource::collection($units);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Error al procesar la solicitud',
        ], 500);
    }
}

    public function import(UploadRequest $request)/* no esta en el request */
    {
        $response =$this->importService->import($request->file('file'), \App\Imports\ModulesImport::class);

        if($response->getData()->success){
            return $response;
        }else{
            return $response;
        }
    }

}
