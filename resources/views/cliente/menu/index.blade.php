@extends('layouts.app')

@section('title', 'Menú Digital')

@section('content')
<div class="page-header" style="text-align: center; margin-bottom: 3rem;">
    <h1 style="justify-content: center; font-size: 3rem;"><i class="fas fa-utensils text-rose-500"></i> Nuestro Menú</h1>
    <p>Descubre nuestra exquisita selección y pide desde tu mesa.</p>
</div>

<div x-data="cart()" class="menu-container" style="display: flex; gap: 2rem; position: relative;">
    
    <!-- Catálogo de Productos -->
    <div style="flex: 1;">
        @foreach($productos as $categoria => $items)
            <div class="mb-10">
                <h2 style="font-size: 1.8rem; font-weight: 700; color: white; margin-bottom: 1.5rem; border-bottom: 2px solid var(--accent-primary); display: inline-block; padding-bottom: 0.5rem;">
                    {{ $categoria }}
                </h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                    @foreach($items as $item)
                        <div class="glass-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; height: 100%;">
                            <div style="height: 200px; overflow: hidden; position: relative;">
                                <img src="{{ $item->imagen_url }}" alt="{{ $item->nombre }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;" class="hover:scale-110">
                                <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 5px 12px; border-radius: 20px; font-weight: 700;">
                                    ${{ number_format($item->precio, 2) }}
                                </div>
                            </div>
                            <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
                                <h3 style="font-size: 1.25rem; font-weight: 700; color: white; margin-bottom: 0.5rem;">{{ $item->nombre }}</h3>
                                <p style="color: var(--text-muted); font-size: 0.9rem; flex: 1;">{{ $item->descripcion }}</p>
                                
                                <button @click="addToCart({{ $item->id }}, '{{ $item->nombre }}', {{ $item->precio }})" class="btn btn-outline" style="width: 100%; margin-top: 1rem; justify-content: center;">
                                    <i class="fas fa-cart-plus"></i> Añadir al Pedido
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <!-- Sidebar Carrito Flotante -->
    <div class="glass-card" style="width: 380px; position: sticky; top: 90px; align-self: flex-start; max-height: calc(100vh - 120px); display: flex; flex-direction: column;" x-show="items.length > 0" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
        <div class="card-title" style="margin-bottom: 1rem;">
            <i class="fas fa-shopping-basket"></i> Tu Pedido
        </div>
        
        <div style="flex: 1; overflow-y: auto; padding-right: 5px;" class="cart-items">
            <template x-for="(item, index) in items" :key="item.id">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding: 1rem 0;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: white;" x-text="item.nombre"></div>
                        <div style="color: var(--text-muted); font-size: 0.85rem;">$<span x-text="item.precio.toFixed(2)"></span></div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.3); padding: 4px 8px; border-radius: 8px;">
                        <button @click="decreaseQty(index)" style="color: var(--text-secondary); padding: 0 5px;"><i class="fas fa-minus text-xs"></i></button>
                        <span style="font-weight: 700; width: 20px; text-align: center;" x-text="item.cantidad"></span>
                        <button @click="increaseQty(index)" style="color: var(--accent-primary); padding: 0 5px;"><i class="fas fa-plus text-xs"></i></button>
                    </div>
                </div>
            </template>
        </div>

        <div style="margin-top: 1.5rem; border-top: 2px dashed var(--border-color); padding-top: 1.5rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 1.25rem;">
                <span style="color: var(--text-secondary);">Total:</span>
                <span style="font-weight: 700; color: white;">$<span x-text="total.toFixed(2)"></span></span>
            </div>
            
            <button @click="enviarPedido" :disabled="loading" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1.1rem;">
                <span x-show="!loading"><i class="fas fa-check-circle"></i> Confirmar Pedido</span>
                <span x-show="loading"><i class="fas fa-spinner fa-spin"></i> Enviando a Cocina...</span>
            </button>
        </div>
    </div>
    
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('cart', () => ({
        items: [],
        loading: false,
        
        get total() {
            return this.items.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
        },
        
        addToCart(id, nombre, precio) {
            const existing = this.items.find(i => i.id === id);
            if (existing) {
                existing.cantidad++;
            } else {
                this.items.push({ id, nombre, precio, cantidad: 1 });
            }
        },
        
        increaseQty(index) {
            this.items[index].cantidad++;
        },
        
        decreaseQty(index) {
            if (this.items[index].cantidad > 1) {
                this.items[index].cantidad--;
            } else {
                this.items.splice(index, 1);
            }
        },
        
        enviarPedido() {
            if(this.items.length === 0) return;
            
            this.loading = true;
            
            fetch('{{ route('cliente.pedidos.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    items: this.items,
                    total: this.total
                })
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if(data.success) {
                    alert('Pedido enviado a la cocina. ¡En breve estará en tu mesa!');
                    this.items = [];
                }
            })
            .catch(err => {
                this.loading = false;
                alert('Error al enviar pedido');
            });
        }
    }))
})
</script>
@endsection
