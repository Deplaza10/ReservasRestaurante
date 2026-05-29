@extends('layouts.app')

@section('title', 'Reservar Mesa')

@section('content')
<div class="page-header" style="text-align: center; margin-bottom: 2rem;">
    <h1 style="justify-content: center; font-size: 2.5rem;"><i class="fas fa-chair"></i> Reservar Mesa</h1>
    <p>Selecciona la fecha y horario para ver la disponibilidad. Luego elige tu mesa ideal.</p>
</div>

<!-- Filtro de Disponibilidad -->
<div class="glass-card" style="margin-bottom: 2rem;">
    <form action="{{ route('cliente.mapa') }}" method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="flex: 1; min-width: 180px; margin-bottom: 0;">
            <label><i class="fas fa-calendar-day"></i> Fecha</label>
            <input type="date" name="fecha" class="form-control" value="{{ $fecha }}" min="{{ date('Y-m-d') }}" required>
        </div>
        <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
            <label><i class="fas fa-clock"></i> Hora Inicio</label>
            <select name="hora_inicio" class="form-control" required>
                <option value="">-- Seleccionar --</option>
                @for($h = 8; $h <= 22; $h++)
                    <option value="{{ sprintf('%02d:00', $h) }}" {{ request('hora_inicio') == sprintf('%02d:00', $h) ? 'selected' : '' }}>{{ sprintf('%02d:00', $h) }}</option>
                    <option value="{{ sprintf('%02d:30', $h) }}" {{ request('hora_inicio') == sprintf('%02d:30', $h) ? 'selected' : '' }}>{{ sprintf('%02d:30', $h) }}</option>
                @endfor
            </select>
        </div>
        <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
            <label><i class="fas fa-clock"></i> Hora Fin</label>
            <select name="hora_fin" class="form-control" required>
                <option value="">-- Seleccionar --</option>
                @for($h = 9; $h <= 23; $h++)
                    <option value="{{ sprintf('%02d:00', $h) }}" {{ request('hora_fin') == sprintf('%02d:00', $h) ? 'selected' : '' }}>{{ sprintf('%02d:00', $h) }}</option>
                    <option value="{{ sprintf('%02d:30', $h) }}" {{ request('hora_fin') == sprintf('%02d:30', $h) ? 'selected' : '' }}>{{ sprintf('%02d:30', $h) }}</option>
                @endfor
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="height: 44px; padding: 0 2rem;">
            <i class="fas fa-search"></i> Buscar Disponibilidad
        </button>
    </form>
</div>

@if(!request('hora_inicio') || !request('hora_fin'))
    <div class="glass-card" style="text-align: center; padding: 4rem 2rem;">
        <i class="fas fa-search" style="font-size: 3rem; color: var(--accent-primary); margin-bottom: 1rem;"></i>
        <h2 style="color: white; margin-bottom: 0.5rem;">Selecciona fecha y horario</h2>
        <p style="color: var(--text-muted); max-width: 500px; margin: 0 auto;">Para ver las mesas disponibles, primero elige la fecha y el rango de hora deseado usando el formulario de arriba.</p>
    </div>
