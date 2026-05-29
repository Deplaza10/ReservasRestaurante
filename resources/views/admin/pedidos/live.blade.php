@extends('layouts.app')

@section('title', 'KDS - Cocina en Vivo')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1><i class="fas fa-fire-burner"></i> Cocina (KDS)</h1>
        <p>Pedidos en tiempo real sincronizados con el restaurante.</p>
    </div>
    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: 8px 16px; border-radius: 20px; color: #34d399; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <div class="w-3 h-3 bg-emerald-500 rounded-full" style="animation: pulse 2s infinite;"></div> LIVE
    </div>
</div>

<div x-data="kds()" class="kanban-board" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; min-height: 60vh;">
    
    <!-- Columna Pendientes -->
    <div class="glass-card" style="background: rgba(15, 23, 42, 0.8);">
        <div class="card-title" style="border-bottom-color: var(--accent-primary);">
            <i class="fas fa-bell text-rose-400"></i> Nuevos / Pendientes 
            <span class="badge" style="margin-left: auto; background: var(--accent-primary);" x-text="pendientes.length">0</span>
        </div>
        <div class="kanban-col" style="display: flex; flex-direction: column; gap: 1rem;">
            <template x-for="pedido in pendientes" :key="pedido.id">
                <div class="pedido-card" style="background: rgba(30, 41, 59, 0.9); border: 1px solid var(--border-color); border-left: 4px solid var(--accent-primary); border-radius: var(--radius-sm); padding: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="font-weight: 700; color: white;">#<span x-text="pedido.id"></span> - <span x-text="pedido.user.name"></span></span>
                        <span style="font-size: 0.8rem; color: var(--text-muted);" x-text="timeAgo(pedido.created_at)"></span>
                    </div>
                    <ul style="margin: 10px 0; padding-left: 15px; color: var(--text-secondary); font-size: 0.95rem; list-style-type: disc;">
                        <template x-for="item in pedido.items" :key="item.id">
                            <li><strong x-text="item.cantidad"></strong>x <span x-text="item.producto.nombre"></span></li>
                        </template>
                    </ul>
                    <button @click="updateEstado(pedido.id, 'preparando')" class="btn btn-outline" style="width: 100%; margin-top: 10px; border-color: var(--accent-primary); color: var(--accent-primary);">
                        <i class="fas fa-fire"></i> Empezar Preparación
                    </button>
                </div>
            </template>
            <div x-show="pendientes.length === 0" style="text-align: center; padding: 2rem; color: var(--text-muted);">Sin pedidos nuevos.</div>
        </div>
    </div>

    <!-- Columna Preparando -->
    <div class="glass-card" style="background: rgba(15, 23, 42, 0.8);">
        <div class="card-title" style="border-bottom-color: #f59e0b;">
            <i class="fas fa-fire-burner text-amber-400"></i> En Preparación
            <span class="badge badge-warning" style="margin-left: auto;" x-text="preparando.length">0</span>
        </div>
        <div class="kanban-col" style="display: flex; flex-direction: column; gap: 1rem;">
            <template x-for="pedido in preparando" :key="pedido.id">
                <div class="pedido-card" style="background: rgba(30, 41, 59, 0.9); border: 1px solid var(--border-color); border-left: 4px solid #f59e0b; border-radius: var(--radius-sm); padding: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="font-weight: 700; color: white;">#<span x-text="pedido.id"></span> - <span x-text="pedido.user.name"></span></span>
                    </div>
                    <ul style="margin: 10px 0; padding-left: 15px; color: var(--text-secondary); font-size: 0.95rem; list-style-type: disc;">
                        <template x-for="item in pedido.items" :key="item.id">
                            <li><strong x-text="item.cantidad"></strong>x <span x-text="item.producto.nombre"></span></li>
                        </template>
                    </ul>
                    <button @click="updateEstado(pedido.id, 'listo')" class="btn btn-success" style="width: 100%; margin-top: 10px;">
                        <i class="fas fa-check-double"></i> Marcar Listo
                    </button>
                </div>
            </template>
            <div x-show="preparando.length === 0" style="text-align: center; padding: 2rem; color: var(--text-muted);">Nada en preparación.</div>
        </div>
    </div>

    <!-- Columna Listos -->
    <div class="glass-card" style="background: rgba(15, 23, 42, 0.8);">
        <div class="card-title" style="border-bottom-color: #10b981;">
            <i class="fas fa-concierge-bell text-emerald-400"></i> Listos para Servir
        </div>
        <div class="kanban-col" style="display: flex; flex-direction: column; gap: 1rem;">
            <template x-for="pedido in listos" :key="pedido.id">
                <div class="pedido-card" style="background: rgba(30, 41, 59, 0.9); border: 1px solid var(--border-color); border-left: 4px solid #10b981; border-radius: var(--radius-sm); padding: 1rem; opacity: 0.8;">
                    <div style="font-weight: 700; color: white; margin-bottom: 10px;">#<span x-text="pedido.id"></span> - <span x-text="pedido.user.name"></span></div>
                    <button @click="updateEstado(pedido.id, 'entregado')" class="btn btn-outline" style="width: 100%;">
                        <i class="fas fa-truck"></i> Entregado al Cliente
                    </button>
                </div>
            </template>
            <div x-show="listos.length === 0" style="text-align: center; padding: 2rem; color: var(--text-muted);">Sin pedidos en espera.</div>
        </div>
    </div>
    
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('kds', () => ({
        todosPedidos: @json($pedidos),
        listos: [],
        
        get pendientes() {
            return this.todosPedidos.filter(p => p.estado === 'pendiente');
        },
        get preparando() {
            return this.todosPedidos.filter(p => p.estado === 'preparando');
        },
        
        init() {
            // Escuchar WebSockets con Laravel Echo
            if (window.Echo) {
                window.Echo.channel('kds-orders')
                    .listen('NuevoPedido', (e) => {
                        console.log('Nuevo pedido recibido:', e.pedido);
                        this.todosPedidos.push(e.pedido);
                        // Opcional: Sonido de notificación
                        let audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                        audio.play().catch(e => {});
                    });
            }
        },
        
        updateEstado(id, nuevoEstado) {
            fetch(`/admin/pedidos/${id}/estado`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ estado: nuevoEstado })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    if (nuevoEstado === 'listo') {
                        // Lo pasamos a listos localmente
                        const idx = this.todosPedidos.findIndex(p => p.id === id);
                        const p = this.todosPedidos.splice(idx, 1)[0];
                        p.estado = 'listo';
                        this.listos.push(p);
                    } else if (nuevoEstado === 'entregado') {
                        const idx = this.listos.findIndex(p => p.id === id);
                        this.listos.splice(idx, 1);
                    } else {
                        const pedido = this.todosPedidos.find(p => p.id === id);
                        if(pedido) pedido.estado = nuevoEstado;
                    }
                }
            });
        },
        
        timeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / 60000);
            if (diffInMinutes < 1) return 'Hace un momento';
            return `Hace ${diffInMinutes} min`;
        }
    }))
})
</script>
@endsection
