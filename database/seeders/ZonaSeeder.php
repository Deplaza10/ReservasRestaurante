<?php

namespace Database\Seeders;

use App\Models\Zona;
use Illuminate\Database\Seeder;

class ZonaSeeder extends Seeder
{
    /**
     * Crear zonas del restaurante.
     */
    public function run(): void
    {
        $zonas = [
            ['nombre' => 'Interior', 'color' => '#3b82f6', 'orden' => 1],
            ['nombre' => 'Terraza',  'color' => '#10b981', 'orden' => 2],
            ['nombre' => 'VIP',      'color' => '#f5a623', 'orden' => 3],
        ];

        foreach ($zonas as $zona) {
            Zona::firstOrCreate(
                ['nombre' => $zona['nombre']],
                $zona
            );
        }

        $this->command->info('Zonas creadas: Interior, Terraza, VIP');
    }
}