@else
    <!-- Leyenda -->
    <div style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem; font-size: 0.9rem;">
        <span style="display: flex; align-items: center; gap: 6px;"><div style="width: 14px; height: 14px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 4px;"></div> Disponible</span>
        <span style="display: flex; align-items: center; gap: 6px;"><div style="width: 14px; height: 14px; background: linear-gradient(135deg, #ef4444, #dc2626); border-radius: 4px;"></div> Ocupada</span>
    </div>

    <!-- Grid de Mesas -->
    @php
        $mesasPorZona = $mesas->groupBy(fn($m) => $m->zona ? $m->zona->nombre : 'Sin Zona');
    @endphp

    @foreach($mesasPorZona as $zonaNombre => $mesasZona)
        <div style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.4rem; font-weight: 700; color: white; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-block; width: 16px; height: 16px; border-radius: 4px; background: {{ $mesasZona->first()->zona->color ?? '#64748b' }};"></span>
                {{ $zonaNombre }}
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.2rem;">
                @foreach($mesasZona as $mesa)
                    @php
                        $disponible = $mesa->esta_disponible;
                        $borderColor = $disponible ? '#10b981' : '#ef4444';
                        $bgGlow = $disponible ? 'rgba(16, 185, 129, 0.08)' : 'rgba(239, 68, 68, 0.08)';
                    @endphp
                    <div class="glass-card mesa-card {{ $disponible ? 'mesa-disponible' : 'mesa-ocupada' }}" 
                         style="padding: 0; overflow: hidden; border-left: 4px solid {{ $borderColor }}; background: {{ $bgGlow }}; transition: all 0.3s ease;">
                        
                        <div style="padding: 1.25rem;">
                            <!-- Header -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <h3 style="font-size: 1.2rem; font-weight: 700; color: white; margin: 0;">
                                    <i class="fas fa-{{ $mesa->tipo_mesa === 'redonda' ? 'circle' : ($mesa->tipo_mesa === 'barra' ? 'minus' : 'square') }}" style="color: {{ $borderColor }}; margin-right: 6px;"></i>
                                    {{ $mesa->nombre }}
                                </h3>
                                <span class="badge {{ $disponible ? 'badge-success' : '' }}" style="{{ !$disponible ? 'background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3);' : '' }}">
                                    {{ $disponible ? '✓ Disponible' : '✕ Ocupada' }}
                                </span>
                            </div>

                            <!-- Info -->
                            <div style="display: flex; gap: 1.5rem; margin-bottom: 1rem; color: var(--text-secondary); font-size: 0.9rem;">
                                <span><i class="fas fa-users" style="margin-right: 5px; color: var(--text-muted);"></i> {{ $mesa->capacidad }} pax</span>
                                <span><i class="fas fa-th-large" style="margin-right: 5px; color: var(--text-muted);"></i> {{ ucfirst($mesa->tipo_mesa) }}</span>
                            </div>


                            <!-- Botón -->
                            @if($disponible)
                                <button onclick="reservarMesa({{ $mesa->id }}, '{{ $fecha }}', '{{ request('hora_inicio') }}', '{{ request('hora_fin') }}')" 
                                   class="btn btn-primary btn-reservar-{{ $mesa->id }}" style="width: 100%; justify-content: center; padding: 12px;">
                                    <i class="fas fa-calendar-check"></i> Reservar Esta Mesa
                                </button>
                            @else
                                <button disabled class="btn btn-outline" style="width: 100%; justify-content: center; padding: 12px; opacity: 0.4; cursor: not-allowed;">
                                    <i class="fas fa-lock"></i> No Disponible
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @if($mesas->isEmpty())
        <div class="glass-card" style="text-align: center; padding: 4rem 2rem;">
            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #f59e0b; margin-bottom: 1rem;"></i>
            <h2 style="color: white;">No hay mesas registradas</h2>
            <p style="color: var(--text-muted);">El restaurante aún no tiene mesas configuradas. Por favor, contacta al administrador.</p>
        </div>
    @endif
@endif

<style>
    .mesa-disponible:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(16, 185, 129, 0.2);
        border-left-color: #34d399 !important;
    }
    .mesa-ocupada {
        opacity: 0.7;
    }
    .mesa-ocupada:hover {
        opacity: 0.85;
    }
</style>

<script>
function reservarMesa(mesaId, fecha, horaInicio, horaFin) {
    const btn = document.querySelector('.btn-reservar-' + mesaId);
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Reservando...';
    btn.disabled = true;

    // Primero crear el hold temporal
    fetch('/api/mesas/' + mesaId + '/hold', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            fecha: fecha,
            hora_inicio: horaInicio,
            hora_fin: horaFin
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Hold creado, redirigir al formulario de reserva
            window.location.href = '/cliente/reservar/' + mesaId + '?fecha=' + fecha + '&hora_inicio=' + horaInicio + '&hora_fin=' + horaFin;
        } else {
            alert(data.message || 'La mesa ya no está disponible. Por favor, intenta con otra mesa.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(err => {
        alert('Error al reservar la mesa. Inténtalo de nuevo.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>
@endsection

