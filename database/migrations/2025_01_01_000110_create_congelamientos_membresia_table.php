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
        Schema::create('congelamientos_membresia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membresia_id')
                ->constrained('membresias')
                ->cascadeOnDelete();
            $table->foreignId('socio_id')
                ->constrained('socios')
                ->cascadeOnDelete();
            $table->date('fecha_inicio')->index();
            $table->date('fecha_fin_planificada')->nullable();
            $table->date('fecha_reanudacion')->nullable();
            $table->unsignedInteger('dias_congelados')->default(0);
            $table->string('motivo')->default('vacaciones'); // 'vacaciones', 'medico', 'laboral', 'otro'
            $table->string('documento_soporte')->nullable();
            $table->string('estado')->default('activo')->index(); // 'activo', 'finalizado', 'cancelado'
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['membresia_id', 'estado']);
            $table->index(['socio_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('congelamientos_membresia');
    }
};
