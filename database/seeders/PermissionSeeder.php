<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $modules = [
            // Solo Ver
            'dashboard'   => ['view'],
            'graficador'  => ['view'],
            'reports'     => ['view'],
            // CRUD
            'notifications' => ['view', 'create', 'edit', 'deactivate', 'restore', 'delete'],
            'users'         => ['view', 'create', 'edit', 'deactivate', 'restore', 'delete'],
            'roles'         => ['view', 'create', 'edit', 'deactivate', 'restore', 'delete'],
            'institutions'  => ['view', 'create', 'edit', 'deactivate', 'restore', 'delete'],
            'dependencie'   => ['view', 'create', 'edit', 'deactivate', 'restore', 'delete'],
            'occupations'   => ['view', 'create', 'edit', 'deactivate', 'restore', 'delete'],
            'categories'    => ['view', 'create', 'edit', 'deactivate', 'restore', 'delete'],
            'catalogs'      => ['view', 'create', 'edit', 'deactivate', 'restore', 'delete'],

            // Con Carga Masiva
            'censos'     => ['view', 'create', 'edit', 'deactivate', 'restore', 'import', 'delete'],
            'modules'    => ['view', 'create', 'edit', 'deactivate', 'restore', 'import', 'delete'],
            'sections'   => ['view', 'create', 'edit', 'deactivate', 'restore', 'import', 'delete'],
            'indexs'     => ['view', 'create', 'edit', 'deactivate', 'restore', 'import', 'delete'],
            'questions'  => ['view', 'create', 'edit', 'deactivate', 'restore', 'import', 'delete'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $name = "{$module}.{$action}";


                Permission::firstOrCreate(['name' => $name]);
            }
        }
    }
}
