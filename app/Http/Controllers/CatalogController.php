<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Catalog;
use App\Http\Resources\CatalogResource\CatalogResource;
use App\Http\Resources\CatalogResource\CatalogListResource;
use App\Http\Requests\CatalogRequest\StoreCatalogRequest;
use App\Http\Requests\CatalogRequest\UpdateCatalogRequest;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ModelSearchController;

class CatalogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CatalogListResource::collection(
            Catalog::with(['module' => function ($query) {
                $query->withTrashed()->select('id', 'name');
            }])->paginate(10)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCatalogRequest $request)
    {
        try {
            Catalog::create($request->validated());

            return response()->json([
                'message' => 'Catálogo creado con éxito',
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'El catálogo no se pudo crear'.$e->getMessage(),
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

        $catalog = Catalog::withTrashed()
            ->with(['module' => function ($query) {
                $query->withTrashed()->select('id', 'name', 'deleted_at');
            }])
            ->find($decryptedId);

        if (!$catalog) {
            return response()->json([
                'message' => 'Catálogo no encontrado',
            ], 404);
        }

        return new CatalogResource($catalog);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Catálogo no encontrado',
        ], 400);
    }
}

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCatalogRequest $request, string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $catalog = Catalog::find($decryptedId);

            if (!$catalog) {
                return response()->json(['message' => 'Catálogo no encontrado'], 404);
            }

            $validated = $request->validated();

            if (empty($validated)) {
                return response()->json([
                    'message' => 'No se enviaron datos para actualizar.',
                ], 400);
            }

            $catalog->update($validated);

            return response()->json([
                'message' => 'Catálogo actualizado con éxito.',
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
            $catalog = Catalog::find($decryptedId);

            if (!$catalog) {
                return response()->json(['message' => 'Catálogo no encontrado'], 404);
            }

            $catalog->delete();

            return response()->json([
                'message' => 'Catálogo desactivado con éxito',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    /**
     * Restore a soft-deleted resource.
     */
    public function restore(string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $catalog = Catalog::onlyTrashed()->find($decryptedId);

            if (!$catalog) {
                return response()->json(['message' => 'Catálogo no encontrado'], 404);
            }

            $catalog->restore();

            return response()->json([
                'message' => 'Catálogo activado con éxito',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    /**
     * Permanently delete the resource.
     */
    public function forceDelete(string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $catalog = Catalog::onlyTrashed()->find($decryptedId);

            if (!$catalog) {
                return response()->json(['message' => 'Catálogo no encontrado'], 404);
            }

            $catalog->forceDelete();

            return response()->json([
                'message' => 'Catálogo eliminado definitivamente con éxito',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    /**
     * List content depending on soft delete state.
     * - active  => solo activos
     * - inactive => solo eliminados (soft delete)
     * - all => todos
     */
     public function content(Request $request, $content)
{
    try {
        if (!in_array($content, ['active', 'inactive', 'all'])) {
            return response()->json([
                'message' => 'Contenido no válido. Use "active", "inactive" o "all".'
            ], 400);
        }

        $query = match($content) {
            'active' => Catalog::query(),
            'inactive' => Catalog::onlyTrashed(),
            'all' => Catalog::withTrashed(),
        };

        $query->with(['module:id,name']);

        $catalogs = ModelSearchController::getPaginatedResults($query, $request);

        return CatalogListResource::collection($catalogs);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Error al procesar la solicitud',
        ], 500);
    }
}
}
