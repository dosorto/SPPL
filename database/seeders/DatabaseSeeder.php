<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\User;
use App\Models\Empresa;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Empleado;
use App\Models\Persona;
use App\Models\TipoEmpleado;
use App\Models\DepartamentoEmpleado;
use App\Models\CategoriaCliente;
use App\Models\Cliente;
use App\Models\MetodoPago;

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
            EmpresaSeeder::class,
            TipoEmpleadoSeeder::class,
            RolesAndPermissionsSeeder::class,
            TipoOrdenComprasSeeder::class,
            CategoriaUnidadesSeeder::class,
            UnidadDeMedidasSeeder::class,
            CategoriaClienteSeeder::class,  // Agregamos el seeder de categorías de clientes
            PersonaSeeder::class, 
            ClienteSeeder::class,
            AsignarCategoriasClientesSeeder::class,  // Agregamos el seeder para asignar categorías
            CategoriaClienteProductoSeeder::class,  // Agregamos el seeder para la relación entre categorías
            ProductosSeeder::class,
            OrdenComprasSeeder::class,
            OrdenComprasDetalleSeeder::class,
            DepartamentoEmpleadoSeeder::class,
            EmpleadoSeeder::class,
            DeduccionesSeeder::class,
            PercepcionesSeeder::class,
            CategoriaProductoSeeder::class,
            SubcategoriaProductoSeeder::class,
            MetodoPagoSeeder::class,
            CaiSeeder::class,
           // InventarioProductosSeeder::class,
        ]);
         
       $this->command->info('Configurando el usuario Root con su empleado base...');

        // --- 1. Asegurar que la empresa "GRUPO B" exista ---
        $empresa = Empresa::firstOrCreate(
            ['rtn' => '0801199900012'],
            [
                'nombre' => 'GRUPO B',
                'pais_id' => 80,
                'departamento_id' => 8,
                'municipio_id' => 131,
                'direccion' => 'Colonia Palmira, Tegucigalpa',
                'telefono' => '2233-4455',
            ]
        );

        $personaConsumidorFinal = Persona::firstOrCreate(
            ['dni' => '0000000000000'],
            [
                'primer_nombre' => 'Consumidor',
                'primer_apellido' => 'Final',
                'direccion' => 'Ciudad',
                'telefono' => '0000-0000',
                'sexo' => 'MASCULINO',
                'fecha_nacimiento' => now(),
                'municipio_id' => $empresa->municipio_id, // Usamos datos de la empresa
                'pais_id' => $empresa->pais_id,
                'departamento_id' => $empresa->departamento_id, // Añadimos el departamento_id
            ]
        );

        // Se enlaza esa persona como un cliente a la empresa "GRUPO B".
        Cliente::firstOrCreate(
            [
                'persona_id' => $personaConsumidorFinal->id,
                'empresa_id' => $empresa->id,
            ],
            [
                'rtn' => '00000000000000',
            ]
        );
        
        $this->command->info('Cliente "Consumidor Final" creado para GRUPO B.');

        // --- 2. Asegurar que el rol 'root' exista ---
        Role::firstOrCreate(['name' => 'root']);

        // --- 3. Asegurar que las dependencias para el empleado existan PARA ESTA EMPRESA ---
        $departamentoAdmin = DepartamentoEmpleado::firstOrCreate(
            [
                'nombre_departamento_empleado' => 'Administración',
                'empresa_id' => $empresa->id
            ],
            ['descripcion' => 'Departamento administrativo']
        );

        $tipoEmpleadoPlaza = TipoEmpleado::firstOrCreate(
            ['nombre_tipo' => 'Con Plaza'],
            ['descripcion' => 'Empleados con un puesto fijo y permanente en la organización.']
        );

        // --- 4. Crear la Persona para el empleado root ---
        $personaRoot = Persona::firstOrCreate(
            ['dni' => '9999999999999'],
            [
                'primer_nombre' => 'Usuario',
                'primer_apellido' => 'Root',
                'direccion' => $empresa->direccion,
                'telefono' => '0000-0000',
                'sexo' => 'MASCULINO',
                'fecha_nacimiento' => '1990-01-01',
                'empresa_id' => $empresa->id,
                'municipio_id' => $empresa->municipio_id,
                'pais_id' => $empresa->pais_id,
                'departamento_id' => $empresa->departamento_id, // Añadimos el departamento_id
            ]
        );

        // --- 5. Crear el Empleado asociado a la Persona ---
        $empleadoRoot = Empleado::firstOrCreate(
            ['persona_id' => $personaRoot->id],
            [
                'numero_empleado' => 'ROOT-001',
                'fecha_ingreso' => now(),
                'salario' => 99999.99,
                'departamento_empleado_id' => $departamentoAdmin->id,
                'empresa_id' => $empresa->id,
                'tipo_empleado_id' => $tipoEmpleadoPlaza->id,
            ]
        );

        // --- 6. Crear o actualizar el Usuario y enlazarlo todo ---
        $user = User::firstOrCreate(
            ['email' => 'root@example.com'],
            [
                'name' => 'root',
                'password' => bcrypt('password'),
                'empresa_id' => $empresa->id,
                'empleado_id' => $empleadoRoot->id, 
            ]
        );

        $user->empleado_id = $empleadoRoot->id;
        $user->save();
        $user->assignRole('root');

        $this->command->info('Usuario Root configurado y enlazado al empleado base correctamente.');

       
    }
}
