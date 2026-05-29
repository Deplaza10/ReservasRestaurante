<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('mesa_id')
                  ->constrained('users')->nullOnDelete();
            $table->time('hora_fin')->nullable()->after('hora');
            $table->timestamp('confirmed_at')->nullable()->after('observaciones');
        });

        // Expandir el enum estado_reserva para incluir 'completada' y 'no_show'
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE reservas MODIFY COLUMN estado_reserva ENUM('activa', 'cancelada', 'completada', 'no_show') DEFAULT 'activa'");
        }

        // Renombrar 'hora' a 'hora_inicio' para claridad
        Schema::table('reservas', function (Blueprint $table) {
            $table->renameColumn('hora', 'hora_inicio');
        });

        // Agregar índices para consultas de disponibilidad
        Schema::table('reservas', function (Blueprint $table) {
            $table->index(['mesa_id', 'fecha', 'estado_reserva'], 'idx_disponibilidad');
            $table->index(['user_id', 'estado_reserva'], 'idx_user_reservas');
            $table->index(['fecha', 'estado_reserva'], 'idx_fecha_estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropIndex('idx_disponibilidad');
            $table->dropIndex('idx_user_reservas');
            $table->dropIndex('idx_fecha_estado');
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->renameColumn('hora_inicio', 'hora');
        });

        DB::statement("ALTER TABLE reservas MODIFY COLUMN estado_reserva ENUM('activa', 'cancelada') DEFAULT 'activa'");

        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'hora_fin', 'confirmed_at']);
        });
    }
};
