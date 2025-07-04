<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;



class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        

        
        $this->call([
            PaisesSeeder::class,
            DepartamentoSeeder::class,
            MunicipioSeeder::class,
            TipoEmpleadoSeeder::class,
            CategoriaUnidadesSeeder::class,
            UnidadDeMedidasSeeder::class,
            RolesAndPermissionsSeeder::class,
            TipoOrdenComprasSeeder::class,
            CategoriaUnidadesSeeder::class,
            UnidadDeMedidasSeeder::class,
            PersonaSeeder::class, 
            ClienteSeeder::class,
            OrdenComprasSeeder::class,
            ProductosSeeder::class,
        ]);

        $user = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@example.com',
        ]);

        //$role = Role::create(['name' => 'admin']);
        //$user->assignRole($role);
        $user = User::find(1);
        $user->assignRole('admin');
    }
}
