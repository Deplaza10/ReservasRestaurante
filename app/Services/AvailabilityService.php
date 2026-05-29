<?php

namespace App\Services;

use App\Models\Mesa;
use App\Models\MesaHold;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /**
     * Obtener disponibilidad de todas las mesas para una fecha y rango horario.
     *
     * @return Collection<int, array{mesa: Mesa, status: string}>
     */
    public function getMesasDisponibilidad(
        string $fecha,
        string $horaInicio,
        string $horaFin,
        ?int $zonaId = null
    ): Collection {
        $query = Mesa::with('zona')->where('activa', true);

        if ($zonaId) {
            $query->where('zona_id', $zonaId);
        }

        $mesas = $query->orderBy('numero_mesa')->get();

        // Obtener todas las reservas activas que se solapan
        $reservasMesaIds = Reserva::where('fecha', $fecha)
            ->where('estado_reserva', 'activa')
            ->where('hora_inicio', '<', $horaFin)
            ->where('hora_fin', '>', $horaInicio)
            ->pluck('mesa_id')
            ->toArray();

        // Obtener todos los holds activos que se solapan
        $holdsMesaIds = MesaHold::where('fecha', $fecha)
            ->where('hora_inicio', '<', $horaFin)
            ->where('hora_fin', '>', $horaInicio)
            ->where('expires_at', '>', now())
            ->pluck('mesa_id')
            ->toArray();

        return $mesas->map(function (Mesa $mesa) use ($reservasMesaIds, $holdsMesaIds) {
            $status = 'disponible';

            if (in_array($mesa->id, $reservasMesaIds)) {
                $status = 'reservada';
            } elseif (in_array($mesa->id, $holdsMesaIds)) {
                $status = 'bloqueada';
            }

            if (!$mesa->activa) {
                $status = 'inactiva';
            }

            return $mesa->toMapData($status);
        });
    }

    /**
     * Verificar si una mesa específica está disponible para un rango horario.
     * NO usa locks — para locks usar el MesaHoldService.
     */
    public function isMesaDisponible(
        int $mesaId,
        string $fecha,
        string $horaInicio,
        string $horaFin,
        ?int $excludeReservaId = null
    ): bool {
        $query = Reserva::where('mesa_id', $mesaId)
            ->where('fecha', $fecha)
            ->where('estado_reserva', 'activa')
            ->where('hora_inicio', '<', $horaFin)
            ->where('hora_fin', '>', $horaInicio);

        if ($excludeReservaId) {
            $query->where('id', '!=', $excludeReservaId);
        }

        if ($query->exists()) {
            return false;
        }

        // Verificar holds activos de otros usuarios
        $holdExists = MesaHold::where('mesa_id', $mesaId)
            ->where('fecha', $fecha)
            ->where('hora_inicio', '<', $horaFin)
            ->where('hora_fin', '>', $horaInicio)
            ->where('expires_at', '>', now())
            ->exists();

        return !$holdExists;
    }

    /**
     * Obtener las horas disponibles para una mesa en una fecha.
     * Horario del restaurante: Domingos y Lunes festivos 8am-5pm.
     */
    public function getHorasDisponibles(int $mesaId, string $fecha): array
    {
        $horaApertura = 8;  // 8:00 AM
        $horaCierre = 17;   // 5:00 PM

        $horasOcupadas = Reserva::where('mesa_id', $mesaId)
            ->where('fecha', $fecha)
            ->where('estado_reserva', 'activa')
            ->get(['hora_inicio', 'hora_fin']);

        $holdsActivos = MesaHold::where('mesa_id', $mesaId)
            ->where('fecha', $fecha)
            ->active()
            ->get(['hora_inicio', 'hora_fin']);

        $disponibles = [];

        for ($h = $horaApertura; $h < $horaCierre; $h++) {
            $horaStr = sprintf('%02d:00', $h);
            $horaFinStr = sprintf('%02d:00', $h + 1);

            $ocupada = $horasOcupadas->contains(function ($reserva) use ($horaStr, $horaFinStr) {
                return $reserva->hora_inicio < $horaFinStr && $reserva->hora_fin > $horaStr;
            });

            $bloqueada = $holdsActivos->contains(function ($hold) use ($horaStr, $horaFinStr) {
                return $hold->hora_inicio < $horaFinStr && $hold->hora_fin > $horaStr;
            });

            $disponibles[] = [
                'hora' => $horaStr,
                'disponible' => !$ocupada && !$bloqueada,
                'status' => $ocupada ? 'reservada' : ($bloqueada ? 'bloqueada' : 'disponible'),
            ];
        }

        return $disponibles;
    }
}
