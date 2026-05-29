<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->constrained('mesas')->onDelete('cascade');
            $table->string('nombre_persona');
            $table->string('numero_documento');
            $table->string('telefono');
            $table->integer('cantidad_personas');
            $table->date('fecha');
            $table->time('hora');
            $table->enum('estado_pago', ['pendiente', 'pagada'])->default('pendiente');
            $table->enum('estado_reserva', ['activa', 'cancelada'])->default('activa');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Restricción única: no pueden existir dos reservas activas para la misma mesa, fecha y hora
            $table->unique(['mesa_id', 'fecha', 'hora', 'estado_reserva'], 'reserva_unica');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
