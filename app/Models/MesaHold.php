<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MesaHold extends Model
{
    protected $fillable = [
        'mesa_id',
        'user_id',
        'session_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Relación: un hold pertenece a una mesa.
     */
    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class);
    }

    /**
     * Relación: un hold pertenece a un usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: solo holds activos (no expirados).
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope: holds expirados.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Verificar si el hold ha expirado.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
