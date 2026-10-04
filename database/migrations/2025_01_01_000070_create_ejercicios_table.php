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
        Schema::create('ejercicios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('grupo_muscular')->index(); // 'pecho', 'espalda', 'piernas', 'hombros', 'brazos', 'core', 'cardio'
            $table->text('descripcion')->nullable();
            $table->string('equipo_necesario')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ejercicios');
    }
};
