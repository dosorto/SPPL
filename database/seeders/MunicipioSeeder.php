<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Departamento;
use App\Models\Municipio;

class MunicipioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define todos los departamentos de Honduras y sus municipios, ordenados alfabéticamente
        $data = [
            'Atlántida' => [
                'Arizona', 'El Porvenir', 'Esparta', 'Jutiapa', 'La Ceiba', 'La Masica',
                'San Francisco', 'Tela'
            ],
            'Choluteca' => [
                'Apacilagua', 'Choluteca', 'Concepción de María', 'Duyure', 'El Corpus',
                'El Triunfo', 'Marcovia', 'Morolica', 'Namasigüe', 'Orocuina', 'Pespire',
                'San Antonio de Flores', 'San Isidro', 'San José', 'San Marcos de Colón',
                'Santa Ana de Yusguare'
            ],
            'Colón' => [
                'Balfate', 'Bonito Oriental', 'Iriona', 'Limón', 'Patuca', 'Sabá',
                'Santa Fé', 'Sonaguera', 'Tocoa', 'Trujillo'
            ],
            'Comayagua' => [
                'Ajuterique', 'Comayagua', 'El Rosario', 'Esquías', 'Humuya', 'La Libertad',
                'Lamaní', 'Las Lajas', 'Lejamani', 'Meámbar', 'Minas de Oro', 'Ojo de Agua',
                'San Jerónimo', 'San José de Comayagua', 'San Luis', 'San Sebastián',
                'Siguatepeque', 'Taulabé', 'Villa de San Antonio'
            ],
            'Copán' => [
                'Cabañas', 'Concepción', 'Copán Ruinas', 'Corquín', 'Cucuyagua', 'Dolores',
                'Dulce Nombre', 'El Paraíso', 'Florida', 'La Unión', 'Nueva Arcadia',
                'San Agustín', 'San Antonio', 'San Jerónimo', 'San José', 'San Juan de Opoa',
                'San Nicolás', 'San Pedro de Copán', 'Santa Rosa de Copán', 'Santa Rita',
                'Trinidad', 'Veracruz'
            ],
            'Cortés' => [
                'Choloma', 'La Lima', 'Omoa', 'Pimienta', 'Potrerillos', 'Puerto Cortés',
                'San Antonio de Cortés', 'San Manuel', 'San Pedro Sula', 'Santa Cruz de Yojoa',
                'Villanueva'
            ],
            'El Paraíso' => [
                'Alauca', 'Danlí', 'El Paraíso', 'Güinope', 'Jacaleapa', 'Liure',
                'Morocelí', 'Oropolí', 'Potrerillos', 'San Antonio de Flores', 'San Lucas',
                'San Matías', 'Soledad', 'Teupasenti', 'Texiguat', 'Trojes', 'Vado Ancho',
                'Yauyupe', 'Yuscarán'
            ],
            'Francisco Morazán' => [
                'Alubarén', 'Cantarranas', 'Cedros', 'Curarén', 'Distrito Central', 'El Porvenir',
                'Guaimaca', 'La Libertad', 'La Venta', 'Lepaterique', 'Maraita', 'Marale',
                'Nueva Armenia', 'Ojojona', 'Reitoca', 'Sabanagrande', 'San Antonio de Oriente',
                'San Buenaventura', 'San Ignacio', 'San Juan de Flores', 'San Miguelito',
                'Santa Ana', 'Santa Lucía', 'Talanga', 'Tatumbla', 'Tegucigalpa', 'Vallecillo',
                'Valle de Ángeles', 'Villa de San Francisco'
            ],
            'Gracias a Dios' => [
                'Ahuas', 'Brus Laguna', 'Juan Francisco Bulnes', 'Puerto Lempira',
                'Villeda Morales', 'Wampusirpi'
            ],
            'Intibucá' => [
                'Camasca', 'Colomoncagua', 'Concepción', 'Dolores', 'Jesús de Otoro',
                'La Esperanza', 'Magdalena', 'Masaguara', 'San Antonio',
                'San Francisco de Opalaca', 'San Isidro', 'San Juan', 'San Marco de Sierra',
                'San Miguelito', 'Santa Lucía', 'Victoria', 'Yamaranguila'
            ],
            'Islas de la Bahía' => [
                'Guanaja', 'José Santos Guardiola', 'Roatán', 'Utila'
            ],
            'La Paz' => [
                'Aguanqueterique', 'Cabañas', 'Cane', 'Chinacla', 'Guajiquiro', 'La Paz',
                'Lauterique', 'Marcala', 'Mercedes de Oriente', 'Opatoro',
                'San Antonio del Norte', 'San José', 'San Juan', 'San Pedro de Tutule',
                'Santa Ana', 'Santa Elena', 'Santa María', 'Santiago de Puringla', 'Yarula'
            ],
            'Lempira' => [
                'Belén', 'Candelaria', 'Cololaca', 'Erandique', 'Gracias', 'Gualcince',
                'Guarita', 'La Campa', 'La Iguala', 'La Unión', 'La Virtud', 'Las Flores',
                'Lepaera', 'Mapulaca', 'Piraera', 'San Andrés', 'San Francisco',
                'San Juan Guarita', 'San Manuel Colohete', 'San Rafael', 'San Sebastián',
                'Santa Cruz', 'Talgua', 'Tambla', 'Tomalá', 'Valladolid', 'Virginia',
                'Yamaranguila'
            ],
            'Ocotepeque' => [
                'Belén Gualcho', 'Concepción', 'Dolores Merendón', 'Fraternidad', 'La Encarnación',
                'La Labor', 'Lucerna', 'Mercedes', 'Ocotepeque', 'San Fernando',
                'San Francisco del Valle', 'Sinuapa'
            ],
            'Olancho' => [
                'Campamento', 'Catacamas', 'Concordia', 'Dulce Nombre de Culmí', 'El Rosario',
                'Esquipulas del Norte', 'Gualaco', 'Guarizama', 'Guata', 'Guayape', 'Jano',
                'Juticalpa', 'La Unión', 'La Venta', 'Mangulile', 'Manto', 'Patuca',
                'Salamá', 'San Esteban', 'San Francisco de la Paz', 'Santa María del Real',
                'Silca', 'Yocón'
            ],
            'Santa Bárbara' => [
                'Arada', 'Atima', 'Azacualpa', 'Ceguaca', 'Chinda', 'Concepción del Norte',
                'Concepción del Sur', 'El Níspero', 'Gualala', 'Ilama', 'Las Vegas',
                'Macuelizo', 'Naranjito', 'Nueva Frontera', 'Nuevo Celilac', 'Petoa',
                'Protección', 'Quimistán', 'San Francisco de Ojuera', 'San Luis', 'San Marcos',
                'San Nicolás', 'San Pedro Zacapa', 'Santa Bárbara', 'Santa Rita', 'Trinidad'
            ],
            'Valle' => [
                'Alianza', 'Amapala', 'Aramecina', 'Caridad', 'Goascorán', 'Langue', 'Nacaome',
                'San Francisco de Coray', 'San Lorenzo'
            ],
            'Yoro' => [
                'Arenal', 'El Negrito', 'El Progreso', 'Jocón', 'Morazán', 'Olanchito',
                'Santa Rita', 'Sulaco', 'Victoria', 'Yoro', 'Yorito'
            ],
        ];

        // Iterar sobre cada departamento para insertar sus municipios
        foreach ($data as $departamentoNombre => $municipios) {
            $departamento = Departamento::where('nombre_departamento', $departamentoNombre)->first();

            // Verificar si el departamento existe
            if (is_null($departamento)) {
                $this->command->error("Error: ¡No se encontró el departamento \"{$departamentoNombre}\"!");
                $this->command->warn('Asegúrate de que DepartamentoSeeder se haya ejecutado correctamente y haya insertado este departamento.');
                continue; // Saltar a la siguiente iteración si el departamento no se encuentra
            } else {
                $this->command->info("Procesando departamento \"{$departamentoNombre}\" (ID: {$departamento->id})...");
            }

            // Insertar los municipios para el departamento actual
            foreach ($municipios as $nombreMunicipio) {
                Municipio::firstOrCreate(
                    [
                        'nombre_municipio' => $nombreMunicipio,
                        'departamento_id' => $departamento->id,
                    ],
                    [
                        'created_by' => 1, // Asume un usuario con ID 1
                        'updated_by' => 1, // Asume un usuario con ID 1
                    ]
                );
            }
            $this->command->info("Municipios para {$departamentoNombre} insertados o ya existen.");
        }

        $this->command->info('Todos los municipios de Honduras han sido insertados o ya existen.');
    }
}
