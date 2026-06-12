<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Occupation;
use App\Http\Resources\Occupation\OccupationResource;
use App\Http\Resources\ListResource;
use App\Http\Requests\OccupationRequest\StoreOccupationRequest;
use App\Http\Requests\OccupationRequest\UpdateOccupationRequest;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ModelSearchController;


class OccupationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ListResource::collection(Occupation::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOccupationRequest $request)
    {
        try {
            Occupation::create($request->validated());

            return response()->json([
                'message' => 'Ocupación creada con éxito',
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'La ocupación no se pudo crear',
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

            $occupation = Occupation::withTrashed()->find($decryptedId);

            if (!$occupation) {
                return response()->json([
                    'message' => 'Ocupación no encontrada',
                ], 404);
            }

            return new OccupationResource($occupation);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Ocupación no encontrada',
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOccupationRequest $request, string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $occupation = Occupation::find($decryptedId);

            if (!$occupation) {
                return response()->json(['message' => 'Ocupación no encontrada'], 404);
            }

            $validated = $request->validated();

            if (empty($validated)) {
                return response()->json([
                    'message' => 'No se enviaron datos para actualizar.',
                ], 400);
            }

            $occupation->update($validated);

            return response()->json([
                'message' => 'Ocupación actualizada con éxito.',
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
            $occupation = Occupation::find($decryptedId);

            if (!$occupation) {
                return response()->json(['message' => 'Ocupación no encontrada'], 404);
            }

            $occupation->delete();

            return response()->json([
                'message' => 'Ocupación desactivada con éxito',
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
            $occupation = Occupation::onlyTrashed()->find($decryptedId);

            if (!$occupation) {
                return response()->json(['message' => 'Ocupación no encontrada'], 404);
            }

            $occupation->restore();

            return response()->json([
                'message' => 'Ocupación activada con éxito',
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
            $occupation = Occupation::onlyTrashed()->find($decryptedId);

            if (!$occupation) {
                return response()->json(['message' => 'Ocupación no encontrada'], 404);
            }

            $occupation->forceDelete();

            return response()->json([
                'message' => 'Ocupación eliminada definitivamente con éxito',
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
                'message' => 'Contenido no válido. Use "active", "inactive" o "all".',
            ], 400);
        }

        $query = match($content) {
            'active' => Occupation::query(),
            'inactive' => Occupation::onlyTrashed(),
            'all' => Occupation::withTrashed(),
        };

        $occupations = ModelSearchController::getPaginatedResults($query, $request);
        return ListResource::collection($occupations);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Error al procesar la solicitud',
        ], 500);
    }
}
}
