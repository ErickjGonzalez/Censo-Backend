<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Index;
use App\Http\Resources\Index\IndexResource;
use App\Http\Resources\ListResource;
use App\Http\Resources\SelectorResource;
use App\Http\Requests\IndexRequest\StoreIndexRequest;
use App\Http\Requests\IndexRequest\UpdateIndexRequest;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\IndexsImport;
use App\Http\Requests\ImportRequest\UploadRequest;
use App\Services\ImportServices\ImportService;
use App\Http\Controllers\ModelSearchController;

class IndexController extends Controller
{
    protected ImportService $importService;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    public function index()
    {
        return SelectorResource::collection(Index::all());
    }

    public function store(StoreIndexRequest $request)
    {
        try {
            Index::create($request->validated());
            return response()->json(['message' => 'Índice creado con éxito'], 201);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'El índice no se pudo crear'], 400);
        }
    }

    public function show(string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $index = Index::withTrashed()->find($decryptedId);
            if (!$index) {
                return response()->json(['message' => 'Índice no encontrado'], 404);
            }
            return new IndexResource($index);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Índice no encontrado'], 400);
        }
    }

    public function update(UpdateIndexRequest $request, string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $index = Index::find($decryptedId);
            if (!$index) {
                return response()->json(['message' => 'Índice no encontrado'], 404);
            }
            $validated = $request->validated();
            if (empty($validated)) {
                return response()->json(['message' => 'No se enviaron datos para actualizar.'], 400);
            }
            $index->update($validated);
            return response()->json(['message' => 'Índice actualizado con éxito.'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error al procesar la solicitud'], 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $index = Index::find($decryptedId);
            if (!$index) {
                return response()->json(['message' => 'Índice no encontrado'], 404);
            }
            $index->delete();
            return response()->json(['message' => 'Índice desactivado con éxito'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error al procesar la solicitud'], 400);
        }
    }

    public function restore(string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $index = Index::onlyTrashed()->find($decryptedId);
            if (!$index) {
                return response()->json(['message' => 'Índice no encontrado'], 404);
            }
            $index->restore();
            return response()->json(['message' => 'Índice activado con éxito'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error al procesar la solicitud'], 400);
        }
    }

    public function forceDelete(string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $index = Index::onlyTrashed()->find($decryptedId);
            if (!$index) {
                return response()->json(['message' => 'Índice no encontrado'], 404);
            }
            $index->forceDelete();
            return response()->json(['message' => 'Índice eliminado definitivamente con éxito'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error al procesar la solicitud'], 400);
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
            'active' => Index::query(),
            'inactive' => Index::onlyTrashed(),
            'all' => Index::withTrashed(),
        };

        $indices = ModelSearchController::getPaginatedResults($query, $request);
        return ListResource::collection($indices);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Error al procesar la solicitud',
        ], 500);
    }
}

    public function import(UploadRequest $request)
    {
        $response = $this->importService->import($request->file('file'), \App\Imports\IndexsImport::class);
        if ($response->getData()->success) {
            return $response;
        } else {
            return $response;
        }
    }
}