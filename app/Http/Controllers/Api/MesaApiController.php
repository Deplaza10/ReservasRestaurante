<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use App\Services\AvailabilityService;
use App\Services\MesaHoldService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MesaApiController extends Controller
{
    public function __construct(
        protected AvailabilityService $availabilityService,
        protected MesaHoldService $holdService
    ) {}

    /**
     * Get availability of all tables for a specific date and time range
     */
    public function disponibilidad(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
            'zona_id' => 'nullable|exists:zonas,id'
        ]);

        $disponibilidad = $this->availabilityService->getMesasDisponibilidad(
            $request->fecha,
            $request->hora_inicio,
            $request->hora_fin,
            $request->zona_id
        );

        return response()->json($disponibilidad);
    }
    
    /**
     * Get available hours for a single table on a specific date
     */
    public function horasDisponibles(Mesa $mesa, Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
        ]);
        
        $horas = $this->availabilityService->getHorasDisponibles($mesa->id, $request->fecha);
        return response()->json($horas);
    }

    /**
     * Attempt to hold a table
     */
    public function hold(Mesa $mesa, Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
        ]);

        $hold = $this->holdService->holdMesa(
            $mesa->id,
            Auth::id(),
            $request->fecha,
            $request->hora_inicio,
            $request->hora_fin
        );

        if ($hold) {
            return response()->json(['success' => true, 'hold' => $hold]);
        }

        return response()->json(['success' => false, 'message' => 'La mesa ya no está disponible'], 409);
    }

    /**
     * Release a hold early (if user cancels)
     */
    public function releaseHold(Mesa $mesa)
    {
        $this->holdService->releaseHold($mesa->id, Auth::id());
        return response()->json(['success' => true]);
    }

    /**
     * Admin only: Update table position on map (drag & drop)
     */
    public function updatePosition(Mesa $mesa, Request $request)
    {
        $request->validate([
            'pos_x' => 'required|numeric',
            'pos_y' => 'required|numeric',
            'rotacion' => 'nullable|numeric'
        ]);

        $mesa->update($request->only(['pos_x', 'pos_y', 'rotacion']));
        
        return response()->json(['success' => true]);
    }
}
