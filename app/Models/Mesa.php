<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mesa extends Model
{
    // Campos que se pueden llenar masivamente
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

    // Formatear automáticamente campos a tipos nativos de PHP (boolean, float)
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

    // Relación: Una mesa puede tener muchas Reservas a lo largo del tiempo
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    // Relación: Una mesa pertenece a una Zona específica (Ej: Terraza, Salón Principal)
    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }

    // Relación: Una mesa puede estar bloqueada temporalmente por varios Holds
    public function holds(): HasMany
    {
        return $this->hasMany(MesaHold::class);
    }

    // Obtener los holds (bloqueos temporales) que todavía no han expirado
    public function activeHolds(): HasMany
    {
        return $this->holds()->active();
    }

    // Filtro rápido (Scope) para buscar solo mesas habilitadas/activas
    public function scopeActiva($query)
    {
        return $query->where('activa', true);
    }

    // Filtro rápido (Scope) para buscar mesas dentro de una zona específica
    public function scopeEnZona($query, int $zonaId)
    {
        return $query->where('zona_id', $zonaId);
    }

    // Formatear los datos de la mesa para poder dibujarla en el mapa interactivo (KonvaJS)
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
