<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zona extends Model
{
    protected $fillable = [
        'nombre',
        'color',
        'orden',
    ];

    /**
     * Relación: una zona tiene muchas mesas.
     */
    public function mesas(): HasMany
    {
        return $this->hasMany(Mesa::class);
    }
}
