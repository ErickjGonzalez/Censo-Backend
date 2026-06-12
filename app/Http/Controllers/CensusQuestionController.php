<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\CensusSection;
use App\Models\CensusQuestion;
use App\Models\Question;
use App\Models\Index;
use App\Http\Resources\SelectorResource;
use App\Http\Requests\CensusQuestionRequest\StoreCensusQuestionRequest;
use App\Http\Requests\CensusQuestionRequest\UpdateCensusQuestionRequest;

class CensusQuestionController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $censusSection = CensusSection::with(['index:id,name', 'section:id,name'])->find($decryptedId);

            if (!$censusSection) {
                return response()->json([
                    'message' => 'Sección del censo no encontrada.',
                ], 404);
            }

            $prefix = explode('.', $censusSection->index->name)[0];

            $censusSectionShow = [
                'id'    => $id,
                'index' => $censusSection->index->name,
                'name'  => $censusSection->section->name,
            ];

            $questions = Question::select('id', 'name')
                ->paginate(10)
                ->through(function ($item) {
                    return [
                        'id'   => Crypt::encryptString($item->id),
                        'name' => $item->name,
                    ];
                });

            $indexes = SelectorResource::collection(
                Index::select('id', 'name')
                    ->whereRaw("name ~ '^{$prefix}'")
                    ->where('name', '!=', $prefix)
                    ->orderByRaw("string_to_array(name, '.')::int[]")
                    ->get()
            );

            $selectedQuestions = CensusQuestion::where('census_section_id', $decryptedId)
            ->whereHas('question')
            ->whereHas('index')
            ->with(['question:id,name', 'index:id,name'])
            ->get()
            ->map(function ($censusQuestion) {
                return [
                    'id'       => Crypt::encryptString($censusQuestion->id),
                    'question' => [
                        'id'   => Crypt::encryptString($censusQuestion->question->id),
                        'name' => $censusQuestion->question->name,
                    ],
                    'index'    => [
                        'id'   => Crypt::encryptString($censusQuestion->index->id),
                        'name' => $censusQuestion->index->name,
                    ],
                ];
            });

            return response()->json([
                'censusSection'     => $censusSectionShow,
                'questions'         => $questions,
                'indexes'           => $indexes,
                'selectedQuestions' => $selectedQuestions,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al obtener la sección del censo: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCensusQuestionRequest $request)
    {
        try {
            $validated = $request->validated();

            $created = collect($validated['items'])->map(function ($item) use ($validated) {
                return CensusQuestion::create([
                    'census_section_id' => $validated['census_section_id'],
                    'index_id'          => $item['index_id'],
                    'question_id'       => $item['question_id'],
                ]);
            });

            return response()->json([
                'message' => 'Preguntas del censo creadas exitosamente.',
                'data'    => $created->map(function ($censusQuestion) {
                    return [
                        'id'                => Crypt::encryptString($censusQuestion->id),
                        'census_section_id' => Crypt::encryptString($censusQuestion->census_section_id),
                        'question_id'       => Crypt::encryptString($censusQuestion->question_id),
                        'index_id'          => Crypt::encryptString($censusQuestion->index_id),
                    ];
                }),
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al crear las preguntas del censo: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function update(UpdateCensusQuestionRequest $request, string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $censusSection = CensusSection::find($decryptedId);

            if (!$censusSection) {
                return response()->json([
                    'message' => 'Sección del censo no encontrada.',
                ], 404);
            }

            $validated = $request->validated();

            $incomingIds = collect($validated['items'])
                ->pluck('census_question_id')
                ->filter()
                ->values();

            CensusQuestion::where('census_section_id', $censusSection->id)
                ->whereNotIn('id', $incomingIds)
                ->delete();

            foreach ($validated['items'] as $item) {
                if (!empty($item['census_question_id'])) {
                    CensusQuestion::where('id', $item['census_question_id'])
                        ->update([
                            'question_id' => $item['question_id'],
                            'index_id'    => $item['index_id'],
                        ]);
                } else {
                    CensusQuestion::create([
                        'census_section_id' => $censusSection->id,
                        'question_id'       => $item['question_id'],
                        'index_id'          => $item['index_id'],
                    ]);
                }
            }

            return response()->json([
                'message' => 'Preguntas actualizadas correctamente.',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al actualizar las preguntas: ' . $e->getMessage(),
            ], 400);
        }
    }
}
