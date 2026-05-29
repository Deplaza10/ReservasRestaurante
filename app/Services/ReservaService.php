<?php

namespace App\Services;

use App\Models\Mesa;
use App\Models\MesaHold;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReservaService
{
    public function __construct(
        protected AvailabilityService $availabilityService,
        protected MesaHoldService $mesaHoldService,
    ) {}

    /**
     * Crear reserva como cliente (requiere hold activo).
     *
     * @throws \Exception
     */
    public function crearReservaCliente(array $data, User $user): Reserva
    {
        return DB::transaction(function () use ($data, $user) {
            // 1. Verificar que el usuario tiene un hold activo para esta mesa
            $hold = MesaHold::where('mesa_id', $data['mesa_id'])
                ->where('user_id', $user->id)
                ->where('fecha', $data['fecha'])
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->first();

            if (!$hold) {
                throw new \Exception('El tiempo de reserva ha expirado. Por favor, selecciona la mesa nuevamente.');
            }

            // 2. Doble verificación de disponibilidad (con lock)
            $conflicto = Reserva::where('mesa_id', $data['mesa_id'])
                ->where('fecha', $data['fecha'])
                ->where('estado_reserva', 'activa')
                ->where('hora_inicio', '<', $data['hora_fin'])
                ->where('hora_fin', '>', $data['hora_inicio'])
                ->lockForUpdate()
                ->exists();

            if ($conflicto) {
                throw new \Exception('La mesa ya no está disponible para ese horario.');
            }

            // 3. Verificar capacidad
            $mesa = Mesa::findOrFail($data['mesa_id']);
            if ($data['cantidad_personas'] > $mesa->capacidad) {
                throw new \Exception("La mesa solo tiene capacidad para {$mesa->capacidad} personas.");
            }

            // 4. Crear reserva
            $reserva = Reserva::create([
                'mesa_id' => $data['mesa_id'],
                'user_id' => $user->id,
                'nombre_persona' => $data['nombre_persona'] ?? $user->name,
                'numero_documento' => $data['numero_documento'],
                'telefono' => $data['telefono'] ?? $user->telefono,
                'cantidad_personas' => $data['cantidad_personas'],
                'fecha' => $data['fecha'],
                'hora_inicio' => $data['hora_inicio'],
                'hora_fin' => $data['hora_fin'],
                'observaciones' => $data['observaciones'] ?? null,
                'estado_pago' => 'pendiente',
                'estado_reserva' => 'activa',
                'confirmed_at' => now(),
            ]);

            // 5. Eliminar hold
            $hold->delete();

            return $reserva;
        });
    }

    /**
     * Crear reserva como administrador (sin necesidad de hold).
     */
    public function crearReservaAdmin(array $data): Reserva
    {
        return DB::transaction(function () use ($data) {
            // Verificar disponibilidad con lock
            $conflicto = Reserva::where('mesa_id', $data['mesa_id'])
                ->where('fecha', $data['fecha'])
                ->where('estado_reserva', 'activa')
                ->where('hora_inicio', '<', $data['hora_fin'])
                ->where('hora_fin', '>', $data['hora_inicio'])
                ->lockForUpdate()
                ->exists();

            if ($conflicto) {
                throw new \Exception('Esta mesa ya tiene una reserva activa para ese horario.');
            }

            // Verificar capacidad
            $mesa = Mesa::findOrFail($data['mesa_id']);
            if ($data['cantidad_personas'] > $mesa->capacidad) {
                throw new \Exception("La mesa solo tiene capacidad para {$mesa->capacidad} personas.");
            }

            return Reserva::create([
                'mesa_id' => $data['mesa_id'],
                'user_id' => $data['user_id'] ?? null,
                'nombre_persona' => $data['nombre_persona'],
                'numero_documento' => $data['numero_documento'],
                'telefono' => $data['telefono'],
                'cantidad_personas' => $data['cantidad_personas'],
                'fecha' => $data['fecha'],
                'hora_inicio' => $data['hora_inicio'],
                'hora_fin' => $data['hora_fin'],
                'observaciones' => $data['observaciones'] ?? null,
                'estado_pago' => $data['estado_pago'] ?? 'pendiente',
                'estado_reserva' => 'activa',
                'confirmed_at' => now(),
            ]);
        });
    }

    /**
     * Actualizar reserva existente (solo admin).
     */
    public function actualizarReserva(Reserva $reserva, array $data): Reserva
    {
        return DB::transaction(function () use ($reserva, $data) {
            // Solo verificar conflicto si cambió mesa, fecha u hora
            $cambioHorario = (
                ($reserva->mesa_id != ($data['mesa_id'] ?? $reserva->mesa_id)) ||
                ($reserva->fecha->format('Y-m-d') != ($data['fecha'] ?? $reserva->fecha->format('Y-m-d'))) ||
                ($reserva->hora_inicio != ($data['hora_inicio'] ?? $reserva->hora_inicio)) ||
                ($reserva->hora_fin != ($data['hora_fin'] ?? $reserva->hora_fin))
            );

            if ($cambioHorario) {
                $mesaId = $data['mesa_id'] ?? $reserva->mesa_id;
                $fecha = $data['fecha'] ?? $reserva->fecha->format('Y-m-d');
                $horaInicio = $data['hora_inicio'] ?? $reserva->hora_inicio;
                $horaFin = $data['hora_fin'] ?? $reserva->hora_fin;

                $conflicto = Reserva::where('mesa_id', $mesaId)
                    ->where('fecha', $fecha)
                    ->where('estado_reserva', 'activa')
                    ->where('hora_inicio', '<', $horaFin)
                    ->where('hora_fin', '>', $horaInicio)
                    ->where('id', '!=', $reserva->id)
                    ->lockForUpdate()
                    ->exists();

                if ($conflicto) {
                    throw new \Exception('Esta mesa ya tiene una reserva activa para ese horario.');
                }
            }

            // Verificar capacidad si cambió mesa o cantidad
            if (isset($data['mesa_id']) || isset($data['cantidad_personas'])) {
                $mesa = Mesa::findOrFail($data['mesa_id'] ?? $reserva->mesa_id);
                $cantidad = $data['cantidad_personas'] ?? $reserva->cantidad_personas;
                if ($cantidad > $mesa->capacidad) {
                    throw new \Exception("La mesa solo tiene capacidad para {$mesa->capacidad} personas.");
                }
            }

            $reserva->update($data);
            return $reserva->fresh();
        });
    }
}
