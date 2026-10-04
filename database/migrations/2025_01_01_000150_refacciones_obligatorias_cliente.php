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
        // 1. Tabla de Sucursales
        Schema::create('sucursales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('direccion');
            $table->string('ciudad');
            $table->string('estado');
            $table->timestamps();
        });

        // 2. Clave foránea sucursal_id en users, asistencias y membresias
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('sucursal_id')
                ->nullable()
                ->after('rol_id')
                ->constrained('sucursales')
                ->nullOnDelete();
        });

        Schema::table('asistencias', function (Blueprint $table) {
            $table->foreignId('sucursal_id')
                ->nullable()
                ->after('socio_id')
                ->constrained('sucursales')
                ->nullOnDelete();

            $table->timestamp('fecha_hora_entrada')->nullable()->index()->after('clase_id');
            $table->timestamp('fecha_hora_salida')->nullable()->after('fecha_hora_entrada');

            if (! Schema::hasColumn('asistencias', 'tiempo_permanencia_minutos')) {
                $table->unsignedInteger('tiempo_permanencia_minutos')->nullable()->after('fecha_hora_salida');
            }
        });

        Schema::table('membresias', function (Blueprint $table) {
            $table->foreignId('sucursal_id')
                ->nullable()
                ->after('socio_id')
                ->constrained('sucursales')
                ->nullOnDelete();
        });

        // 3. Tabla de Profesores
        Schema::create('profesores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('nombre');
            $table->string('especialidad');
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 4. Actualización de clases_grupales con profesor_id y duracion_minutos
        Schema::table('clases_grupales', function (Blueprint $table) {
            $table->foreignId('profesor_id')
                ->nullable()
                ->after('entrenador_id')
                ->constrained('profesores')
                ->nullOnDelete();

            if (! Schema::hasColumn('clases_grupales', 'duracion_minutos')) {
                $table->unsignedInteger('duracion_minutos')->default(60)->after('horario');
            }
        });

        // 5. Tabla de Inscripciones a Clases Grupales
        Schema::create('inscripciones_clases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')
                ->constrained('socios')
                ->cascadeOnDelete();
            $table->foreignId('clase_grupal_id')
                ->constrained('clases_grupales')
                ->cascadeOnDelete();
            $table->date('fecha_clase')->index();
            $table->decimal('monto_recargo_pagado', 10, 2)->default(0.00);
            $table->string('estado')->default('confirmada'); // 'confirmada', 'cancelada', 'asistio'
            $table->timestamps();

            $table->unique(['socio_id', 'clase_grupal_id', 'fecha_clase']);
        });

        // 6. Restricciones y reglas de negocio en la tabla planes
        Schema::table('planes', function (Blueprint $table) {
            $table->boolean('horario_restringido')->default(false)->after('duracion_dias');
            $table->time('hora_inicio_permitida')->nullable()->after('horario_restringido');
            $table->time('hora_fin_permitida')->nullable()->after('hora_inicio_permitida');
            $table->json('dias_permitidos')->nullable()->after('hora_fin_permitida');
            $table->string('acceso_clases')->default('sin_acceso')->after('dias_permitidos'); // 'sin_acceso', 'con_recargo', 'incluido'
            $table->decimal('costo_recargo_clase', 10, 2)->default(0.00)->after('acceso_clases');
            $table->boolean('permite_invitados')->default(false)->after('costo_recargo_clase');
        });

        // 7. Historial y niveles de dificultad en rutinas
        Schema::table('rutinas', function (Blueprint $table) {
            $table->string('nivel_dificultad')->default('Intermedio')->after('nivel');
            $table->string('estado')->default('Activa')->index()->after('activa'); // 'Activa', 'Histórica/Finalizada'
            $table->date('fecha_inicio')->nullable()->after('estado');
            $table->date('fecha_fin')->nullable()->after('fecha_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rutinas', function (Blueprint $table) {
            $table->dropColumn(['nivel_dificultad', 'estado', 'fecha_inicio', 'fecha_fin']);
        });

        Schema::table('planes', function (Blueprint $table) {
            $table->dropColumn([
                'horario_restringido',
                'hora_inicio_permitida',
                'hora_fin_permitida',
                'dias_permitidos',
                'acceso_clases',
                'costo_recargo_clase',
                'permite_invitados',
            ]);
        });

        Schema::dropIfExists('inscripciones_clases');

        Schema::table('clases_grupales', function (Blueprint $table) {
            $table->dropForeign(['profesor_id']);
            $table->dropColumn(['profesor_id']);
        });

        Schema::dropIfExists('profesores');

        Schema::table('membresias', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn(['sucursal_id']);
        });

        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn([
                'sucursal_id',
                'fecha_hora_entrada',
                'fecha_hora_salida',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn(['sucursal_id']);
        });

        Schema::dropIfExists('sucursales');
    }
};
