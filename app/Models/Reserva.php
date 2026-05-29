<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    // Campos que Laravel permite guardar de forma masiva (Mass Assignment)
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

    // Convierte automáticamente campos de la base de datos a objetos de tipo fecha/tiempo
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'confirmed_at' => 'datetime',
        ];
    }

    // Relación: Una reserva pertenece a una Mesa específica
    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class);
    }

    // Relación: Una reserva pertenece a un Usuario (Cliente) específico
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Filtro rápido (Scope) para obtener únicamente las reservas que están activas
    public function scopeActiva($query)
    {
        return $query->where('estado_reserva', 'activa');
    }

    // Filtro rápido (Scope) para obtener reservas de una fecha en específico
    public function scopeEnFecha($query, $fecha)
    {
        return $query->where('fecha', $fecha);
    }

    // Filtro para buscar si hay reservas que se cruzan/solapan en un rango de horas
    public function scopeSolapaCon($query, string $horaInicio, string $horaFin)
    {
        return $query->where('hora_inicio', '<', $horaFin)
                     ->where('hora_fin', '>', $horaInicio);
    }

    // Filtro para obtener las reservas pertenecientes a un usuario en específico
    public function scopeDelUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Retorna verdadero si la reserva está activa
    public function isActiva(): bool
    {
        return $this->estado_reserva === 'activa';
    }

    // Retorna verdadero si la reserva ya fue pagada
    public function isPagada(): bool
    {
        return $this->estado_pago === 'pagada';
    }
}
