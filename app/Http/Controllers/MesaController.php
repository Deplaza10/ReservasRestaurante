<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use Illuminate\Http\Request;

class MesaController extends Controller
{
    /**
     * Mostrar listado de mesas.
     */
    public function index()
    {
        $mesas = Mesa::orderBy('nombre')->get();
        return view('mesas.index', compact('mesas'));
    }

    /**
     * Guardar nueva mesa.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'capacidad' => 'required|integer|min:1|max:50',
            'ubicacion' => 'nullable|string|max:100',
        ]);

        Mesa::create($request->only('nombre', 'capacidad', 'ubicacion'));

        return redirect()->route('mesas.index')->with('success', 'Mesa creada exitosamente.');
    }

    /**
     * Actualizar mesa existente.
     */
    public function update(Request $request, Mesa $mesa)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'capacidad' => 'required|integer|min:1|max:50',
            'ubicacion' => 'nullable|string|max:100',
        ]);

        $mesa->update($request->only('nombre', 'capacidad', 'ubicacion'));

        return redirect()->route('mesas.index')->with('success', 'Mesa actualizada exitosamente.');
    }

    /**
     * Eliminar mesa.
     */
    public function destroy(Mesa $mesa)
    {
        $mesa->delete();
        return redirect()->route('mesas.index')->with('success', 'Mesa eliminada exitosamente.');
    }
}
