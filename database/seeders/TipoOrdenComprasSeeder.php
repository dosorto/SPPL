<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TipoOrdenComprasSeeder extends Seeder
{
    public function run(): void
    {
        $tiposOrden = [
            'Maquinaria',
            'Equipo',
            'Insumos',
            'Materia Prima',
            'Empaques',
        ];

        $dataToInsert = [];
        $now = Carbon::now();
        $createdBy = 1; // Adjust based on user ID
        $empresaId = 1; // Adjust based on empresa ID

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