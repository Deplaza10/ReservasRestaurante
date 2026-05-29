<?php

namespace App\Policies;

use App\Models\Reserva;
use App\Models\User;

class ReservaPolicy
{
    /**
     * Determina si el usuario puede ver la lista de reservas.
     */
    public function viewAny(User $user): bool
    {
        return true; // Ambos roles pueden ver listas (admin todas, cliente las suyas)
    }

    /**
     * Determina si el usuario puede ver una reserva específica.
     */
    public function view(User $user, Reserva $reserva): bool
    {
        return $user->hasRole('admin') || $user->id === $reserva->user_id;
    }

    /**
     * Determina si el usuario puede crear reservas.
     */
    public function create(User $user): bool
    {
        return true; // Ambos roles pueden crear
    }

    /**
     * Determina si el usuario puede actualizar una reserva.
     */
    public function update(User $user, Reserva $reserva): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determina si el usuario puede cancelar una reserva.
     */
    public function cancel(User $user, Reserva $reserva): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Cliente solo puede cancelar si es suya y la fecha es futura
        if ($user->id === $reserva->user_id) {
            if ($reserva->fecha > today()) {
                return true;
            }
            if ($reserva->fecha == today() && $reserva->hora_inicio > now()->format('H:i')) {
                return true;
            }
        }
        
        return false;
    }
}
