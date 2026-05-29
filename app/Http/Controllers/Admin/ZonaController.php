<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Zona;
use Illuminate\Http\Request;

class ZonaController extends Controller
{
    public function index()
    {
        $zonas = Zona::orderBy('orden')->get();
        return view('admin.zonas.index', compact('zonas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'color' => 'required|string|max:7',
            'orden' => 'required|integer',
        ]);

        Zona::create($request->all());
        return redirect()->route('admin.zonas.index')->with('success', 'Zona creada.');
    }

    public function update(Request $request, Zona $zona)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'color' => 'required|string|max:7',
            'orden' => 'required|integer',
        ]);

        $zona->update($request->all());
        return redirect()->route('admin.zonas.index')->with('success', 'Zona actualizada.');
    }

    public function destroy(Zona $zona)
    {
        $zona->delete();
        return redirect()->route('admin.zonas.index')->with('success', 'Zona eliminada.');
    }
}
