<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PermissionRoleController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\CensusController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\OccupationController;
use App\Http\Controllers\CatalogItemController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuestionController;   
use App\Http\Controllers\DependencyController; 
use App\Http\Controllers\CategoryController; 
use App\Http\Controllers\ModelSearchController; 
use App\Http\Controllers\CensusModuleController;
use App\Http\Controllers\CensusSectionController;
use App\Http\Controllers\CensusAssignmentController;
use App\Http\Controllers\CensusQuestionController;
use App\Http\Controllers\AuthController;

/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */

//el grupo de rutas apiResource tiene las rutas index, show, store, update, destroy
//las rutas adicionales son para restaurar(restore(restaura un elemento borrado)), 
//eliminar definitivamente(force-delete)
// buscar por contenido(content)
//no mover
Route::get('/{model}/search', [ModelSearchController::class, 'search']);

// En routes/api.php
Route::apiResource('modules', ModuleController::class);
Route::post('modules/import', [ModuleController::class, 'import'])->name('modules.import');
Route::post('modules/{id}/restore', [ModuleController::class, 'restore'])->name('modules.restore');
Route::delete('modules/{id}/force-delete', [ModuleController::class, 'forceDelete'])->name('modules.force-delete');
Route::get('modules/{content}/content', [ModuleController::class, 'content'])->name('modules.content');

