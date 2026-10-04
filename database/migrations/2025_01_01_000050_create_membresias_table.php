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
        Schema::create('membresias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')
                ->constrained('socios')
                ->cascadeOnDelete();
            $table->foreignId('plan_id')
                ->constrained('planes')
                ->cascadeOnDelete();
            $table->date('fecha_inicio')->index();
            $table->date('fecha_fin')->index();
            $table->decimal('precio_pagado', 10, 2);
            $table->string('estado')->default('activa')->index(); // 'activa', 'vencida', 'cancelada'
            $table->string('metodo_pago')->default('efectivo'); // 'efectivo', 'tarjeta', 'transferencia'
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['socio_id', 'estado']);
            $table->index(['fecha_fin', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membresias');
    }
};
