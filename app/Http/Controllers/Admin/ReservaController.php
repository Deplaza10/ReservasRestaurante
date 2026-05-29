<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Mesa;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReservaController extends Controller
{
    public function __construct(protected ReservaService $reservaService) {}

    public function index(Request $request)
    {
        $query = Reserva::with(['mesa.zona', 'user'])->where('estado_reserva', 'activa');

        if ($request->filled('buscar_documento')) {
            $query->where('numero_documento', 'like', '%' . $request->buscar_documento . '%');
        }

        if ($request->filled('fecha')) {
            $query->where('fecha', $request->fecha);
        }

        $reservas = $query->orderBy('fecha', 'desc')->orderBy('hora_inicio', 'desc')->paginate(15);
        $mesas = Mesa::where('activa', true)->orderBy('numero_mesa')->get();

        return view('admin.reservas.index', compact('reservas', 'mesas'));
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
            $this->reservaService->crearReservaAdmin($request->all());
            return redirect()->route('admin.reservas.index')->with('success', 'Reserva creada exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function update(Request $request, Reserva $reserva)
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'nombre_persona' => 'required|string|max:200',
            'numero_documento' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
            'cantidad_personas' => 'required|integer|min:1',
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
            'estado_pago' => 'required|in:pendiente,pagada',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            $this->reservaService->actualizarReserva($reserva, $request->all());
            return redirect()->route('admin.reservas.index')->with('success', 'Reserva actualizada exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function cancelar(Reserva $reserva)
    {
        $reserva->update(['estado_reserva' => 'cancelada']);
        return redirect()->route('admin.reservas.index')->with('success', 'Reserva cancelada exitosamente.');
    }

    public function destroy(Reserva $reserva)
    {
        $reserva->delete();
        return redirect()->route('admin.reservas.index')->with('success', 'Reserva eliminada exitosamente.');
    }

    public function togglePago(Reserva $reserva)
    {
        $nuevoEstado = $reserva->estado_pago === 'pendiente' ? 'pagada' : 'pendiente';
        $reserva->update(['estado_pago' => $nuevoEstado]);
        return redirect()->route('admin.reservas.index')->with('success', 'Estado de pago actualizado.');
    }

    public function facturaPdf(Reserva $reserva)
    {
        $reserva->load(['mesa.zona', 'user']);
        $pdf = Pdf::loadView('admin.reservas.factura', compact('reserva'));
        return $pdf->download("factura_reserva_{$reserva->id}.pdf");
    }
}
