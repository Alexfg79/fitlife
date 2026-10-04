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
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')
                ->constrained('socios')
                ->cascadeOnDelete();
            $table->foreignId('registrado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('fecha_hora')->index();
            $table->string('metodo')->default('codigo'); // 'qr', 'codigo', 'biometrico', 'manual'
            $table->string('estado_acceso')->default('permitido')->index(); // 'permitido', 'denegado_vencido', 'denegado_inactivo'
            $table->string('mensaje_acceso')->nullable();
            $table->timestamps();

            $table->index(['socio_id', 'fecha_hora']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
