<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Empresa;
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
            RolesAndPermissionsSeeder::class,
            TipoOrdenComprasSeeder::class,
            CategoriaUnidadesSeeder::class,
            UnidadDeMedidasSeeder::class,
            PersonaSeeder::class, 
            ClienteSeeder::class,
            ProductosSeeder::class,
            OrdenComprasSeeder::class,
            OrdenComprasDetalleSeeder::class,
        ]);
         
       $empresa = Empresa::create([
            'nombre' => 'GRUPO B',
            'pais_id' => 80,
            'departamento_id' => 8,
            'municipio_id' => 131,
            'direccion' => 'Colonia Palmira, Tegucigalpa',
            'telefono' => '2233-4455',
            'rtn' => '0801199900012',
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $user = User::factory()->create([
            'name' => 'root',
            'email' => 'root@example.com',
            'empresa_id' => $empresa->id,
        ]);

        //$role = Role::create(['name' => 'admin']);
        //$user->assignRole($role);
        $user = User::find(1);
        $user->assignRole('root');
    }
}
