<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use App\Models\Zona;
use Illuminate\Http\Request;

class MapaController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->get('fecha', today()->format('Y-m-d'));
        $hora_inicio = $request->get('hora_inicio');
        $hora_fin = $request->get('hora_fin');

        $zonas = Zona::orderBy('orden')->get();
        $mesas = Mesa::with('zona')
            ->where('activa', true)
            ->orderBy('zona_id')
            ->orderBy('numero_mesa')
            ->get();

        // Check availability for each mesa
        foreach ($mesas as $mesa) {
            $mesa->esta_disponible = true;
            if ($hora_inicio && $hora_fin) {
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
                $mesa->esta_disponible = $conflictos === 0;
            }
        }

        return view('cliente.mapa', compact('zonas', 'mesas', 'fecha'));
    }
}

