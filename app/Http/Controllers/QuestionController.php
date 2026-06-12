<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Option;
use App\Models\IndexForCensoQuestion;
use App\Http\Resources\Question\QuestionResource;
use App\Http\Resources\ListResource;
use App\Http\Resources\SelectorResource;
use App\Http\Requests\QuestionRequest\StoreQuestionRequest;
use App\Http\Requests\QuestionRequest\UpdateQuestionRequest;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\ImportRequest\UploadRequest;
use App\Services\ImportServices\ImportService;
use App\Http\Controllers\ModelSearchController;


class QuestionController extends Controller
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
    public function index()/* listado para un selector */
    {
        return SelectorResource::collection(Question::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuestionRequest $request)/* revisar que onda porque esta raro */
    {
        try {
            Question::create($request->validated());
            return response()->json([
                'message' => 'Pregunta creada con exito',
            ], 201);
        } catch (\Throwable $e) {
           return response()->json([
                'message' => 'La pregunta no se pudo crear',
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
            $question = Question::withTrashed()->find($decryptedId);

            if (!$question) {
                return response()->json([
                    'message' => 'Pregunta no encontrada',
                ], 404);
            }

            return new QuestionResource($question);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Pregunta no encontrada',
            ], 400);
        }
    }

    public function getComponent($id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $question_component = Question::select('question_structure')->find($decryptedId);

            if (!$question_component) {
                return response()->json([
                    'message' => 'Pregunta no encontrada',
                ], 404);
            }

            return response()->json([
                'question_structure' => $question_component->question_structure,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuestionRequest $request, string $id)//actualiza con las mismas reglas que la creacion y solo recursos activos
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $question = Question::find($decryptedId);

            if (!$question) {
                return response()->json([
                    'message' => 'Pregunta no encontrada',
                ], 404);
            }

            if (empty($request->validated())) {
                return response()->json([
                    'message' => 'No se enviaron datos para actualizar.',
                ], 400);
            }

            $question->update($request->validated());

            return response()->json([
                'message' => 'Pregunta actualizada con éxito.',
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
            $question = Question::find($decryptedId);

            if(!$question) {
                return response()->json([
                    'message' => 'Pregunta no encontrada',
                ], 404);
            }

            /* desactivamor el recurso */
            $question->delete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Pregunta desactivada con exito',
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
             $question = Question::onlyTrashed()->find($decryptedId);
 
            if (!$question) {
                return response()->json([
                    'message' => 'Pregunta no encontrada',
                ], 404);
            }

            /* reactivamos el recurso */
            $question->restore();
            //mensaje de exito 
            return response()->json([
                'message' => 'Pregunta activada con exito',
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
            $question = Question::onlyTrashed()->find($decryptedId);

           if (!$question) {
                return response()->json([
                    'message' => 'Pregunta no encontrada',
                ], 404);
            }

            /* eliminamos definitivamente el recurso */
            $question->forceDelete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Pregunta eliminada definitivamente con exito',
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
            'active' => Question::query(),
            'inactive' => Question::onlyTrashed(),
            'all' => Question::withTrashed(),
        };

        $questions = ModelSearchController::getPaginatedResults($query, $request);
        return ListResource::collection($questions);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Error al procesar la solicitud',
        ], 500);
    }
}

    public function import(UploadRequest $request)
    {
        $response =$this->importService->import($request->file('file'), \App\Imports\QuestionsImport::class);

        if($response->getData()->success){
            return $response;
        }else{
            return $response;
        }
    }

    /**
 * GET /questions/{id}/getForm
 * Retorna los datos de la pregunta para el Builder
 */
public function getForm(string $id)
{
    try {
        $decryptedId = Crypt::decryptString($id);
        $question = Question::find($decryptedId);

        if (!$question) {
            return response()->json([
                'message' => 'Pregunta no encontrada',
            ], 404);
        }

        // Decodificar question_structure si llega como string
        $jsonSchema = null;
        if ($question->question_structure) {
            $jsonSchema = is_string($question->question_structure)
                ? json_decode($question->question_structure, true)
                : $question->question_structure;
        }

        return response()->json([
            'json_schema'    => $jsonSchema,
            'name'           => $question->name,
            'instructions'   => $question->instructions,
            'commentaries'   => $question->commentaries,
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Error al procesar la solicitud',
        ], 400);
    }
}

/**
 * POST /questions/{id}/saveForm
 * Guarda el schema del formulario en question_structure
 */
public function saveForm(Request $request, string $id)
{
    try {
        $decryptedId = Crypt::decryptString($id);
        $question = Question::find($decryptedId);

        if (!$question) {
            return response()->json(['message' => 'Pregunta no encontrada'], 404);
        }

        $jsonSchema = $request->input('json_schema');

        if (empty($jsonSchema)) {
            return response()->json(['message' => 'json_schema está vacío o no fue enviado'], 422);
        }

        // ⚠️ Remover display si viene incluido
        unset($jsonSchema['display']);

        $question->question_structure = json_encode($jsonSchema);
        $question->save();

        return response()->json(['message' => 'Estructura guardada correctamente'], 200);

    } catch (\Throwable $e) {
        return response()->json(['message' => 'Error al procesar la solicitud'], 400);
    }

}





}