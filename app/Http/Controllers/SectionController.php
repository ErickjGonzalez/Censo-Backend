<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Http\Resources\Section\SectionResource;
use App\Http\Resources\ListResource;
use App\Http\Resources\SelectorResource;
use App\Http\Requests\SectionRequest\StoreSectionRequest;
use App\Http\Requests\SectionRequest\UpdateSectionRequest;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\ImportRequest\UploadRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SectionsImport;
use App\Services\ImportServices\ImportService;
use App\Http\Controllers\ModelSearchController;


class SectionController extends Controller
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
        return SelectorResource::collection(Section::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSectionRequest $request)//ya esta validado con el StoreSectionRequest
    {
        try {
            Section::create($request->validated());
            return response()->json([
                'message' => 'Sección creada con exito',
            ], 201);
        } catch (\Throwable $e) {
           return response()->json([
                'message' => 'La sección no se pudo crear',
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
            $section = Section::withTrashed()->find($decryptedId);

            if (!$section) {
                return response()->json([
                    'message' => 'Sección no encontrada',
                ], 404);
            }

            return new SectionResource($section);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Sección no encontrada',
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSectionRequest $request, string $id)//actualiza con las mismas reglas que la creacion y solo recursos activos
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $section = Section::find($decryptedId);

            if (!$section) {
                return response()->json([
                    'message' => 'Sección no encontrada',
                ], 404);
            }

            if (empty($request->validated())) {
                return response()->json([
                    'message' => 'No se enviaron datos para actualizar.',
                ], 400);
            }

            $section->update($request->validated());
            
            return response()->json([
                'message' => 'Sección actualizada con exito',
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
            
            $section = Section::find($decryptedId);

            if (!$section) {
                return response()->json([
                    'message' => 'Sección no encontrada',
                ], 404);
            }

            /* desactivamor el recurso */
            $section->delete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Sección desactivada con exito',
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
            $section = Section::onlyTrashed()->find($decryptedId);
 
            if (!$section) {
                return response()->json([
                    'message' => 'Sección no encontrada',
                ], 404);
            }

            /* reactivamos el recurso */
            $section->restore();
            //mensaje de exito 
            return response()->json([
                'message' => 'Sección activada con exito',
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
            $section = Section::onlyTrashed()->find($decryptedId);

            if (!$section) {
                return response()->json([
                    'message' => 'Sección no encontrada',
                ], 404);
            }

            /* eliminamos definitivamente el recurso */
            $section->forceDelete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Sección eliminada definitivamente con exito',
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
                'message' => 'Contenido no válido. Use "active", "inactive" o "all".',
            ], 400);
        }

        $query = match($content) {
            'active' => Section::query(),
            'inactive' => Section::onlyTrashed(),
            'all' => Section::withTrashed(),
        };

        $sections = ModelSearchController::getPaginatedResults($query, $request);
        return ListResource::collection($sections);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Error al procesar la solicitud',
        ], 500);
    }
}

    public function import(UploadRequest $request)/* no esta en el request */
    {
       /*  dd($request->all(), $request->file('file'));
        return response()->json('hola'); */
        $response =$this->importService->import($request->file('file'), \App\Imports\SectionsImport::class);

        if($response->getData()->success){
            return $response;
        }else{
            return $response;
        }
    }
}
