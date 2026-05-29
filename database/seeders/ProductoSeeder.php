<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = [
            // Entradas
            ['nombre' => 'Tartar de Salmón', 'descripcion' => 'Con aguacate, alcaparras y un toque de cítricos.', 'precio' => 15.00, 'categoria' => 'Entradas', 'imagen_url' => 'https://images.unsplash.com/photo-1599084942896-6756f17e3f5b?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'],
            ['nombre' => 'Burrata Trufada', 'descripcion' => 'Queso burrata fresco con aceite de trufa y tomates cherry asados.', 'precio' => 18.00, 'categoria' => 'Entradas', 'imagen_url' => 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'],
            
            // Platos Fuertes
            ['nombre' => 'Filet Mignon', 'descripcion' => 'Corte premium servido con puré rústico y reducción de vino tinto.', 'precio' => 45.00, 'categoria' => 'Platos Fuertes', 'imagen_url' => 'https://images.unsplash.com/photo-1544025162-8111f42d2a45?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'],
            ['nombre' => 'Risotto de Setas', 'descripcion' => 'Cremoso risotto con mezcla de setas silvestres y queso parmesano.', 'precio' => 28.00, 'categoria' => 'Platos Fuertes', 'imagen_url' => 'https://images.unsplash.com/photo-1633337474564-1d84873eb36f?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'],
            ['nombre' => 'Salmón Glaseado', 'descripcion' => 'Salmón fresco con glaseado de miel y mostaza sobre cama de espárragos.', 'precio' => 32.00, 'categoria' => 'Platos Fuertes', 'imagen_url' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'],

            // Bebidas
            ['nombre' => 'Vino Tinto Reserva', 'descripcion' => 'Copa de vino tinto reserva, variedad Cabernet Sauvignon.', 'precio' => 12.00, 'categoria' => 'Bebidas', 'imagen_url' => 'https://images.unsplash.com/photo-1506377247377-2a5b3b417ebb?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'],
            ['nombre' => 'Cóctel de Frutos Rojos', 'descripcion' => 'Refrescante mezcla de frutos rojos con ginebra y tónica.', 'precio' => 14.00, 'categoria' => 'Bebidas', 'imagen_url' => 'https://images.unsplash.com/photo-1536935338788-846bb9981813?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'],

            // Postres
            ['nombre' => 'Coulant de Chocolate', 'descripcion' => 'Volcán de chocolate caliente con centro líquido y helado de vainilla.', 'precio' => 10.00, 'categoria' => 'Postres', 'imagen_url' => 'https://images.unsplash.com/photo-1624353365286-3f8d62daad51?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'],
            ['nombre' => 'Cheesecake de Frambuesa', 'descripcion' => 'Suave tarta de queso con coulis de frambuesa natural.', 'precio' => 11.00, 'categoria' => 'Postres', 'imagen_url' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'],
        ];

        foreach ($productos as $producto) {
            \App\Models\Producto::create($producto);
        }
    }
}
