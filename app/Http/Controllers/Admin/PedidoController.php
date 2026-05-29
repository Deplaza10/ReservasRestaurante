<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function live()
    {
        $pedidos = Pedido::with(['items.producto', 'user'])
            ->whereIn('estado', ['pendiente', 'preparando'])
            ->orderBy('created_at')
            ->get();
            
        return view('admin.pedidos.live', compact('pedidos'));
    }

    public function updateEstado(Request $request, Pedido $pedido)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,preparando,listo,entregado'
        ]);

        $pedido->update(['estado' => $request->estado]);

        return response()->json(['success' => true, 'nuevo_estado' => $pedido->estado]);
    }
}
