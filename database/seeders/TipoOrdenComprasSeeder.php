<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TipoOrdenComprasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposOrden = [
            'Equipo Maquinaria',
            'Insumos',
            'Materia Prima',
        ];

        $dataToInsert = [];
        $now = Carbon::now();
        $createdBy = 1; // Ajusta según el ID de usuario
        $empresaId = 1; // Ajusta según el ID de empresa disponible

        foreach ($tiposOrden as $nombreTipo) {
            $dataToInsert[] = [
                'nombre' => $nombreTipo,
                'empresa_id' => $empresaId,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
                'deleted_at' => null,
                'deleted_by' => null,
            ];
        }

        DB::table('tipo_orden_compras')->insertOrIgnore($dataToInsert);
    }
}