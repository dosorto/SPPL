<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoriaUnidades;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; 

class CategoriaUnidadesSeeder extends Seeder
{
    public function run(): void
    {
        //
    
        // Define un listado de nombres de categorías de unidades comunes.
        $categoriasUnidades = [
            'Peso',
            'Volumen',
            'Unidades',
            'Longitud',
            'Área',
            'Tiempo',
            'Temperatura',
            'Moneda',
            'Energía',
            'Densidad',
            'Presión',
            'Frecuencia',
        ];

        $dataToInsert = [];
        $now = Carbon::now(); // Obtiene la fecha y hora actual
        $createdBy = 1;       // ID del usuario por defecto que crea estos registros (ej. un administrador)

        // Prepara los datos para la inserción masiva.
        foreach ($categoriasUnidades as $nombreCategoria) {
            $dataToInsert[] = [
                'nombre'     => $nombreCategoria,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
                'deleted_at' => null, // Campo para SoftDeletes, por defecto es null al crear
                'deleted_by' => null, // Campo para SoftDeletes, por defecto es null al crear
            ];
        }

        // Inserta todos los datos preparados en la tabla 'categorias_unidades'.
        // Utiliza insertOrIgnore() para evitar errores si las categorías ya existen y tienes una restricción UNIQUE en 'nombre'.
        // Si no tienes restricción UNIQUE y quieres duplicados al re-ejecutar sin limpiar, usa insert().
        DB::table('categoria_unidades')->insertOrIgnore($dataToInsert);
    }
}
   