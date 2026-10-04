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
        // 1. Ampliación de profesores
        Schema::table('profesores', function (Blueprint $table) {
            if (! Schema::hasColumn('profesores', 'apellido')) {
                $table->string('apellido')->nullable()->after('nombre');
            }
            if (! Schema::hasColumn('profesores', 'correo_electronico')) {
                $table->string('correo_electronico')->nullable()->after('correo');
            }
            if (! Schema::hasColumn('profesores', 'estado')) {
                $table->string('estado')->default('activo')->index()->after('activo');
            }
        });

        // 2. Ampliación de clases_grupales
        Schema::table('clases_grupales', function (Blueprint $table) {
            if (! Schema::hasColumn('clases_grupales', 'sucursal_id')) {
                $table->foreignId('sucursal_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('sucursales')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('clases_grupales', 'dia_semana')) {
                $table->string('dia_semana')->nullable()->index()->after('descripcion');
            }
            if (! Schema::hasColumn('clases_grupales', 'hora_inicio')) {
                $table->string('hora_inicio')->nullable()->after('dia_semana');
            }
            if (! Schema::hasColumn('clases_grupales', 'estado')) {
                $table->string('estado')->default('activa')->index()->after('activa');
            }
        });

        // 3. Ampliación de inscripciones_clases
        Schema::table('inscripciones_clases', function (Blueprint $table) {
            if (! Schema::hasColumn('inscripciones_clases', 'monto_recargo')) {
                $table->decimal('monto_recargo', 10, 2)->default(0.00)->after('monto_recargo_pagado');
            }
            if (! Schema::hasColumn('inscripciones_clases', 'estado_pago')) {
                $table->string('estado_pago')->default('Exento')->index()->after('monto_recargo');
            }
            if (! Schema::hasColumn('inscripciones_clases', 'asistencia_confirmada')) {
                $table->boolean('asistencia_confirmada')->default(false)->index()->after('estado');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscripciones_clases', function (Blueprint $table) {
            $table->dropColumn(['monto_recargo', 'estado_pago', 'asistencia_confirmada']);
        });

        Schema::table('clases_grupales', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn(['sucursal_id', 'dia_semana', 'hora_inicio', 'estado']);
        });

        Schema::table('profesores', function (Blueprint $table) {
            $table->dropColumn(['apellido', 'correo_electronico', 'estado']);
        });
    }
};
