<?php

namespace Database\Seeders;

use App\Models\Mesa;
use App\Models\Zona;
use Illuminate\Database\Seeder;

class MesaSeeder extends Seeder
{
    /**
     * Crear mesas de ejemplo con posiciones para el mapa.
     * Máximo 15 mesas según requerimiento.
     */
    public function run(): void
    {
        $interior = Zona::where('nombre', 'Interior')->first();
        $terraza = Zona::where('nombre', 'Terraza')->first();
        $vip = Zona::where('nombre', 'VIP')->first();

        $mesas = [
            // Interior - 7 mesas
            ['nombre' => 'Mesa 1',  'capacidad' => 2, 'ubicacion' => 'Interior', 'zona_id' => $interior?->id, 'tipo_mesa' => 'cuadrada',    'pos_x' => 80,  'pos_y' => 120, 'ancho' => 70,  'alto' => 70,  'numero_mesa' => 1],
            ['nombre' => 'Mesa 2',  'capacidad' => 2, 'ubicacion' => 'Interior', 'zona_id' => $interior?->id, 'tipo_mesa' => 'cuadrada',    'pos_x' => 200, 'pos_y' => 120, 'ancho' => 70,  'alto' => 70,  'numero_mesa' => 2],
            ['nombre' => 'Mesa 3',  'capacidad' => 4, 'ubicacion' => 'Interior', 'zona_id' => $interior?->id, 'tipo_mesa' => 'rectangular', 'pos_x' => 80,  'pos_y' => 260, 'ancho' => 110, 'alto' => 70,  'numero_mesa' => 3],
            ['nombre' => 'Mesa 4',  'capacidad' => 4, 'ubicacion' => 'Interior', 'zona_id' => $interior?->id, 'tipo_mesa' => 'rectangular', 'pos_x' => 240, 'pos_y' => 260, 'ancho' => 110, 'alto' => 70,  'numero_mesa' => 4],
            ['nombre' => 'Mesa 5',  'capacidad' => 6, 'ubicacion' => 'Interior', 'zona_id' => $interior?->id, 'tipo_mesa' => 'redonda',     'pos_x' => 160, 'pos_y' => 410, 'ancho' => 100, 'alto' => 100, 'numero_mesa' => 5],
            ['nombre' => 'Mesa 6',  'capacidad' => 4, 'ubicacion' => 'Interior', 'zona_id' => $interior?->id, 'tipo_mesa' => 'cuadrada',    'pos_x' => 80,  'pos_y' => 540, 'ancho' => 80,  'alto' => 80,  'numero_mesa' => 6],
            ['nombre' => 'Mesa 7',  'capacidad' => 4, 'ubicacion' => 'Interior', 'zona_id' => $interior?->id, 'tipo_mesa' => 'cuadrada',    'pos_x' => 220, 'pos_y' => 540, 'ancho' => 80,  'alto' => 80,  'numero_mesa' => 7],

            // Terraza - 5 mesas
            ['nombre' => 'Mesa 8',  'capacidad' => 2, 'ubicacion' => 'Terraza',  'zona_id' => $terraza?->id,  'tipo_mesa' => 'redonda',     'pos_x' => 480, 'pos_y' => 120, 'ancho' => 70,  'alto' => 70,  'numero_mesa' => 8],
            ['nombre' => 'Mesa 9',  'capacidad' => 2, 'ubicacion' => 'Terraza',  'zona_id' => $terraza?->id,  'tipo_mesa' => 'redonda',     'pos_x' => 600, 'pos_y' => 120, 'ancho' => 70,  'alto' => 70,  'numero_mesa' => 9],
            ['nombre' => 'Mesa 10', 'capacidad' => 4, 'ubicacion' => 'Terraza',  'zona_id' => $terraza?->id,  'tipo_mesa' => 'cuadrada',    'pos_x' => 480, 'pos_y' => 260, 'ancho' => 80,  'alto' => 80,  'numero_mesa' => 10],
            ['nombre' => 'Mesa 11', 'capacidad' => 4, 'ubicacion' => 'Terraza',  'zona_id' => $terraza?->id,  'tipo_mesa' => 'cuadrada',    'pos_x' => 620, 'pos_y' => 260, 'ancho' => 80,  'alto' => 80,  'numero_mesa' => 11],
            ['nombre' => 'Mesa 12', 'capacidad' => 6, 'ubicacion' => 'Terraza',  'zona_id' => $terraza?->id,  'tipo_mesa' => 'rectangular', 'pos_x' => 530, 'pos_y' => 410, 'ancho' => 140, 'alto' => 70,  'numero_mesa' => 12],

            // VIP - 3 mesas
            ['nombre' => 'Mesa VIP 1', 'capacidad' => 6,  'ubicacion' => 'VIP', 'zona_id' => $vip?->id, 'tipo_mesa' => 'redonda',     'pos_x' => 830, 'pos_y' => 150, 'ancho' => 110, 'alto' => 110, 'numero_mesa' => 13],
            ['nombre' => 'Mesa VIP 2', 'capacidad' => 8,  'ubicacion' => 'VIP', 'zona_id' => $vip?->id, 'tipo_mesa' => 'rectangular', 'pos_x' => 800, 'pos_y' => 320, 'ancho' => 160, 'alto' => 80,  'numero_mesa' => 14],
            ['nombre' => 'Mesa VIP 3', 'capacidad' => 10, 'ubicacion' => 'VIP', 'zona_id' => $vip?->id, 'tipo_mesa' => 'rectangular', 'pos_x' => 800, 'pos_y' => 470, 'ancho' => 180, 'alto' => 90,  'numero_mesa' => 15],
        ];

        foreach ($mesas as $mesa) {
            Mesa::firstOrCreate(
                ['nombre' => $mesa['nombre']],
                array_merge($mesa, ['activa' => true, 'rotacion' => 0, 'piso' => 1])
            );
        }

        $this->command->info('15 mesas creadas con posiciones en el mapa.');
    }
}
