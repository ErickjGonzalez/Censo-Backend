<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ModelSearchController extends Controller
{
    // Lista de modelos permitidos (debes completarla con tus modelos)
    private $allowedModels = [
        'units' => Unit::class,
        'catalogs' => Catalog::class,
        'censos' => Censo::class,
        'options' => Option::class,
        'areas' => Area::class,
        'categories' => Category::class,
        'institutions' => Institution::class,
        'occupations' => Occupation::class,
        'sections' => Section::class,
        'permissions' => Permission::class,
        'questions' => Question::class,
        'roles' => Role::class,

    ];

    /**
     * Aplica búsqueda y ordenamiento a un query builder.
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public static function applySearchAndSort(Builder $query, Request $request): Builder
    {
        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $sortField = match($request->sort_by) {
            'id', 'name', 'updated_at' => $request->sort_by,
            default => 'created_at'
        };
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortField, $direction);
    }

    /**
     * Método de búsqueda genérico.
     */
    public function search(Request $request, $model)
    {
        if (!isset($this->allowedModels[$model])) {
            return response()->json(['message' => 'Modelo no encontrado'], 404);
        }

        try {
            $modelClass = $this->allowedModels[$model];
            $query = $modelClass::query();

            if ($request->filled('filter') && method_exists($modelClass, 'withTrashed')) {
                switch ($request->filter) {
                    case 'active':
                        $query->whereNull('deleted_at');
                        break;
                    case 'inactive':
                        $query->onlyTrashed();
                        break;
                    case 'all':
                        $query->withTrashed();
                        break;
                }
            } elseif (method_exists($modelClass, 'withTrashed')) {
                $query->whereNull('deleted_at');
            }

            $query = self::applySearchAndSort($query, $request);
            return response()->json($query->paginate(10));
            
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error al procesar la solicitud'], 500);
        }
    }

    public static function getPaginatedResults(Builder $query, Request $request, int $perPage = 10)
{
    $query = self::applySearchAndSort($query, $request);
    return $query->paginate($perPage);
}
}