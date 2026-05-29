<?php

namespace App\Services;

use App\Models\Mesa;
use App\Models\MesaHold;
use App\Models\Reserva;
use Illuminate\Support\Facades\DB;

class MesaHoldService
{
    /**
     * Duración del bloqueo temporal en minutos.
     */
    const HOLD_DURATION_MINUTES = 5;

    /**
     * Intentar bloquear una mesa temporalmente.
     * Usa transacción con lock pesimista para evitar race conditions.
     *
     * @return MesaHold|false
     */
    public function holdMesa(
        int $mesaId,
        int $userId,
        string $fecha,
        string $horaInicio,
        string $horaFin
    ): MesaHold|false {
        return DB::transaction(function () use ($mesaId, $userId, $fecha, $horaInicio, $horaFin) {
            // 1. Verificar que no haya reserva existente (con lock pesimista)
            $conflictoReserva = Reserva::where('mesa_id', $mesaId)
                ->where('fecha', $fecha)
                ->where('estado_reserva', 'activa')
                ->where('hora_inicio', '<', $horaFin)
                ->where('hora_fin', '>', $horaInicio)
                ->lockForUpdate()
                ->exists();

            if ($conflictoReserva) {
                return false;
            }

            // 2. Verificar que no haya otro hold activo de otro usuario
            $holdExistente = MesaHold::where('mesa_id', $mesaId)
                ->where('fecha', $fecha)
                ->where('hora_inicio', '<', $horaFin)
                ->where('hora_fin', '>', $horaInicio)
                ->where('expires_at', '>', now())
                ->where('user_id', '!=', $userId)
                ->lockForUpdate()
                ->exists();

            if ($holdExistente) {
                return false;
            }

            // 3. Eliminar hold previo del mismo usuario para esta mesa/fecha si existe
            MesaHold::where('mesa_id', $mesaId)
                ->where('user_id', $userId)
                ->where('fecha', $fecha)
                ->delete();

            // 4. Crear nuevo hold
            return MesaHold::create([
                'mesa_id' => $mesaId,
                'user_id' => $userId,
                'fecha' => $fecha,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'expires_at' => now()->addMinutes(self::HOLD_DURATION_MINUTES),
            ]);
        });
    }

    /**
     * Liberar un hold específico del usuario.
     */
    public function releaseHold(int $mesaId, int $userId): bool
    {
        return MesaHold::where('mesa_id', $mesaId)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    /**
     * Liberar todos los holds expirados.
     * Se ejecuta cada minuto via scheduler.
     *
     * @return int Cantidad de holds liberados
     */
    public function releaseExpiredHolds(): int
    {
        return MesaHold::expired()->delete();
    }

    /**
     * Obtener el hold activo de un usuario para una mesa.
     */
    public function getActiveHold(int $mesaId, int $userId): ?MesaHold
    {
        return MesaHold::where('mesa_id', $mesaId)
            ->where('user_id', $userId)
            ->active()
            ->first();
    }

    /**
     * Verificar si el usuario tiene un hold activo para una mesa.
     */
    public function userHasHold(int $mesaId, int $userId): bool
    {
        return MesaHold::where('mesa_id', $mesaId)
            ->where('user_id', $userId)
            ->active()
            ->exists();
    }
}
