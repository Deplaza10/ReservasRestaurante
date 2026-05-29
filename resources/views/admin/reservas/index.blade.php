@extends('layouts.app')

@section('title', 'Gestión de Reservas')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-calendar-check"></i> Todas las Reservas</h1>
    <p>Revisa y administra el historial y las reservas activas.</p>
</div>

<div class="glass-card mb-6">
    <form action="{{ route('admin.reservas.index') }}" method="GET" class="flex gap-4 items-end">
        <div class="form-group mb-0 flex-1">
            <label>Fecha</label>
            <input type="date" name="fecha" class="form-control" value="{{ request('fecha') }}">
        </div>
        <div class="form-group mb-0 flex-1">
            <label>Buscar por Documento</label>
            <input type="text" name="buscar_documento" class="form-control" placeholder="Ej. 12345678" value="{{ request('buscar_documento') }}">
        </div>
        <button type="submit" class="btn btn-primary" style="height: 42px;">
            <i class="fas fa-search"></i> Buscar
        </button>
        <a href="{{ route('admin.reservas.index') }}" class="btn btn-outline" style="height: 42px;">
            Limpiar
        </a>
    </form>
</div>

<div class="glass-card">
    <div style="overflow-x: auto;">
        <table style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted);">
                    <th style="padding: 12px;">Cliente</th>
                    <th style="padding: 12px;">Fecha y Hora</th>
                    <th style="padding: 12px;">Mesa</th>
                    <th style="padding: 12px;">Pax</th>
                    <th style="padding: 12px;">Pago</th>
                    <th style="padding: 12px; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservas as $reserva)
                <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.3s;" class="hover:bg-slate-700/30">
                    <td style="padding: 12px;">
                        <div style="font-weight: 600;">{{ $reserva->nombre_persona }}</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Doc: {{ $reserva->numero_documento }}</div>
                    </td>
                    <td style="padding: 12px;">
                        <div>{{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</div>
                        <div style="font-weight: 600; color: var(--accent-primary);">
                            {{ \Carbon\Carbon::parse($reserva->hora_inicio)->format('h:i A') }}
                        </div>
                    </td>
                    <td style="padding: 12px;">
                        <span class="badge" style="background: {{ $reserva->mesa->zona ? $reserva->mesa->zona->color : '#ccc' }}33; color: {{ $reserva->mesa->zona ? $reserva->mesa->zona->color : '#ccc' }};">
                            {{ $reserva->mesa->nombre }}
                        </span>
                    </td>
                    <td style="padding: 12px;">{{ $reserva->cantidad_personas }}</td>
                    <td style="padding: 12px;">
                        <span class="badge {{ $reserva->estado_pago === 'pagada' ? 'badge-success' : 'badge-warning' }}">
                            {{ ucfirst($reserva->estado_pago) }}
                        </span>
                    </td>
                    <td style="padding: 12px; text-align: right; display: flex; justify-content: flex-end; gap: 5px;">
                        <form action="{{ route('admin.reservas.togglePago', $reserva) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-outline" style="padding: 6px 10px;" title="Cambiar Estado de Pago">
                                <i class="fas fa-money-bill-wave text-emerald-400"></i>
                            </button>
                        </form>
                        
                        <form action="{{ route('admin.reservas.cancelar', $reserva) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas cancelar esta reserva?');">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-outline" style="padding: 6px 10px;" title="Cancelar Reserva">
                                <i class="fas fa-ban text-rose-400"></i>
                            </button>
                        </form>

                        <a href="{{ route('admin.reservas.factura', $reserva) }}" target="_blank" class="btn btn-outline" style="padding: 6px 10px;" title="Descargar Factura">
                            <i class="fas fa-file-pdf text-blue-400"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-muted);">
                        No hay reservas que coincidan con la búsqueda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="mt-4">
            {{ $reservas->links() }}
        </div>
    </div>
</div>
@endsection
