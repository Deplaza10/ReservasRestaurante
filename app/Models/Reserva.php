<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    protected $fillable = [
        'mesa_id',
        'user_id',
        'nombre_persona',
        'numero_documento',
        'telefono',
        'cantidad_personas',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado_pago',
        'estado_reserva',
        'observaciones',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'confirmed_at' => 'datetime',
        ];
    }

    /**
     * Relación: una reserva pertenece a una mesa.
     */
    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class);
    }

    /**
     * Relación: una reserva pertenece a un usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: solo reservas activas.
     */
    public function scopeActiva($query)
    {
        return $query->where('estado_reserva', 'activa');
    }

    /**
     * Scope: reservas de una fecha específica.
     */
    public function scopeEnFecha($query, $fecha)
    {
        return $query->where('fecha', $fecha);
    }

    /**
     * Scope: reservas que se solapan con un rango horario dado.
     * Algoritmo: nueva_inicio < existente_fin AND nueva_fin > existente_inicio
     */
    public function scopeSolapaCon($query, string $horaInicio, string $horaFin)
    {
        return $query->where('hora_inicio', '<', $horaFin)
                     ->where('hora_fin', '>', $horaInicio);
    }

    /**
     * Scope: reservas de un usuario.
     */
    public function scopeDelUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Verificar si la reserva está activa.
     */
    public function isActiva(): bool
    {
        return $this->estado_reserva === 'activa';
    }

    /**
     * Verificar si la reserva está pagada.
     */
    public function isPagada(): bool
    {
        return $this->estado_pago === 'pagada';
    }
}
