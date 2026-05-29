@extends('layouts.app')

@section('title', 'Gestión de Mesas')

@section('content')
<div class="page-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1><i class="fas fa-chair"></i> Gestión de Mesas</h1>
        <p>Administra las mesas del restaurante</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('modalCrearMesa').classList.add('show')">
        <i class="fas fa-plus"></i> Nueva Mesa
    </button>
</div>

<!-- Grid de mesas -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem;">
    @forelse($mesas as $mesa)
        <div class="glass-card" style="position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 4px;">
                        <i class="fas fa-chair" style="color: var(--accent-primary); margin-right: 6px;"></i>
                        {{ $mesa->nombre }}
                    </h3>
                    <span class="badge {{ $mesa->activa ? 'badge-success' : 'badge-danger' }}">
                        <span class="pulse-dot {{ $mesa->activa ? 'green' : 'red' }}"></span>
                        {{ $mesa->activa ? 'Activa' : 'Inactiva' }}
                    </span>
                </div>
                <div class="btn-group">
                    <button class="btn btn-outline btn-icon btn-sm"
                        onclick="editarMesa({{ $mesa->id }}, '{{ $mesa->nombre }}', {{ $mesa->capacidad }}, '{{ $mesa->ubicacion }}')"
                        title="Editar">
                        <i class="fas fa-pen"></i>
                    </button>
                    <form action="{{ route('mesas.destroy', $mesa) }}" method="POST" style="display:inline;"
                        onsubmit="return confirm('¿Seguro que deseas eliminar esta mesa?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-icon btn-sm" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div style="margin-top: 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div>
                    <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Capacidad</div>
                    <div style="font-size: 1.1rem; font-weight: 700; color: var(--accent-gold);">
                        <i class="fas fa-users" style="font-size: 0.85rem;"></i> {{ $mesa->capacidad }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Ubicación</div>
                    <div style="font-size: 0.9rem; font-weight: 500; color: var(--text-secondary);">
                        <i class="fas fa-map-pin" style="font-size: 0.8rem;"></i> {{ $mesa->ubicacion ?: 'General' }}
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="glass-card" style="grid-column: 1 / -1;">
            <div class="empty-state">
                <i class="fas fa-chair"></i>
                <h3>No hay mesas registradas</h3>
                <p>Crea la primera mesa para empezar a recibir reservas</p>
            </div>
        </div>
    @endforelse
</div>

<!-- Modal Crear Mesa -->
<div class="modal-overlay" id="modalCrearMesa">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-plus-circle"></i> Nueva Mesa</h2>
            <button class="modal-close" onclick="document.getElementById('modalCrearMesa').classList.remove('show')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('mesas.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nombre de la Mesa</label>
                <input type="text" name="nombre" class="form-control" placeholder="Ej: Mesa 1, Mesa VIP" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Capacidad (personas)</label>
                    <input type="number" name="capacidad" class="form-control" min="1" max="50" placeholder="4" required>
                </div>
                <div class="form-group">
                    <label>Ubicación</label>
                    <select name="ubicacion" class="form-control">
                        <option value="">Seleccionar...</option>
                        <option value="Interior">Interior</option>
                        <option value="Terraza">Terraza</option>
                        <option value="Jardín">Jardín</option>
                        <option value="VIP">Salón VIP</option>
                        <option value="Barra">Barra</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;">
                    <i class="fas fa-save"></i> Guardar Mesa
                </button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalCrearMesa').classList.remove('show')">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Mesa -->
<div class="modal-overlay" id="modalEditarMesa">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-pen"></i> Editar Mesa</h2>
            <button class="modal-close" onclick="document.getElementById('modalEditarMesa').classList.remove('show')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formEditarMesa" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nombre de la Mesa</label>
                <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Capacidad (personas)</label>
                    <input type="number" name="capacidad" id="edit_capacidad" class="form-control" min="1" max="50" required>
                </div>
                <div class="form-group">
                    <label>Ubicación</label>
                    <select name="ubicacion" id="edit_ubicacion" class="form-control">
                        <option value="">Seleccionar...</option>
                        <option value="Interior">Interior</option>
                        <option value="Terraza">Terraza</option>
                        <option value="Jardín">Jardín</option>
                        <option value="VIP">Salón VIP</option>
                        <option value="Barra">Barra</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-success" style="flex:1;">
                    <i class="fas fa-save"></i> Actualizar Mesa
                </button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalEditarMesa').classList.remove('show')">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function editarMesa(id, nombre, capacidad, ubicacion) {
    document.getElementById('formEditarMesa').action = '/mesas/' + id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_capacidad').value = capacidad;
    document.getElementById('edit_ubicacion').value = ubicacion;
    document.getElementById('modalEditarMesa').classList.add('show');
}

// Cerrar modales al hacer click fuera
document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
        }
    });
});
</script>
@endsection
