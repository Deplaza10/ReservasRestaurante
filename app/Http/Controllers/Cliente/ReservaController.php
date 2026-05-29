<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use App\Models\Reserva;
use App\Services\MesaHoldService;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservaController extends Controller
{
    public function __construct(
        protected ReservaService $reservaService,
        protected MesaHoldService $holdService
    ) {}

    public function index()
    {
        $reservas = Auth::user()->reservas()
            ->with('mesa.zona')
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->paginate(10);
            
        return view('cliente.mis-reservas', compact('reservas'));
    }

    public function create(Mesa $mesa, Request $request)
    {
        $fecha = $request->query('fecha');
        
        if (!$fecha) {
            return redirect()->route('cliente.mapa')->withErrors(['error' => 'Fecha requerida']);
        }

        return view('cliente.reservar', compact('mesa'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'nombre_persona' => 'required|string|max:200',
            'numero_documento' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
            'cantidad_personas' => 'required|integer|min:1',
            'fecha' => 'required|date|after_or_equal:today',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            $this->reservaService->crearReservaCliente($request->all(), Auth::user());
            return redirect()->route('cliente.mis-reservas')->with('success', '¡Tu reserva ha sido confirmada!');
        } catch (\Exception $e) {
            return redirect()->route('cliente.mapa')->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancelar(Reserva $reserva)
    {
        if ($reserva->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        if ($reserva->fecha < today() || ($reserva->fecha == today() && $reserva->hora_inicio <= now()->format('H:i'))) {
            return back()->withErrors(['error' => 'No puedes cancelar una reserva pasada o en curso.']);
        }

        $reserva->update(['estado_reserva' => 'cancelada']);
        return back()->with('success', 'Tu reserva ha sido cancelada.');
    }
}
