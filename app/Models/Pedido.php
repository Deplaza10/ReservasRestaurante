<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'user_id', 'reserva_id', 'mesa_id', 'estado', 'total'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function mesa() {
        return $this->belongsTo(Mesa::class);
    }

    public function reserva() {
        return $this->belongsTo(Reserva::class);
    }

    public function items() {
        return $this->hasMany(PedidoItem::class);
    }
}
