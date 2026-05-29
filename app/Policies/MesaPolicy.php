<?php

namespace App\Policies;

use App\Models\Mesa;
use App\Models\User;

class MesaPolicy
{
    /**
     * Determina si el usuario puede ver cualquier mesa (admin)
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determina si el usuario puede gestionar mesas.
     */
    public function manage(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
