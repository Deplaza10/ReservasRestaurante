<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Events\NuevoPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    public function index()
    {
        $productos = Producto::where('disponible', true)->get()->groupBy('categoria');
        return view('cliente.menu.index', compact('productos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'total' => 'required|numeric'
        ]);

        $pedido = Pedido::create([
            'user_id' => Auth::id(),
            'estado' => 'pendiente',
            'total' => $request->total
        ]);

        foreach ($request->items as $item) {
            $producto = Producto::find($item['id']);
            PedidoItem::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $producto->id,
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $producto->precio
            ]);
        }

        // Emitimos el evento a Reverb (no falla si Reverb no está activo)
        $pedido->load('items.producto', 'user');
        try {
            broadcast(new NuevoPedido($pedido));
        } catch (\Exception $e) {
            \Log::warning('No se pudo emitir evento de nuevo pedido: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true, 
            'message' => '¡Pedido enviado a la cocina con éxito!'
        ]);
    }
}
