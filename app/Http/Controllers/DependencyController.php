<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dependency;
use App\Http\Resources\Dependency\DependencyResource;
use App\Http\Resources\SelectorResource;
use App\Http\Resources\ListResource;
use App\Http\Requests\DependencyRequest\StoreDependencyRequest;
use App\Http\Requests\DependencyRequest\UpdateDependencyRequest;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ModelSearchController;

class DependencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SelectorResource::collection(Dependency::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDependencyRequest $request)
    {
        try {
            Dependency::create($request->validated());

            return response()->json([
                'message' => 'Dependencia creada con éxito',
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'La Dependencia no se pudo crear',
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

            $dependency = Dependency::withTrashed()->find($decryptedId);

            if (!$dependency) {
                return response()->json([
                    'message' => 'Dependencia no encontrada',
                ], 404);
            }

            return new DependencyResource($dependency);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Dependencia no encontrada',
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDependencyRequest $request, string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $dependency = Dependency::find($decryptedId);

            if (!$dependency) {
                return response()->json(['message' => 'Dependencia no encontrada'], 404);
            }

            $validated = $request->validated();

            if (empty($validated)) {
                return response()->json([
                    'message' => 'No se enviaron datos para actualizar.',
                ], 400);
            }

            $dependency->update($validated);

            return response()->json([
                'message' => 'Dependencia actualizada con éxito.',
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
            $dependency = Dependency::find($decryptedId);

            if (!$dependency) {
                return response()->json(['message' => 'Dependencia no encontrada'], 404);
            }

            $dependency->delete();

            return response()->json([
                'message' => 'Dependencia desactivada con éxito',
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
            $dependency = Dependency::onlyTrashed()->find($decryptedId);

            if (!$dependency) {
                return response()->json(['message' => 'Dependencia no encontrada'], 404);
            }

            $dependency->restore();

            return response()->json([
                'message' => 'Dependencia activada con éxito',
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
            $dependency = Dependency::onlyTrashed()->find($decryptedId);

            if (!$dependency) {
                return response()->json(['message' => 'Dependencia no encontrada'], 404);
            }

            $dependency->forceDelete();

            return response()->json([
                'message' => 'Dependencia eliminada definitivamente con éxito',
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
            'active' => Dependency::query(),
            'inactive' => Dependency::onlyTrashed(),
            'all' => Dependency::withTrashed(),
        };

        $dependencies = ModelSearchController::getPaginatedResults($query, $request);
        return ListResource::collection($dependencies);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Error al procesar la solicitud',
        ], 500);
    }
}
}