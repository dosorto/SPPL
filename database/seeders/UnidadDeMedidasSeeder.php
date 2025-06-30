<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnidadDeMedidas;
use App\Models\CategoriaUnidades;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; 

class UnidadDeMedidasSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener los IDs de las categorías de unidades que ya deben haber sido sembradas.
        // Asegúrate de que CategoriaUnidadSeeder se ejecute antes que este seeder.
        $pesoId = CategoriaUnidades::where('nombre', 'Peso')->first()->id ?? null;
        $volumenId = CategoriaUnidades::where('nombre', 'Volumen')->first()->id ?? null;
        $unidadesId = CategoriaUnidades::where('nombre', 'Unidades')->first()->id ?? null;
        $longitudId = CategoriaUnidades::where('nombre', 'Longitud')->first()->id ?? null;
        $areaId = CategoriaUnidades::where('nombre', 'Área')->first()->id ?? null;
        $tiempoId = CategoriaUnidades::where('nombre', 'Tiempo')->first()->id ?? null;
        $temperaturaId = CategoriaUnidades::where('nombre', 'Temperatura')->first()->id ?? null;
        $monedaId = CategoriaUnidades::where('nombre', 'Moneda')->first()->id ?? null;
        $energiaId = CategoriaUnidades::where('nombre', 'Energía')->first()->id ?? null;

        // Si alguna categoría no se encuentra (lo cual no debería pasar si el orden de los seeders es correcto),
        // podrías añadir una lógica para crearla o lanzar un error. Por ahora, asumimos que existen.
        if (is_null($pesoId) || is_null($volumenId) || is_null($unidadesId)) {
            echo "¡Advertencia! No se encontraron todas las categorías de unidades base. Asegúrate de ejecutar CategoriaUnidadSeeder primero.\n";
            // Podrías lanzar una excepción o simplemente salir si las dependencias son críticas.
            // return;
        }

        // Define las unidades de medida que quieres insertar con su categoría asociada.
        $unidadesMedidas = [
            // Categoría: Peso
            ['nombre' => 'Kilogramo', 'abreviacion' => 'kg', 'categoria_id' => $pesoId],
            ['nombre' => 'Gramo', 'abreviacion' => 'g', 'categoria_id' => $pesoId],
            ['nombre' => 'Libra', 'abreviacion' => 'lb', 'categoria_id' => $pesoId],

            // Categoría: Volumen
            ['nombre' => 'Litro', 'abreviacion' => 'L', 'categoria_id' => $volumenId],
            ['nombre' => 'Mililitro', 'abreviacion' => 'mL', 'categoria_id' => $volumenId],
            ['nombre' => 'Galón', 'abreviacion' => 'gal', 'categoria_id' => $volumenId],

            // Categoría: Unidades
            ['nombre' => 'Unidad', 'abreviacion' => 'ud', 'categoria_id' => $unidadesId],
            ['nombre' => 'Caja', 'abreviacion' => 'caja', 'categoria_id' => $unidadesId],
            ['nombre' => 'Paquete', 'abreviacion' => 'pq', 'categoria_id' => $unidadesId],

            // Categoría: Longitud (si existe)
            ['nombre' => 'Metro', 'abreviacion' => 'm', 'categoria_id' => $longitudId],
            ['nombre' => 'Centímetro', 'abreviacion' => 'cm', 'categoria_id' => $longitudId],
            ['nombre' => 'Kilómetro', 'abreviacion' => 'km', 'categoria_id' => $longitudId],

            // Categoría: Área (si existe)
            ['nombre' => 'Metro Cuadrado', 'abreviacion' => 'm²', 'categoria_id' => $areaId],

            // Categoría: Tiempo (si existe)
            ['nombre' => 'Segundo', 'abreviacion' => 's', 'categoria_id' => $tiempoId],
            ['nombre' => 'Minuto', 'abreviacion' => 'min', 'categoria_id' => $tiempoId],

            // Categoría: Temperatura (si existe)
            ['nombre' => 'Grados Celsius', 'abreviacion' => '°C', 'categoria_id' => $temperaturaId],

            // Categoría: Moneda (si existe)
            ['nombre' => 'Lempira Hondureña', 'abreviacion' => 'HNL', 'categoria_id' => $monedaId],
            ['nombre' => 'Dólar Estadounidense', 'abreviacion' => 'USD', 'categoria_id' => $monedaId],

            // Categoría: Energía (si existe)
            ['nombre' => 'Joule', 'abreviacion' => 'J', 'categoria_id' => $energiaId],
        ];

        $dataToInsert = [];
        $now = Carbon::now();
        $createdBy = 1;

        // Prepara los datos para la inserción masiva, filtrando los que tienen categoría_id válida
        foreach ($unidadesMedidas as $unidad) {
            // Solo inserta si la categoria_id no es nula
            if (!is_null($unidad['categoria_id'])) {
                $dataToInsert[] = [
                    'nombre'        => $unidad['nombre'],
                    'abreviacion'   => $unidad['abreviacion'],
                    'categoria_id'  => $unidad['categoria_id'],
                    'created_at'    => $now,
                    'updated_at'    => $now,
                    'created_by'    => $createdBy,
                    'updated_by'    => $createdBy,
                    'deleted_at'    => null,
                    'deleted_by'    => null,
                ];
            }
        }

        // Inserta los datos en la tabla 'unidades_medidas'.
        // Usamos insertOrIgnore para evitar duplicados si se ejecuta varias veces.
        DB::table('unidad_de_medidas')->insertOrIgnore($dataToInsert);
    }
}

