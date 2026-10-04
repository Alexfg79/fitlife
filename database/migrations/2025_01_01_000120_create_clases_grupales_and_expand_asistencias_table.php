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
        Schema::create('clases_grupales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: 'Spinning RPM', 'Power Yoga', 'CrossFit WOD'
            $table->text('descripcion')->nullable();
            $table->foreignId('entrenador_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('horario'); // Ej: 'Lunes y Miércoles 18:00 - 19:00'
            $table->unsignedInteger('cupo_maximo')->default(20);
            $table->string('sala')->default('Sala Principal');
            $table->boolean('activa')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('asistencias', function (Blueprint $table) {
            $table->timestamp('hora_salida')->nullable()->after('fecha_hora');
            $table->unsignedInteger('tiempo_permanencia_minutos')->nullable()->after('hora_salida');
            $table->foreignId('clase_id')
                ->nullable()
                ->after('registrado_por')
                ->constrained('clases_grupales')
                ->nullOnDelete();
            $table->string('tipo_acceso')->default('general')->index()->after('metodo'); // 'general', 'clase_grupal'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropForeign(['clase_id']);
            $table->dropColumn([
                'hora_salida',
                'tiempo_permanencia_minutos',
                'clase_id',
                'tipo_acceso',
            ]);
        });

        Schema::dropIfExists('clases_grupales');
    }
};
