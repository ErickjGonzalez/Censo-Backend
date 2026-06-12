<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Module\ModuleResource;
use App\Http\Resources\SelectorResource;
use Illuminate\Support\Facades\Crypt;
use App\Models\Census;
use App\Models\Index;
use App\Models\Module;
use App\Models\CensusModule;
use App\Http\Requests\CensusModuleRequest\StoreCensusModuleRequest;
use App\Http\Requests\CensusModuleRequest\UpdateCensusModuleRequest;

class CensusModuleController extends Controller
{
/* 
    show:
    1.-aqui mandamos los indices numericos y los modulos y los modulos seleccionados, para que el frontend pueda mostrar los indices y modulos disponibles y cuales estan seleccionados
    
    Store:
    1.-aqui recibimos el id del censo y un array de modulos seleccionados, y guardamos esa relacion en la tabla census_module, eliminando las relaciones anteriores y
    creando las nuevas relaciones con los modulos seleccionados
    
    Update:

    change:aqui cambiamos rl valor de el indice o de un modulo pero en el mismo registro sin crear uno nuevo

    find:buscmos un registro para poder mostrarlo ento

*/
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $census = Census::withTrashed()->find($decryptedId);

            if (!$census) {
                return response()->json([
                    'message' => 'Censo no encontrado',
                ], 404);
            }

            $censusShow =[
                'id' => $id,
                'name' => $census->name,
            ];

            $modules = SelectorResource::collection(Module::all());

            $indexes = SelectorResource::collection(Index::whereRaw("name ~ '^[0-9]+$'")->get());

            /* aqui traemos todos los modulos seleccionados y sus indices */
            $selectedModules = CensusModule::where('census_id', $decryptedId)
            ->whereHas('module')
            ->whereHas('index')
            ->with(['module', 'index'])
            ->get()
            ->map(function ($censusModule) {
                return [
                    'id'     => Crypt::encryptString($censusModule->id),
                    'module' => [
                        'id'   => Crypt::encryptString($censusModule->module->id),
                        'name' => $censusModule->module->name,
                    ],
                    'index'  => [
                        'id'   => Crypt::encryptString($censusModule->index->id),
                        'name' => $censusModule->index->name,
                    ],
                ];
            });

            return response()->json([
                'census'   => $censusShow,
                'indexes'  => $indexes,
                'modules' => $modules,
                ...($selectedModules ? ['selectedModules' => $selectedModules] : []),
            ]);

        } catch (\Throwable $e) {
           return response()->json([
                'message' => 'Error al obtener el censo'.$e->getMessage(),
            ], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCensusModuleRequest $request)
    {
        try {
            $validated = $request->validated();

            $created = collect($validated['items'])->map(function ($item) use ($validated) {
                return CensusModule::create([
                    'census_id' => $validated['census_id'],
                    'module_id' => $item['module_id'],
                    'index_id'  => $item['index_id'],
                ]);
            });

            return response()->json([
                'message' => 'Módulos asignados correctamente.',
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al asignar los módulos.',
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCensusModuleRequest $request, string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $census = Census::find($decryptedId);

            if (!$census) {
                return response()->json([
                    'message' => 'Censo no encontrado.',
                ], 404);
            }

            $validated = $request->validated();

            // IDs que vienen en el request con census_module_id (existentes)
            $incomingIds = collect($validated['items'])
                ->pluck('census_module_id')
                ->filter()
                ->values();

            // Eliminar los que no vinieron en el request
            CensusModule::where('census_id', $census->id)
                ->whereNotIn('id', $incomingIds)
                ->delete();

            foreach ($validated['items'] as $item) {
                if (!empty($item['census_module_id'])) {
                    // Actualizar existente
                    CensusModule::where('id', $item['census_module_id'])
                        ->update([
                            'module_id' => $item['module_id'],
                            'index_id'  => $item['index_id'],
                        ]);
                } else {
                    // Crear nuevo
                    CensusModule::create([
                        'census_id' => $census->id,
                        'module_id' => $item['module_id'],
                        'index_id'  => $item['index_id'],
                    ]);
                }
            }

            return response()->json([
                'message' => 'Módulos actualizados correctamente.',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al actualizar los módulos.',
            ], 400);
        }
    }

    public function find()/* para despues */
    {
        //
    }

    public function change(string $id)/* para despues */
    {
        //
    }

}
