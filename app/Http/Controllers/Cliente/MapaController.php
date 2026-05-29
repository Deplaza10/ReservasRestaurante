<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use App\Models\Zona;
use Illuminate\Http\Request;

class MapaController extends Controller
{
    // Mostrar la vista del mapa interactivo con la disponibilidad de las mesas
    public function index(Request $request)
    {
        // 1. Obtener los parámetros de búsqueda del cliente (fecha, hora inicio, hora fin)
        $fecha = $request->get('fecha', today()->format('Y-m-d'));
        $hora_inicio = $request->get('hora_inicio');
        $hora_fin = $request->get('hora_fin');

        // 2. Traer todas las zonas y mesas activas del restaurante
        $zonas = Zona::orderBy('orden')->get();
        $mesas = Mesa::with('zona')
            ->where('activa', true)
            ->orderBy('zona_id')
            ->orderBy('numero_mesa')
            ->get();

        // 3. Evaluar la disponibilidad de cada mesa de manera dinámica si se indicaron las horas
        foreach ($mesas as $mesa) {
            $mesa->esta_disponible = true; // Por defecto está disponible
            if ($hora_inicio && $hora_fin) {
                // Contar si hay reservas activas que se crucen con el horario elegido
                $conflictos = $mesa->reservas()
                    ->where('fecha', $fecha)
                    ->where('estado_reserva', 'activa')
                    ->where(function ($q) use ($hora_inicio, $hora_fin) {
                        $q->where(function ($q2) use ($hora_inicio, $hora_fin) {
                            $q2->where('hora_inicio', '<', $hora_fin)
                               ->where('hora_fin', '>', $hora_inicio);
                        });
                    })
                    ->count();
                // Si hay 0 conflictos, la mesa se mantiene disponible (true), sino se marca ocupada (false)
                $mesa->esta_disponible = $conflictos === 0;
            }
        }

        return view('cliente.mapa', compact('zonas', 'mesas', 'fecha'));
    }
}

