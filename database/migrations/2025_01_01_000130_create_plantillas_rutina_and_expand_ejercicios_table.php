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
        Schema::table('ejercicios', function (Blueprint $table) {
            $table->string('dificultad')->default('intermedio')->index()->after('grupo_muscular'); // 'principiante', 'intermedio', 'avanzado'
            $table->string('equipamiento')->default('peso_corporal')->index()->after('dificultad'); // 'mancuernas', 'barra', 'polea', 'peso_corporal', 'maquina', 'kettlebell'
        });

        Schema::table('ejercicio_rutina', function (Blueprint $table) {
            $table->string('tipo_serie')->default('estandar')->after('repeticiones'); // 'estandar', 'super_serie', 'drop_set', 'piramidal', 'circuito'
            $table->unsignedTinyInteger('rpe')->nullable()->after('descanso_segundos'); // RPE 1 a 10
            $table->string('tempo')->nullable()->after('rpe'); // Ej: '3-0-1-0'
        });

        Schema::create('plantillas_rutina', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('objetivo')->index(); // 'perder_peso', 'ganar_musculo', 'fuerza', 'acondicionamiento', 'resistencia'
            $table->string('nivel')->default('intermedio')->index(); // 'principiante', 'intermedio', 'avanzado'
            $table->json('dias_semana')->nullable();
            $table->foreignId('creada_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->boolean('activa')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('ejercicio_plantilla', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_id')
                ->constrained('plantillas_rutina')
                ->cascadeOnDelete();
            $table->foreignId('ejercicio_id')
                ->constrained('ejercicios')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('orden')->default(1);
            $table->unsignedSmallInteger('series')->default(3);
            $table->string('repeticiones')->default('10-12');
            $table->string('tipo_serie')->default('estandar');
            $table->unsignedSmallInteger('descanso_segundos')->default(60);
            $table->unsignedTinyInteger('rpe')->nullable();
            $table->string('tempo')->nullable();
            $table->string('notas')->nullable();
            $table->timestamps();

            $table->unique(['plantilla_id', 'ejercicio_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ejercicio_plantilla');
        Schema::dropIfExists('plantillas_rutina');

        Schema::table('ejercicio_rutina', function (Blueprint $table) {
            $table->dropColumn(['tipo_serie', 'rpe', 'tempo']);
        });

        Schema::table('ejercicios', function (Blueprint $table) {
            $table->dropColumn(['dificultad', 'equipamiento']);
        });
    }
};
