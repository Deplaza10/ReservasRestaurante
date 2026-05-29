<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReservaController extends Controller
{
    /**
     * Mostrar listado de reservas con buscador.
     */
    public function index(Request $request)
    {
        $query = Reserva::with('mesa')->where('estado_reserva', 'activa');

        // Buscador por número de documento
        if ($request->filled('buscar_documento')) {
            $query->where('numero_documento', 'like', '%' . $request->buscar_documento . '%');
        }

        // Buscador por ID de reserva
        if ($request->filled('buscar_reserva')) {
            $query->where('id', $request->buscar_reserva);
        }

        $reservas = $query->orderBy('fecha', 'desc')->orderBy('hora', 'desc')->get();
        $mesas = Mesa::where('activa', true)->orderBy('nombre')->get();

        return view('reservas.index', compact('reservas', 'mesas'));
    }

    /**
     * Guardar nueva reserva con validación de conflicto de horario.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'nombre_persona' => 'required|string|max:200',
            'numero_documento' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
            'cantidad_personas' => 'required|integer|min:1',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // Verificar que no exista otra reserva activa para la misma mesa, fecha y hora
        $conflicto = Reserva::where('mesa_id', $request->mesa_id)
            ->where('fecha', $request->fecha)
            ->where('hora', $request->hora)
            ->where('estado_reserva', 'activa')
            ->exists();

        if ($conflicto) {
            return back()->withErrors([
                'mesa_id' => 'Esta mesa ya tiene una reserva activa para esa fecha y hora.'
            ])->withInput();
        }

        // Verificar capacidad de la mesa
        $mesa = Mesa::findOrFail($request->mesa_id);
        if ($request->cantidad_personas > $mesa->capacidad) {
            return back()->withErrors([
                'cantidad_personas' => "La mesa seleccionada solo tiene capacidad para {$mesa->capacidad} personas."
            ])->withInput();
        }

        Reserva::create($request->only([
            'mesa_id', 'nombre_persona', 'numero_documento', 'telefono',
            'cantidad_personas', 'fecha', 'hora', 'observaciones'
        ]));

        return redirect()->route('reservas.index')->with('success', 'Reserva creada exitosamente.');
    }

    /**
     * Actualizar reserva existente.
     */
    public function update(Request $request, Reserva $reserva)
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'nombre_persona' => 'required|string|max:200',
            'numero_documento' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
            'cantidad_personas' => 'required|integer|min:1',
            'fecha' => 'required|date',
            'hora' => 'required',
            'estado_pago' => 'required|in:pendiente,pagada',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // Verificar conflicto solo si cambió mesa, fecha u hora
        if ($reserva->mesa_id != $request->mesa_id ||
            $reserva->fecha->format('Y-m-d') != $request->fecha ||
            $reserva->hora != $request->hora) {

            $conflicto = Reserva::where('mesa_id', $request->mesa_id)
                ->where('fecha', $request->fecha)
                ->where('hora', $request->hora)
                ->where('estado_reserva', 'activa')
                ->where('id', '!=', $reserva->id)
                ->exists();

            if ($conflicto) {
                return back()->withErrors([
                    'mesa_id' => 'Esta mesa ya tiene una reserva activa para esa fecha y hora.'
                ])->withInput();
            }
        }

        // Verificar capacidad
        $mesa = Mesa::findOrFail($request->mesa_id);
        if ($request->cantidad_personas > $mesa->capacidad) {
            return back()->withErrors([
                'cantidad_personas' => "La mesa seleccionada solo tiene capacidad para {$mesa->capacidad} personas."
            ])->withInput();
        }

        $reserva->update($request->only([
            'mesa_id', 'nombre_persona', 'numero_documento', 'telefono',
            'cantidad_personas', 'fecha', 'hora', 'estado_pago', 'observaciones'
        ]));

        return redirect()->route('reservas.index')->with('success', 'Reserva actualizada exitosamente.');
    }

    /**
     * Cancelar (eliminar lógicamente) una reserva.
     */
    public function cancelar(Reserva $reserva)
    {
        $reserva->update(['estado_reserva' => 'cancelada']);
        return redirect()->route('reservas.index')->with('success', 'Reserva cancelada exitosamente.');
    }

    /**
     * Eliminar permanentemente una reserva.
     */
    public function destroy(Reserva $reserva)
    {
        $reserva->delete();
        return redirect()->route('reservas.index')->with('success', 'Reserva eliminada exitosamente.');
    }

    /**
     * Cambiar estado de pago.
     */
    public function togglePago(Reserva $reserva)
    {
        $nuevoEstado = $reserva->estado_pago === 'pendiente' ? 'pagada' : 'pendiente';
        $reserva->update(['estado_pago' => $nuevoEstado]);
        return redirect()->route('reservas.index')->with('success', 'Estado de pago actualizado.');
    }

    /**
     * Generar factura en PDF.
     */
    public function facturaPdf(Reserva $reserva)
    {
        $reserva->load('mesa');
        $pdf = Pdf::loadView('reservas.factura', compact('reserva'));
        return $pdf->download("factura_reserva_{$reserva->id}.pdf");
    }
}
