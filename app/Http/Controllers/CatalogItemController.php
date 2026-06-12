<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogItemRequest\StoreCatalogItemRequest;
use App\Http\Requests\CatalogItemRequest\UpdateCatalogItemRequest;

use App\Http\Resources\CatalogItemResource\CatalogItemResource;
use App\Http\Resources\CatalogItemResource\CatalogItemListResource;

use App\Models\CatalogItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Crypt;

class CatalogItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CatalogItemListResource::collection(
            CatalogItem::with(['catalog' => function ($query) {
                $query->withTrashed()->select('id', 'name');
            }])->paginate(10)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCatalogItemRequest $request)
    {
        try {
            CatalogItem::create($request->validated());

            return response()->json([
                'message' => 'Ítem creado exitosamente'
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al crear el ítem del catálogo'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);

            $item = CatalogItem::withTrashed()
                ->with(['catalog' => function ($query) {
                    $query->withTrashed()->select('id', 'name');
                }])
                ->findOrFail($decryptedId);

            return new CatalogItemResource($item);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Ítem no encontrado'], 404);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error al obtener el ítem'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCatalogItemRequest $request, string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $item = CatalogItem::findOrFail($decryptedId);

            $validated = $request->validated();

            if (empty($validated)) {
                return response()->json([
                    'message' => 'No se enviaron datos para actualizar.',
                ], 400);
            }

            $item->update($validated);

            return response()->json(['message' => 'Ítem actualizado exitosamente'], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Ítem no encontrado'], 404);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error al actualizar el ítem'], 500);
        }
    }

    /**
     * Remove the specified from storage (soft delete).
     */
    public function destroy(string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $item = CatalogItem::findOrFail($decryptedId);

            $item->delete();

            return response()->json(['message' => 'Ítem eliminado exitosamente'], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Ítem no encontrado'], 404);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error al eliminar el ítem'], 500);
        }
    }

    public function restore(string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $catalogItem = CatalogItem::onlyTrashed()->find($decryptedId);

            if (!$catalogItem) {
                return response()->json([
                    'message' => 'Ítem no encontrado en los eliminados',
                ], 404);
            }

            $catalogItem->restore();

            return response()->json([
                'message' => 'Ítem restaurado exitosamente',
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
            $catalogItem = CatalogItem::onlyTrashed()->find($decryptedId);

            if (!$catalogItem) {
                return response()->json([
                    'message' => 'Ítem no encontrado en los eliminados',
                ], 404);
            }

            $catalogItem->forceDelete();

            return response()->json([
                'message' => 'Ítem eliminado definitivamente con éxito',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    public function content($content)
    {
        try {
            switch ($content) {

                case 'active':
                    return CatalogItemListResource::collection(
                        CatalogItem::with(['catalog:id,name'])->paginate(10)
                    );

                case 'inactive':
                    return CatalogItemListResource::collection(
                        CatalogItem::onlyTrashed()
                            ->with(['catalog:id,name'])
                            ->paginate(10)
                    );

                case 'all':
                    return CatalogItemListResource::collection(
                        CatalogItem::withTrashed()
                            ->with(['catalog:id,name'])
                            ->paginate(10)
                    );

                default:
                    return response()->json([
                        'message' => 'Contenido no válido. Use "active", "inactive" o "all".',
                    ], 400);
            }

        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error al procesar la solicitud'], 400);
        }
    }
}
