@extends('layouts.app')

@section('title', 'Gestión de Reservas')

@section('content')
<div class="page-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1><i class="fas fa-calendar-check"></i> Gestión de Reservas</h1>
        <p>Administra todas las reservas del restaurante</p>
    </div>
    <button class="btn btn-primary" onclick="abrirModalCrear()">
        <i class="fas fa-plus"></i> Nueva Reserva
    </button>
</div>

<!-- Stats rápidos -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-info">
            <h3>{{ $reservas->count() }}</h3>
            <p>Total Activas</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-double"></i></div>
        <div class="stat-info">
            <h3>{{ $reservas->where('estado_pago', 'pagada')->count() }}</h3>
            <p>Pagadas</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon gold"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-info">
            <h3>{{ $reservas->where('estado_pago', 'pendiente')->count() }}</h3>
            <p>Pendientes</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <h3>{{ $reservas->sum('cantidad_personas') }}</h3>
            <p>Personas Totales</p>
        </div>
    </div>
</div>

<!-- Buscador -->
<div class="glass-card" style="margin-bottom: 1.5rem;">
    <div class="card-title"><i class="fas fa-search"></i> Buscar Reserva</div>
    <form action="{{ route('reservas.index') }}" method="GET">
        <div class="search-bar">
            <div class="form-group">
                <label>Número de Documento</label>
                <input type="text" name="buscar_documento" class="form-control" placeholder="Buscar por documento..." value="{{ request('buscar_documento') }}">
            </div>
            <div class="form-group">
                <label>ID de Reserva</label>
                <input type="number" name="buscar_reserva" class="form-control" placeholder="Buscar por # reserva..." value="{{ request('buscar_reserva') }}">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
            @if(request('buscar_documento') || request('buscar_reserva'))
                <a href="{{ route('reservas.index') }}" class="btn btn-outline">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Tabla de Reservas -->
