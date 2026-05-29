@extends('layouts.app')

@section('title', 'Mis Reservas')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-calendar-alt"></i> Mis Reservas</h1>
    <p>Historial y estado de todas tus reservas.</p>
</div>

<div class="glass-card">
    @if($reservas->count() > 0)
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Mesa</th>
                        <th>Personas</th>
                        <th>Estado Pago</th>
                        <th>Estado Reserva</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservas as $reserva)
                        <tr>
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</strong><br>
                                <span style="color: var(--text-muted); font-size: 0.8rem;">
                                    {{ $reserva->hora_inicio }} - {{ $reserva->hora_fin }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $reserva->mesa->nombre }}</strong><br>
                                <span style="color: var(--text-muted); font-size: 0.8rem;">
                                    {{ $reserva->mesa->zona->nombre ?? 'Principal' }}
                                </span>
                            </td>
                            <td>{{ $reserva->cantidad_personas }}</td>
                            <td>
                                <span class="badge {{ $reserva->estado_pago === 'pagada' ? 'badge-success' : 'badge-warning' }}">
                                    {{ ucfirst($reserva->estado_pago) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $estadoClass = match($reserva->estado_reserva) {
                                        'activa' => 'badge-success',
                                        'cancelada' => 'badge-danger',
                                        'completada' => 'badge-success',
                                        'no_show' => 'badge-danger',
                                        default => 'badge-warning'
                                    };
                                @endphp
                                <span class="badge {{ $estadoClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $reserva->estado_reserva)) }}
                                </span>
                            </td>
                            <td>
                                @can('cancel', $reserva)
                                    <form action="{{ route('cliente.reservas.cancelar', $reserva) }}" method="POST" onsubmit="return confirm('¿Estás seguro de cancelar esta reserva?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-danger" style="padding: 5px 10px;">
                                            <i class="fas fa-times"></i> Cancelar
                                        </button>
                                    </form>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">-</span>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1.5rem;">
            {{ $reservas->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>No tienes reservas aún</h3>
            <p>Explora el mapa del restaurante y haz tu primera reserva hoy mismo.</p>
            <a href="{{ route('cliente.mapa') }}" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-map"></i> Ver Mapa
            </a>
        </div>
    @endif
</div>
@endsection
