<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\ListResource;
use App\Http\Resources\SelectorResource;
use App\Http\Requests\CategoryRequest\StoreCategoryRequest;
use App\Http\Requests\CategoryRequest\UpdateCategoryRequest;
use Illuminate\Support\Facades\Crypt;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SelectorResource::collection(Category::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)//ya esta validado con el StoreCategoryRequest
    {
        try {
            Category::create($request->validated());
            return response()->json([
                'message' => 'Categoría creada con exito',
            ], 201);
        } catch (\Throwable $e) {
           return response()->json([
                'message' => 'La categoría no se pudo crear',
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
            $category = Category::withTrashed()->find($decryptedId);

            if (!$category) {
                return response()->json([
                    'message' => 'Categoría no encontrada',
                ], 404);
            }

            return new CategoryResource($category);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Categoría no encontrada',
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $id)//actualiza con las mismas reglas que la creacion y solo recursos activos
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $category = Category::find($decryptedId);

            if (!$category) {
                return response()->json([
                    'message' => 'Categoría no encontrada',
                ], 404);
            }

            if (empty($request->validated())) {
                return response()->json([
                    'message' => 'No se enviaron datos para actualizar.',
                ], 400);
            }

            $category->update($request->validated());

            return response()->json([
                'message' => 'Categoría actualizada con éxito.',
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
            
            $category = Category::find($decryptedId);

            if (!$category) {
                return response()->json([
                    'message' => 'Categoría no encontrada',
                ], 404);
            }

            /* desactivamor el recurso */
            $category->delete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Categoría desactivada con exito',
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
            $category = Category::onlyTrashed()->find($decryptedId);
 
            if (!$category) {
                return response()->json([
                    'message' => 'Categoría no encontrada',
                ], 404);
            }

            /* reactivamos el recurso */
            $category->restore();
            //mensaje de exito 
            return response()->json([
                'message' => 'Categoría activada con exito',
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
            $category = Category::onlyTrashed()->find($decryptedId);

            if (!$category) {
                return response()->json([
                    'message' => 'Categoría no encontrada',
                ], 404);
            }

            /* eliminamos definitivamente el recurso */
            $category->forceDelete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Categoría eliminada definitivamente con exito',
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
            'active' => Category::query(),
            'inactive' => Category::onlyTrashed(),
            'all' => Category::withTrashed(),
        };

        $categories = ModelSearchController::getPaginatedResults($query, $request);
        return ListResource::collection($categories);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Error al procesar la solicitud',
        ], 500);
    }
}
}