<div class="glass-card">
    <div class="card-title" style="justify-content: space-between;">
        <span><i class="fas fa-list"></i> Listado de Reservas</span>
        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 400;">{{ $reservas->count() }} resultados</span>
    </div>
    <div class="table-container">
        @if($reservas->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Persona</th>
                        <th>Documento</th>
                        <th>Teléfono</th>
                        <th>Mesa</th>
                        <th>Fecha & Hora</th>
                        <th>Personas</th>
                        <th>Estado Pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservas as $reserva)
                        <tr>
                            <td style="font-weight: 700; color: var(--accent-primary);">#{{ $reserva->id }}</td>
                            <td>
                                <div style="font-weight: 600;">{{ $reserva->nombre_persona }}</div>
                            </td>
                            <td>
                                <span style="font-family: monospace; font-size: 0.85rem; color: var(--text-secondary);">{{ $reserva->numero_documento }}</span>
                            </td>
                            <td>
                                <span style="color: var(--text-secondary);">
                                    <i class="fas fa-phone" style="font-size: 0.7rem; color: var(--accent-green);"></i>
                                    {{ $reserva->telefono }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-success" style="font-size: 0.72rem;">
                                    <i class="fas fa-chair"></i> {{ $reserva->mesa->nombre }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $reserva->fecha->format('d/m/Y') }}</div>
                                <div style="font-size: 0.8rem; color: var(--accent-gold);">
                                    <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($reserva->hora)->format('h:i A') }}
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span style="font-weight: 700; font-size: 1rem; color: var(--accent-blue);">{{ $reserva->cantidad_personas }}</span>
                            </td>
                            <td>
                                <form action="{{ route('reservas.togglePago', $reserva) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="badge {{ $reserva->estado_pago === 'pagada' ? 'badge-success' : 'badge-warning' }}" style="cursor: pointer; border: none; font-family: inherit;" title="Click para cambiar estado">
                                        <span class="pulse-dot {{ $reserva->estado_pago === 'pagada' ? 'green' : 'gold' }}"></span>
                                        {{ $reserva->estado_pago === 'pagada' ? 'PAGADA' : 'PENDIENTE' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <!-- Editar -->
                                    <button class="btn btn-outline btn-icon btn-sm" title="Editar"
                                        onclick="editarReserva({{ json_encode($reserva) }})">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <!-- Cancelar -->
                                    <form action="{{ route('reservas.cancelar', $reserva) }}" method="POST" style="display:inline;"
                                        onsubmit="return confirm('¿Seguro que deseas cancelar esta reserva?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-warning btn-icon btn-sm" title="Cancelar Reserva">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                    <!-- Eliminar -->
                                    <form action="{{ route('reservas.destroy', $reserva) }}" method="POST" style="display:inline;"
                                        onsubmit="return confirm('¿Seguro que deseas ELIMINAR permanentemente esta reserva?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-icon btn-sm" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <i class="fas fa-calendar-xmark"></i>
                <h3>No se encontraron reservas</h3>
                <p>
                    @if(request('buscar_documento') || request('buscar_reserva'))
                        No hay resultados para tu búsqueda. Intenta con otros criterios.
                    @else
                        Crea una nueva reserva para comenzar.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

<!-- Modal Crear Reserva -->
<div class="modal-overlay" id="modalCrear">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-calendar-plus"></i> Nueva Reserva</h2>
            <button class="modal-close" onclick="cerrarModal('modalCrear')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('reservas.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre_persona" class="form-control" placeholder="Nombre del cliente" value="{{ old('nombre_persona') }}" required>
                </div>
                <div class="form-group">
                    <label>Número de Documento</label>
                    <input type="text" name="numero_documento" class="form-control" placeholder="Cédula o documento" value="{{ old('numero_documento') }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" class="form-control" placeholder="Número de contacto" value="{{ old('telefono') }}" required>
                </div>
                <div class="form-group">
                    <label>Cantidad de Personas</label>
                    <input type="number" name="cantidad_personas" class="form-control" min="1" placeholder="¿Cuántas personas?" value="{{ old('cantidad_personas') }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Mesa</label>
                    <select name="mesa_id" class="form-control" required>
                        <option value="">Seleccionar mesa...</option>
                        @foreach($mesas as $mesa)
                            <option value="{{ $mesa->id }}" {{ old('mesa_id') == $mesa->id ? 'selected' : '' }}>
                                {{ $mesa->nombre }} (Cap: {{ $mesa->capacidad }}) - {{ $mesa->ubicacion ?: 'General' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" name="fecha" class="form-control" value="{{ old('fecha', date('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label>Hora</label>
                    <input type="time" name="hora" class="form-control" value="{{ old('hora') }}" required>
                </div>
            </div>
            <div class="form-group">
                <label>Observaciones (opcional)</label>
                <textarea name="observaciones" class="form-control" placeholder="Notas adicionales, alergias, celebraciones, etc.">{{ old('observaciones') }}</textarea>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;">
                    <i class="fas fa-save"></i> Crear Reserva
                </button>
                <button type="button" class="btn btn-outline" onclick="cerrarModal('modalCrear')">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Reserva -->
<div class="modal-overlay" id="modalEditar">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-pen"></i> Editar Reserva <span id="editReservaId" style="color: var(--accent-primary);"></span></h2>
            <button class="modal-close" onclick="cerrarModal('modalEditar')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formEditar" method="POST">
            @csrf
            @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre_persona" id="edit_nombre_persona" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Número de Documento</label>
                    <input type="text" name="numero_documento" id="edit_numero_documento" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" id="edit_telefono" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Cantidad de Personas</label>
                    <input type="number" name="cantidad_personas" id="edit_cantidad_personas" class="form-control" min="1" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Mesa</label>
                    <select name="mesa_id" id="edit_mesa_id" class="form-control" required>
                        @foreach($mesas as $mesa)
                            <option value="{{ $mesa->id }}">
                                {{ $mesa->nombre }} (Cap: {{ $mesa->capacidad }}) - {{ $mesa->ubicacion ?: 'General' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Estado de Pago</label>
                    <select name="estado_pago" id="edit_estado_pago" class="form-control" required>
                        <option value="pendiente">Pendiente</option>
                        <option value="pagada">Pagada</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" name="fecha" id="edit_fecha" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Hora</label>
                    <input type="time" name="hora" id="edit_hora" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label>Observaciones (opcional)</label>
                <textarea name="observaciones" id="edit_observaciones" class="form-control"></textarea>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-success" style="flex:1;">
                    <i class="fas fa-save"></i> Actualizar Reserva
                </button>
                <button type="button" class="btn btn-outline" onclick="cerrarModal('modalEditar')">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function abrirModalCrear() {
    document.getElementById('modalCrear').classList.add('show');
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('show');
}

function editarReserva(reserva) {
    document.getElementById('formEditar').action = '/reservas/' + reserva.id;
    document.getElementById('editReservaId').textContent = '#' + reserva.id;
    document.getElementById('edit_nombre_persona').value = reserva.nombre_persona;
    document.getElementById('edit_numero_documento').value = reserva.numero_documento;
    document.getElementById('edit_telefono').value = reserva.telefono;
    document.getElementById('edit_cantidad_personas').value = reserva.cantidad_personas;
    document.getElementById('edit_mesa_id').value = reserva.mesa_id;
    document.getElementById('edit_fecha').value = reserva.fecha.split('T')[0];
    document.getElementById('edit_hora').value = reserva.hora;
    document.getElementById('edit_estado_pago').value = reserva.estado_pago;
    document.getElementById('edit_observaciones').value = reserva.observaciones || '';
    document.getElementById('modalEditar').classList.add('show');
}

// Cerrar modales al hacer click fuera
document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
        }
    });
});

// Si hay errores de validación, abrir el modal de crear
@if($errors->any() && old('nombre_persona'))
    abrirModalCrear();
@endif
</script>
@endsection