Route::apiResource('categories', CategoryController::class);
Route::post('categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
Route::delete('categories/{id}/force-delete', [CategoryController::class, 'forceDelete'])->name('categories.force-delete');
Route::get('categories/{content}/content', [CategoryController::class, 'content'])->name('categories.content');

Route::apiResource('roles', RoleController::class);
Route::post('roles/{id}/restore', [RoleController::class, 'restore'])->name('roles.restore');
Route::delete('roles/{id}/force-delete', [RoleController::class, 'forceDelete'])->name('roles.force-delete');
Route::get('roles/{content}/content', [RoleController::class, 'content'])->name('roles.content');

Route::get('roles/{id}/get-permissions', [RoleController::class, 'get_permissions_by_role'])->name('roles.get-permissions');

Route::apiResource('permission-role', PermissionRoleController::class)->only(['show', 'store', 'update']);

Route::apiResource('sections', SectionController::class);
Route::post('sections/import', [SectionController::class, 'import'])->name('sections.import');
Route::post('sections/{id}/restore', [SectionController::class, 'restore'])->name('sections.restore');
Route::delete('sections/{id}/force-delete', [SectionController::class, 'forceDelete'])->name('sections.force-delete');
Route::get('sections/{content}/content', [SectionController::class, 'content'])->name('sections.content');

Route::apiResource('censuses', CensusController::class);
Route::post('censuses/test', [CensusController::class, 'test'])->name('censuses.test');
Route::post('censuses/{id}/restore', [CensusController::class, 'restore'])->name('censuses.restore');
Route::delete('censuses/{id}/force-delete', [CensusController::class, 'forceDelete'])->name('censuses.force-delete');
Route::get('censuses/{content}/content', [CensusController::class, 'content'])->name('censuses.content');

Route::apiResource('catalogs', CatalogController::class);
Route::post('catalogs/{id}/restore', [CatalogController::class, 'restore'])->name('catalogs.restore');
Route::delete('catalogs/{id}/force-delete', [CatalogController::class, 'forceDelete'])->name('catalogs.force-delete');
Route::get('catalogs/{content}/content', [CatalogController::class, 'content'])->name('catalogs.content');
 
Route::apiResource('institutions', InstitutionController::class);
Route::get('institutions/{id}/get-map', [InstitutionController::class, 'getMap'])->name('institutions.get-map');
Route::post('institutions/{id}/restore', [InstitutionController::class, 'restore'])->name('institutions.restore');
Route::delete('institutions/{id}/force-delete', [InstitutionController::class, 'forceDelete'])->name('institutions.force-delete');
Route::get('institutions/{content}/content', [InstitutionController::class, 'content'])->name('institutions.content'); 

Route::apiResource('users',UserController::class)->except(['index']);
Route::patch('users/{id}/updateUser', [UserController::class, 'updateUser'])->name('users.updateUser');/* actualiza toda la información del usuario */
Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
Route::delete('users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');
Route::get('users/{content}/content', [UserController::class, 'content'])->name('users.content');

Route::apiResource('permissions', PermissionController::class);
Route::post('permissions/{id}/restore', [PermissionController::class, 'restore'])->name('permissions.restore');
Route::delete('permissions/{id}/force-delete', [PermissionController::class, 'forceDelete'])->name('permissions.force-delete');
Route::get('permissions/{content}/content', [PermissionController::class, 'content'])->name('permissions.content');

Route::apiResource('occupations', OccupationController::class);
Route::post('occupations/{id}/restore', [OccupationController::class, 'restore'])->name('occupations.restore');
Route::delete('occupations/{id}/force-delete', [OccupationController::class, 'forceDelete'])->name('occupations.force-delete');
Route::get('occupations/{content}/content', [OccupationController::class, 'content'])->name('occupations.content');
 
Route::apiResource('index', IndexController::class);
Route::post('index/import', [IndexController::class, 'import'])->name('index.import');
Route::post('index/{id}/restore', [IndexController::class, 'restore'])->name('index.restore');
Route::delete('index/{id}/force-delete', [IndexController::class, 'forceDelete'])->name('index.force-delete');
Route::get('index/{content}/content', [IndexController::class, 'content'])->name('index.content');

Route::apiResource('catalog-items', CatalogItemController::class);
Route::post('catalog-items/{id}/restore', [CatalogItemController::class, 'restore'])->name('catalog-items.restore');
Route::delete('catalog-items/{id}/force-delete', [CatalogItemController::class, 'forceDelete'])->name('catalog-items.force-delete');
Route::get('catalog-items/{content}/content', [CatalogItemController::class, 'content'])->name('catalog-items.content');

Route::get('count/{model}', [DashboardController::class, 'count'])->name('dashboard.count');
Route::get('institutions/centralized/count', [DashboardController::class, 'countCentralizedInstitutions'])->name('dashboard.countCentralizedInstitutions');
Route::get('/dashboard/questions-by-censo', [DashboardController::class, 'questionsByCenso']);
Route::get('/dashboard/get-censos', [DashboardController::class, 'getAllCensos']);

Route::apiResource('questions', QuestionController::class);
Route::get('questions/get-component/{id}', [QuestionController::class, 'getComponent'])->name('questions.get-component');
Route::post('questions/import', [QuestionController::class, 'import'])->name('questions.import');
Route::post('questions/{id}/restore', [QuestionController::class, 'restore'])->name('questions.restore');
Route::delete('questions/{id}/force-delete', [QuestionController::class, 'forceDelete'])->name('questions.force-delete');
Route::get('questions/{content}/content', [QuestionController::class, 'content'])->name('questions.content');
Route::get('questions/{id}/getForm', [QuestionController::class, 'getForm']);
Route::post('questions/{id}/saveForm', [QuestionController::class, 'saveForm']);

Route::apiResource('dependency', DependencyController::class);
Route::post('dependency/{id}/restore', [DependencyController::class, 'restore'])->name('dependency.restore');
Route::delete('dependency/{id}/force-delete', [DependencyController::class, 'forceDelete'])->name('dependency.force-delete');
Route::get('dependency/{content}/content', [DependencyController::class, 'content'])->name('dependency.content');

Route::apiResource('census-module', CensusModuleController::class)->only(['show','store','update']);
Route::apiResource('census-section', CensusSectionController::class)->only(['show','store','update']);
Route::apiResource('assignment-for-census', CensusAssignmentController::class)->only(['show','store','update']);
Route::apiResource('census-question', CensusQuestionController::class)->only(['show','store','update']);
Route::post('/censuses/clone', [CensusController::class, 'clone']);

Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
});
