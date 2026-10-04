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
        Schema::create('ejercicio_rutina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rutina_id')
                ->constrained('rutinas')
                ->cascadeOnDelete();
            $table->foreignId('ejercicio_id')
                ->constrained('ejercicios')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('orden')->default(1);
            $table->unsignedSmallInteger('series')->default(3);
            $table->string('repeticiones')->default('10-12');
            $table->unsignedSmallInteger('descanso_segundos')->default(60);
            $table->string('notas')->nullable();
            $table->timestamps();

            $table->unique(['rutina_id', 'ejercicio_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ejercicio_rutina');
    }
};
