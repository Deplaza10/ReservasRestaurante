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
     * CREAR RESERVA DE CLIENTE (Desde la web pública)
     */
    public function crearReservaCliente(array $data, User $user): Reserva
    {
        return DB::transaction(function () use ($data, $user) {
            // 1. Verificar disponibilidad de la mesa en ese horario (evita reservas duplicadas)
            $conflicto = Reserva::where('mesa_id', $data['mesa_id'])
                ->where('fecha', $data['fecha'])
                ->where('estado_reserva', 'activa')
                ->where('hora_inicio', '<', $data['hora_fin'])
                ->where('hora_fin', '>', $data['hora_inicio'])
                ->lockForUpdate() // Bloquea la fila para evitar condiciones de carrera
                ->exists();

            if ($conflicto) {
                throw new \Exception('La mesa ya no está disponible para ese horario.');
            }

            // 2. Verificar que no se exceda la capacidad máxima de personas de la mesa
            $mesa = Mesa::findOrFail($data['mesa_id']);
            if ($data['cantidad_personas'] > $mesa->capacidad) {
                throw new \Exception("La mesa solo tiene capacidad para {$mesa->capacidad} personas.");
            }

            // 3. Crear el registro de la reserva asociada al usuario
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

            // 4. Eliminar el hold (bloqueo temporal) ya que la reserva se confirmó con éxito
            MesaHold::where('mesa_id', $data['mesa_id'])
                ->where('user_id', $user->id)
                ->where('fecha', $data['fecha'])
                ->delete();

            return $reserva;
        });
    }

    /**
     * CREAR RESERVA DE ADMINISTRADOR (Desde el panel de administración)
     */
    public function crearReservaAdmin(array $data): Reserva
    {
        return DB::transaction(function () use ($data) {
            // 1. Verificar disponibilidad de la mesa en ese horario
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

            // 2. Verificar la capacidad de la mesa
            $mesa = Mesa::findOrFail($data['mesa_id']);
            if ($data['cantidad_personas'] > $mesa->capacidad) {
                throw new \Exception("La mesa solo tiene capacidad para {$mesa->capacidad} personas.");
            }

            // 3. Crear el registro de la reserva con los datos del administrador
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
     * ACTUALIZAR RESERVA EXISTENTE (Por un administrador)
     */
    public function actualizarReserva(Reserva $reserva, array $data): Reserva
    {
        return DB::transaction(function () use ($reserva, $data) {
            // 1. Detectar si cambió de mesa, fecha u horario
            $cambioHorario = (
                ($reserva->mesa_id != ($data['mesa_id'] ?? $reserva->mesa_id)) ||
                ($reserva->fecha->format('Y-m-d') != ($data['fecha'] ?? $reserva->fecha->format('Y-m-d'))) ||
                ($reserva->hora_inicio != ($data['hora_inicio'] ?? $reserva->hora_inicio)) ||
                ($reserva->hora_fin != ($data['hora_fin'] ?? $reserva->hora_fin))
            );

            // 2. Si cambió el horario o mesa, comprobar que no choque con otra reserva existente
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
                    ->where('id', '!=', $reserva->id) // Ignorar la reserva que estamos modificando
                    ->lockForUpdate()
                    ->exists();

                if ($conflicto) {
                    throw new \Exception('Esta mesa ya tiene una reserva activa para ese horario.');
                }
            }

            // 3. Si cambió la mesa o la cantidad de personas, verificar capacidad
            if (isset($data['mesa_id']) || isset($data['cantidad_personas'])) {
                $mesa = Mesa::findOrFail($data['mesa_id'] ?? $reserva->mesa_id);
                $cantidad = $data['cantidad_personas'] ?? $reserva->cantidad_personas;
                if ($cantidad > $mesa->capacidad) {
                    throw new \Exception("La mesa solo tiene capacidad para {$mesa->capacidad} personas.");
                }
            }

            // 4. Guardar los cambios
            $reserva->update($data);
            return $reserva->fresh();
        });
    }
}
