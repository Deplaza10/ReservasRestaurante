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
        Schema::table('mesas', function (Blueprint $table) {
            $table->foreignId('zona_id')->nullable()->after('activa')
                  ->constrained('zonas')->nullOnDelete();
            $table->enum('tipo_mesa', ['redonda', 'cuadrada', 'rectangular', 'barra'])
                  ->default('cuadrada')->after('zona_id');
            $table->float('pos_x')->default(100)->after('tipo_mesa');
            $table->float('pos_y')->default(100)->after('pos_x');
            $table->float('ancho')->default(80)->after('pos_y');
            $table->float('alto')->default(80)->after('ancho');
            $table->float('rotacion')->default(0)->after('alto');
            $table->integer('piso')->default(1)->after('rotacion');
            $table->integer('numero_mesa')->nullable()->after('piso');

            $table->index(['zona_id', 'piso', 'activa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            $table->dropForeign(['zona_id']);
            $table->dropIndex(['zona_id', 'piso', 'activa']);
            $table->dropColumn([
                'zona_id', 'tipo_mesa', 'pos_x', 'pos_y',
                'ancho', 'alto', 'rotacion', 'piso', 'numero_mesa'
            ]);
        });
    }
};
