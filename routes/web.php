<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Cliente;
use App\Http\Controllers\Api;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de autenticación generadas por Breeze
require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ===== RUTAS DE ADMINISTRADOR =====
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Mesas
    Route::resource('mesas', Admin\MesaController::class)->except(['create', 'show', 'edit']);
    Route::patch('/mesas/{mesa}/toggle-activa', [Admin\MesaController::class, 'toggleActiva'])->name('mesas.toggleActiva');

    // Reservas
    Route::resource('reservas', Admin\ReservaController::class)->except(['create', 'show', 'edit']);
    Route::patch('/reservas/{reserva}/cancelar', [Admin\ReservaController::class, 'cancelar'])->name('reservas.cancelar');
    Route::patch('/reservas/{reserva}/toggle-pago', [Admin\ReservaController::class, 'togglePago'])->name('reservas.togglePago');
    Route::get('/reservas/{reserva}/factura', [Admin\ReservaController::class, 'facturaPdf'])->name('reservas.factura');

    // Zonas
    Route::resource('zonas', Admin\ZonaController::class)->except(['create', 'show', 'edit']);
    
    // KDS y Menu Admin
    Route::get('/kds', [Admin\PedidoController::class, 'live'])->name('pedidos.live');
    Route::patch('/pedidos/{pedido}/estado', [Admin\PedidoController::class, 'updateEstado'])->name('pedidos.updateEstado');
});

// ===== RUTAS DE CLIENTE =====
Route::middleware(['auth', 'role:cliente'])->prefix('cliente')->name('cliente.')->group(function () {
    Route::get('/mapa', [Cliente\MapaController::class, 'index'])->name('mapa');
    Route::get('/reservar/{mesa}', [Cliente\ReservaController::class, 'create'])->name('reservar');
    Route::post('/reservar', [Cliente\ReservaController::class, 'store'])->name('reservar.store');
    Route::get('/mis-reservas', [Cliente\ReservaController::class, 'index'])->name('mis-reservas');
    Route::patch('/reservas/{reserva}/cancelar', [Cliente\ReservaController::class, 'cancelar'])->name('reservas.cancelar');

    // Menú y Pedidos
    Route::get('/menu', [Cliente\MenuController::class, 'index'])->name('menu');
    Route::post('/pedidos', [Cliente\MenuController::class, 'store'])->name('pedidos.store');
});

// ===== RUTAS API (INTERNAS) =====
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/mesas/disponibilidad', [Api\MesaApiController::class, 'disponibilidad']);
    Route::get('/mesas/{mesa}/horas-disponibles', [Api\MesaApiController::class, 'horasDisponibles']);
    
    Route::post('/mesas/{mesa}/hold', [Api\MesaApiController::class, 'hold']);
    Route::delete('/mesas/{mesa}/hold', [Api\MesaApiController::class, 'releaseHold']);
    
    // Solo admin puede mover mesas
    Route::put('/mesas/{mesa}/position', [Api\MesaApiController::class, 'updatePosition'])->middleware('role:admin');
});
