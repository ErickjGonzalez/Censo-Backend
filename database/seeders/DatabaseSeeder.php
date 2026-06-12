<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        /* User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]); */
        /* crear roles basicos */
            $this->call(RoleSeeder::class);
            $this->call(PermissionSeeder::class);
            /* 
            $this->call(PermissionRoleSeeder::class);
            $this->call(UnitSeeder::class);
            $this->call(SectionSeeder::class);
            $this->call(InstitutionSeeder::class);
            $this->call(OccupationSeeder::class);
            $this->call(CategorySeeder::class);
             $this->call(QuestionSeeder::class);
             $this->call(AreaSeeder::class);
             $this->call(CatalogSeeder::class);  
            $this->call(CatalogItemSeeder::class);
            $this->call(UserSeeder::class);
            $this->call(CensoSeeder::class); */

    }
}
