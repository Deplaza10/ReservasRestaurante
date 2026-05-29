@extends('layouts.app')

@section('title', 'Completar Reserva')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-calendar-check"></i> Completar Reserva</h1>
    <p>Rellena tus datos para confirmar la reserva de tu mesa.</p>
</div>

<div class="form-row" style="gap: 2rem; align-items: start;">
    <div class="glass-card" style="flex: 2;">
        <div class="card-title">
            <i class="fas fa-user-edit"></i> Datos del Titular
        </div>
        
        <form action="{{ route('cliente.reservar.store') }}" method="POST">
            @csrf
            <input type="hidden" name="mesa_id" value="{{ $mesa->id }}">
            <input type="hidden" name="fecha" value="{{ request('fecha') }}">
            <input type="hidden" name="hora_inicio" value="{{ request('hora_inicio') }}">
            <input type="hidden" name="hora_fin" value="{{ request('hora_fin') }}">

            @if($errors->any())
                <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem;">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-row">
                <div class="form-group">
                    <label>Nombre Completo *</label>
                    <input type="text" name="nombre_persona" class="form-control" value="{{ old('nombre_persona', Auth::user()->name) }}" required>
                </div>
                <div class="form-group">
                    <label>Documento de Identidad *</label>
                    <input type="text" name="numero_documento" class="form-control" value="{{ old('numero_documento') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Teléfono de Contacto *</label>
                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}" required>
                </div>
                <div class="form-group">
                    <label>Cantidad de Personas *</label>
                    <input type="number" name="cantidad_personas" class="form-control" min="1" max="{{ $mesa->capacidad }}" value="{{ old('cantidad_personas', 2) }}" required>
                    <small style="color: var(--text-muted); font-size: 0.75rem;">Máximo {{ $mesa->capacidad }} personas para esta mesa</small>
                </div>
            </div>

            <div class="form-group">
                <label>Observaciones o Peticiones Especiales</label>
                <textarea name="observaciones" class="form-control" placeholder="Ej: Aniversario, alergias, silla para bebé, etc.">{{ old('observaciones') }}</textarea>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 15px; font-size: 1rem;">
                    <i class="fas fa-check"></i> Confirmar Reserva
                </button>
                <a href="{{ route('cliente.mapa') }}?fecha={{ request('fecha') }}&hora_inicio={{ request('hora_inicio') }}&hora_fin={{ request('hora_fin') }}" class="btn btn-outline" style="padding: 15px;">
                    Volver
                </a>
            </div>
        </form>
    </div>
    
    <div class="glass-card" style="flex: 1; position: sticky; top: 90px;">
        <div class="card-title">
            <i class="fas fa-receipt"></i> Resumen
        </div>
        
        <div style="background: rgba(15,15,26,0.6); padding: 1.25rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: var(--text-muted);">Mesa:</span>
                <span style="font-weight: 700; color: white;">{{ $mesa->nombre }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: var(--text-muted);">Zona:</span>
                <span style="font-weight: 700; color: white;">{{ $mesa->zona->nombre ?? 'Principal' }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: var(--text-muted);">Capacidad:</span>
                <span style="font-weight: 700; color: white;">{{ $mesa->capacidad }} personas</span>
            </div>
            
            <hr style="border-color: var(--border-color); margin: 15px 0;">
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: var(--text-muted);">Fecha:</span>
                <span style="font-weight: 700; color: white;">{{ \Carbon\Carbon::parse(request('fecha'))->format('d/m/Y') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">Horario:</span>
                <span style="font-weight: 700; color: var(--accent-primary);">{{ request('hora_inicio') }} - {{ request('hora_fin') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
