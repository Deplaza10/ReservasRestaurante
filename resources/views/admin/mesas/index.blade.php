@extends('layouts.app')

@section('title', 'Gestión de Mesas')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-chair"></i> Gestión de Mesas</h1>
    <p>Administra las mesas del restaurante. Activa o desactiva su disponibilidad para los clientes.</p>
</div>

@if(session('success'))
    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;">
    <!-- Lista de Mesas -->
    <div class="glass-card">
        <div class="card-title">
            <i class="fas fa-list"></i> Mesas Registradas
        </div>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted);">
                        <th style="padding: 12px;">Mesa</th>
                        <th style="padding: 12px;">Capacidad</th>
                        <th style="padding: 12px;">Zona</th>
                        <th style="padding: 12px;">Tipo</th>
                        <th style="padding: 12px;">Estado</th>
                        <th style="padding: 12px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mesas as $mesa)
                    <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.3s;">
                        <td style="padding: 12px; font-weight: 600;">
                            {{ $mesa->nombre }}
                        </td>
                        <td style="padding: 12px;">{{ $mesa->capacidad }} pax</td>
                        <td style="padding: 12px;">
                            <span class="badge" style="background: {{ $mesa->zona ? $mesa->zona->color : '#ccc' }}33; color: {{ $mesa->zona ? $mesa->zona->color : '#ccc' }}; border-color: {{ $mesa->zona ? $mesa->zona->color : '#ccc' }}66;">
                                {{ $mesa->zona ? $mesa->zona->nombre : 'Sin zona' }}
                            </span>
                        </td>
                        <td style="padding: 12px;">{{ ucfirst($mesa->tipo_mesa) }}</td>
                        <td style="padding: 12px;">
                            @if($mesa->activa)
                                <span class="badge badge-success">Disponible</span>
                            @else
                                <span class="badge" style="background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3);">No Disponible</span>
                            @endif
                        </td>
                        <td style="padding: 12px; text-align: right; display: flex; justify-content: flex-end; gap: 5px;">
                            <!-- Toggle Activa -->
                            <form action="{{ route('admin.mesas.toggleActiva', $mesa) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-outline" style="padding: 6px 10px;" title="{{ $mesa->activa ? 'Desactivar' : 'Activar' }} mesa">
                                    @if($mesa->activa)
                                        <i class="fas fa-toggle-on text-emerald-400" style="font-size: 1.2rem;"></i>
                                    @else
                                        <i class="fas fa-toggle-off text-rose-400" style="font-size: 1.2rem;"></i>
                                    @endif
                                </button>
                            </form>
                            <!-- Eliminar -->
                            <form action="{{ route('admin.mesas.destroy', $mesa) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta mesa?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline" style="padding: 6px 10px; color: var(--accent-primary); border-color: var(--accent-primary);">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-muted);">
                            No hay mesas registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Formulario Nueva Mesa -->
    <div class="glass-card" style="align-self: start;">
        <div class="card-title">
            <i class="fas fa-plus-circle"></i> Nueva Mesa
        </div>
        
        <form action="{{ route('admin.mesas.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nombre de la Mesa</label>
                <input type="text" name="nombre" class="form-control" required placeholder="Ej. Mesa 1, VIP 2...">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Número</label>
                    <input type="number" name="numero_mesa" class="form-control">
                </div>
                <div class="form-group">
                    <label>Capacidad</label>
                    <input type="number" name="capacidad" class="form-control" required min="1" max="50">
                </div>
            </div>

            <div class="form-group">
                <label>Zona</label>
                <select name="zona_id" class="form-control">
                    <option value="">-- Seleccionar Zona --</option>
                    @foreach($zonas as $zona)
                        <option value="{{ $zona->id }}">{{ $zona->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Tipo de Mesa</label>
                <select name="tipo_mesa" class="form-control" required>
                    <option value="cuadrada">Cuadrada</option>
                    <option value="redonda">Redonda</option>
                    <option value="rectangular">Rectangular</option>
                    <option value="barra">Barra</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary mt-4" style="width: 100%;">
                <i class="fas fa-save"></i> Guardar Mesa
            </button>
        </form>
    </div>
</div>
@endsection
