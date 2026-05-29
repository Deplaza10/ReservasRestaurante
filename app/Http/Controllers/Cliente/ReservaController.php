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

    // Mostrar la lista de reservas hechas por el cliente autenticado
    public function index()
    {
        // Obtiene las reservas del usuario, cargando la información de su mesa y zona
        $reservas = Auth::user()->reservas()
            ->with('mesa.zona')
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->paginate(10);
            
        return view('cliente.mis-reservas', compact('reservas'));
    }

    // Mostrar el formulario para reservar una mesa específica
    public function create(Mesa $mesa, Request $request)
    {
        $fecha = $request->query('fecha');
        
        // Es obligatorio que el mapa haya enviado una fecha
        if (!$fecha) {
            return redirect()->route('cliente.mapa')->withErrors(['error' => 'Fecha requerida']);
        }

        return view('cliente.reservar', compact('mesa'));
    }

    // Procesar y guardar la nueva reserva enviada por el formulario
    public function store(Request $request)
    {
        // Validaciones básicas de seguridad y datos requeridos
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
            // Llama al servicio para guardar la reserva y validar disponibilidad en base de datos
            $this->reservaService->crearReservaCliente($request->all(), Auth::user());
            return redirect()->route('cliente.mis-reservas')->with('success', '¡Tu reserva ha sido confirmada!');
        } catch (\Exception $e) {
            // Si algo falla (capacidad excedida o mesa ocupada), se redirige mostrando el error
            return redirect()->route('cliente.mapa')->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Cancelar una reserva activa del cliente
    public function cancelar(Reserva $reserva)
    {
        // Seguridad: Verificar que la reserva realmente pertenece al usuario logueado
        if ($reserva->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        // Regla de negocio: No se puede cancelar si ya pasó la fecha o ya comenzó
        if ($reserva->fecha < today() || ($reserva->fecha == today() && $reserva->hora_inicio <= now()->format('H:i'))) {
            return back()->withErrors(['error' => 'No puedes cancelar una reserva pasada o en curso.']);
        }

        // Cambiar el estado de la reserva a cancelada
        $reserva->update(['estado_reserva' => 'cancelada']);
        return back()->with('success', 'Tu reserva ha sido cancelada.');
    }
}
