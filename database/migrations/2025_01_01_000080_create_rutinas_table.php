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
        Schema::create('rutinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')
                ->nullable()
                ->constrained('socios')
                ->cascadeOnDelete();
            $table->foreignId('creada_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('nivel')->default('principiante'); // 'principiante', 'intermedio', 'avanzado'
            $table->json('dias_semana')->nullable(); // ["lunes", "miercoles", "viernes"]
            $table->boolean('activa')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutinas');
    }
};
