<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\CensusModule;
use App\Models\CensusSection;   
use App\Models\Section;
use App\Models\Index;
use App\Http\Resources\SelectorResource;
use App\Http\Requests\CensusSectionRequest\StoreCensusSectionRequest;
use App\Http\Requests\CensusSectionRequest\UpdateCensusSectionRequest;

class CensusSectionController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $censusModule = CensusModule::with('index:id,name')->find($decryptedId);/* aqui tenemos que traernos el indice del censusmodule */

            if (!$censusModule) {
                return response()->json([
                    'message' => 'Módulo del censo no encontrado.',
                ], 404);
            }

            $prefix = explode('.', $censusModule->index->name)[0];/* aqui nos traemos el valor de el indice del modulo*/
            $censusModuleShow = [
                'id'   => $id,
                'name' => $censusModule->module->name,
            ];

            /* las secciones paginadas */
            $sections = Section::select('id', 'name')
                ->paginate(10)
                ->through(function ($item) {
                    return [
                        'id'   => Crypt::encryptString($item->id),
                        'name' => $item->name,
                    ];
            });

            /* traemos los indices que inician con el indice del modulo */
            $indexes = SelectorResource::collection(
                Index::select('id', 'name')
                    ->whereRaw("name ~ '^{$prefix}'")
                    ->where('name', '!=', $prefix)
                    ->orderByRaw("string_to_array(name, '.')::int[]")
                    ->get()
            );

            $selectedSections = CensusSection::where('census_module_id', $decryptedId)
            ->whereHas('section')
            ->whereHas('index')
            ->with(['section', 'index'])
            ->get()
            ->map(function ($censusSection) {
                return [
                    'id'      => Crypt::encryptString($censusSection->id),
                    'section' => [
                        'id'   => Crypt::encryptString($censusSection->section->id),
                        'name' => $censusSection->section->name,
                    ],
                    'index'   => [
                        'id'   => Crypt::encryptString($censusSection->index->id),
                        'name' => $censusSection->index->name,
                    ],
                ];
            });

            return response()->json([
                'censusModule'     => $censusModuleShow,
                'sections'         => $sections,
                'indexes'          => $indexes,
                'selectedSections' => $selectedSections,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al obtener el módulo del censo: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCensusSectionRequest $request)
    {
        try {
            $validated = $request->validated();

            $created = collect($validated['items'])->map(function ($item) use ($validated) {
                return CensusSection::create([
                    'census_module_id' => $validated['census_module_id'],
                    'index_id'         => $item['index_id'],
                    'section_id'       => $item['section_id'],
                ]);
            });

            return response()->json([
                'message' => 'Secciones del censo creadas exitosamente.',
                'data'    => $created,
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al crear las secciones del censo: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCensusSectionRequest $request, string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $censusModule = CensusModule::find($decryptedId);

            if (!$censusModule) {
                return response()->json([
                    'message' => 'Módulo del censo no encontrado.',
                ], 404);
            }

            $validated = $request->validated();

            $incomingIds = collect($validated['items'])
                ->pluck('census_section_id')
                ->filter()
                ->values();

            CensusSection::where('census_module_id', $censusModule->id)
                ->whereNotIn('id', $incomingIds)
                ->delete();

            foreach ($validated['items'] as $item) {
                if (!empty($item['census_section_id'])) {
                    CensusSection::where('id', $item['census_section_id'])
                        ->update([
                            'section_id' => $item['section_id'],
                            'index_id'   => $item['index_id'],
                        ]);
                } else {
                    CensusSection::create([
                        'census_module_id' => $censusModule->id,
                        'section_id'       => $item['section_id'],
                        'index_id'         => $item['index_id'],
                    ]);
                }
            }

            return response()->json([
                'message' => 'Secciones actualizadas correctamente.',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al actualizar las secciones: ' . $e->getMessage(),
            ], 400);
        }
    }

}
