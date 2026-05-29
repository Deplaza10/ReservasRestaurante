<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mesa extends Model
{
    protected $fillable = [
        'nombre',
        'capacidad',
        'ubicacion',
        'activa',
        'zona_id',
        'tipo_mesa',
        'pos_x',
        'pos_y',
        'ancho',
        'alto',
        'rotacion',
        'piso',
        'numero_mesa',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
            'pos_x' => 'float',
            'pos_y' => 'float',
            'ancho' => 'float',
            'alto' => 'float',
            'rotacion' => 'float',
        ];
    }

    /**
     * Relación: una mesa tiene muchas reservas.
     */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    /**
     * Relación: una mesa pertenece a una zona.
     */
    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }

    /**
     * Relación: una mesa tiene muchos holds temporales.
     */
    public function holds(): HasMany
    {
        return $this->hasMany(MesaHold::class);
    }

    /**
     * Holds activos (no expirados).
     */
    public function activeHolds(): HasMany
    {
        return $this->holds()->active();
    }

    /**
     * Scope: solo mesas activas.
     */
    public function scopeActiva($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Scope: mesas por zona.
     */
    public function scopeEnZona($query, int $zonaId)
    {
        return $query->where('zona_id', $zonaId);
    }

    /**
     * Obtener datos para renderizar en el mapa (Konva.js).
     */
    public function toMapData(string $status = 'disponible'): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'numero_mesa' => $this->numero_mesa,
            'capacidad' => $this->capacidad,
            'tipo_mesa' => $this->tipo_mesa,
            'pos_x' => $this->pos_x,
            'pos_y' => $this->pos_y,
            'ancho' => $this->ancho,
            'alto' => $this->alto,
            'rotacion' => $this->rotacion,
            'zona_id' => $this->zona_id,
            'zona_nombre' => $this->zona?->nombre,
            'zona_color' => $this->zona?->color,
            'status' => $status,
            'activa' => $this->activa,
        ];
    }
}
