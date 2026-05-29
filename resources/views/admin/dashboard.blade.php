@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-chart-pie"></i> Panel de Control (Admin)</h1>
    <p>Bienvenido al sistema de gestión de reservas del restaurante</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $reservasActivas }}</h3>
            <p>Reservas Activas</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $pagadas }}</h3>
            <p>Pagadas</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon gold">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $reservasHoy }}</h3>
            <p>Reservas de Hoy</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-chair"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $mesasCount }}</h3>
            <p>Mesas Activas</p>
        </div>
    </div>
</div>

<!-- Quick Access -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
    <div class="glass-card">
        <div class="card-title">
            <i class="fas fa-calendar-plus"></i> Acciones Rápidas
        </div>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <a href="{{ route('admin.reservas.index') }}" class="btn btn-primary" style="width:100%;">
                <i class="fas fa-list"></i> Ver Todas las Reservas
            </a>
            <a href="{{ route('admin.mesas.index') }}" class="btn btn-outline" style="width:100%;">
                <i class="fas fa-chair"></i> Gestionar Mesas y Plano
            </a>
            <a href="{{ route('admin.zonas.index') }}" class="btn btn-outline" style="width:100%;">
                <i class="fas fa-layer-group"></i> Gestionar Zonas
            </a>
        </div>
    </div>
    
    <div class="glass-card">
        <div class="card-title">
            <i class="fas fa-calendar-day"></i> Reservas Recientes
        </div>
        @if($reservasRecientes->count() > 0)
            @foreach($reservasRecientes as $r)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                    <div>
                        <div style="font-weight: 600; font-size: 0.9rem;">{{ $r->nombre_persona }}</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            {{ $r->mesa->nombre }} · {{ \Carbon\Carbon::parse($r->fecha)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($r->hora_inicio)->format('h:i A') }}
                        </div>
                    </div>
                    <span class="badge {{ $r->estado_pago === 'pagada' ? 'badge-success' : 'badge-warning' }}">
                        <span class="pulse-dot {{ $r->estado_pago === 'pagada' ? 'green' : 'gold' }}"></span>
                        {{ ucfirst($r->estado_pago) }}
                    </span>
                </div>
            @endforeach
        @else
            <div class="empty-state" style="padding: 2rem 0;">
                <i class="fas fa-calendar-xmark"></i>
                <h3>No hay reservas recientes</h3>
            </div>
        @endif
    </div>
</div>
@endsection
