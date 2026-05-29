<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use App\Models\Zona;
use Illuminate\Http\Request;

class MesaController extends Controller
{
    public function index()
    {
        $mesas = Mesa::with('zona')->orderBy('numero_mesa')->get();
        $zonas = Zona::orderBy('orden')->get();
        return view('admin.mesas.index', compact('mesas', 'zonas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'numero_mesa' => 'nullable|integer',
            'capacidad' => 'required|integer|min:1|max:50',
            'ubicacion' => 'nullable|string|max:100',
            'zona_id' => 'nullable|exists:zonas,id',
            'tipo_mesa' => 'required|in:redonda,cuadrada,rectangular,barra',
        ]);

        Mesa::create($request->all());

        return redirect()->route('admin.mesas.index')->with('success', 'Mesa creada exitosamente.');
    }

    public function update(Request $request, Mesa $mesa)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'numero_mesa' => 'nullable|integer',
            'capacidad' => 'required|integer|min:1|max:50',
            'ubicacion' => 'nullable|string|max:100',
            'zona_id' => 'nullable|exists:zonas,id',
            'tipo_mesa' => 'required|in:redonda,cuadrada,rectangular,barra',
        ]);

        $mesa->update($request->all());

        return redirect()->route('admin.mesas.index')->with('success', 'Mesa actualizada exitosamente.');
    }

    public function destroy(Mesa $mesa)
    {
        $mesa->delete();
        return redirect()->route('admin.mesas.index')->with('success', 'Mesa eliminada exitosamente.');
    }

    public function toggleActiva(Mesa $mesa)
    {
        $mesa->update(['activa' => !$mesa->activa]);
        $estado = $mesa->activa ? 'activada' : 'desactivada';
        return redirect()->route('admin.mesas.index')->with('success', "Mesa {$estado} exitosamente.");
    }
}
