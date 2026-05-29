@extends('layouts.app')

@section('title', 'Gestión de Zonas')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-layer-group"></i> Gestión de Zonas</h1>
    <p>Administra las diferentes áreas del restaurante (Ej. Terraza, VIP).</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;">
    <!-- Lista de Zonas -->
    <div class="glass-card">
        <div class="card-title">
            <i class="fas fa-list"></i> Zonas Registradas
        </div>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted);">
                        <th style="padding: 12px;">Orden</th>
                        <th style="padding: 12px;">Nombre</th>
                        <th style="padding: 12px;">Color</th>
                        <th style="padding: 12px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($zonas as $zona)
                    <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.3s;" class="hover:bg-slate-700/30">
                        <td style="padding: 12px;">{{ $zona->orden }}</td>
                        <td style="padding: 12px; font-weight: 600;">{{ $zona->nombre }}</td>
                        <td style="padding: 12px;">
                            <span class="badge" style="background: {{ $zona->color }}33; color: {{ $zona->color }}; border-color: {{ $zona->color }}66;">
                                <div class="w-3 h-3 rounded-full mr-1" style="background: {{ $zona->color }}"></div>
                                {{ $zona->color }}
                            </span>
                        </td>
                        <td style="padding: 12px; text-align: right;">
                            <form action="{{ route('admin.zonas.destroy', $zona) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Seguro que deseas eliminar esta zona?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline" style="padding: 6px 10px; color: var(--accent-primary); border-color: var(--accent-primary);">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding: 2rem; text-align: center; color: var(--text-muted);">
                            No hay zonas registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Formulario Nueva Zona -->
    <div class="glass-card" style="align-self: start;">
        <div class="card-title">
            <i class="fas fa-plus-circle"></i> Nueva Zona
        </div>
        
        <form action="{{ route('admin.zonas.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nombre de la Zona</label>
                <input type="text" name="nombre" class="form-control" required placeholder="Ej. Terraza Principal">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Color (Hex)</label>
                    <input type="color" name="color" class="form-control" style="padding: 4px; height: 42px;" value="#10b981">
                </div>
                <div class="form-group">
                    <label>Orden</label>
                    <input type="number" name="orden" class="form-control" required value="1">
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-4" style="width: 100%;">
                <i class="fas fa-save"></i> Guardar Zona
            </button>
        </form>
    </div>
</div>
@endsection
