<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use App\Models\Reserva;
use App\Models\Zona;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $mesasCount = Mesa::activa()->count();
        $reservasActivas = Reserva::activa()->count();
        $reservasHoy = Reserva::activa()->enFecha(today()->format('Y-m-d'))->count();
        $pagadas = Reserva::activa()->where('estado_pago', 'pagada')->count();

        $reservasRecientes = Reserva::with(['mesa', 'user'])
            ->activa()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'mesasCount',
            'reservasActivas',
            'reservasHoy',
            'pagadas',
            'reservasRecientes'
        ));
    }
}
