<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MetodoPago;

class MetodoPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creando métodos de pago por defecto...');

        $metodos = [
            [
                'nombre' => 'Efectivo',
                'requiere_referencia' => false,
            ],
            [
                'nombre' => 'Transferencia Bancaria',
                'requiere_referencia' => true,
            ],
            [
                'nombre' => 'Tarjeta de Crédito',
                'requiere_referencia' => true,
            ],
            [
                'nombre' => 'Tarjeta de Débito',
                'requiere_referencia' => true,
            ],
        ];

        foreach ($metodos as $metodo) {
            // Usamos firstOrCreate para evitar crear duplicados si el seeder se ejecuta varias veces.
            // Busca un método con ese 'nombre', y si no lo encuentra, lo crea con todos los datos.
            MetodoPago::firstOrCreate(
                ['nombre' => $metodo['nombre']],
                ['requiere_referencia' => $metodo['requiere_referencia']]
            );
        }

        $this->command->info('Se han creado los métodos de pago.');
    }
}
