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
        Schema::create('socios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('codigo')->unique()->index(); // Código de barras o QR para acceso rápido
            $table->string('nombre');
            $table->string('apellido');
            $table->string('dni')->unique()->index();
            $table->string('correo')->unique()->index();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->string('contacto_emergencia')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('foto')->nullable();
            $table->string('estado')->default('activo')->index(); // 'activo', 'inactivo', 'moroso'
            $table->text('observaciones_medicas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socios');
    }
};
