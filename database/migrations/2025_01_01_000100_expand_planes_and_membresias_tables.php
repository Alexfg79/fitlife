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
        Schema::table('planes', function (Blueprint $table) {
            $table->string('tipo')->default('individual')->index()->after('duracion_dias');
            $table->unsignedInteger('sesiones_totales')->nullable()->after('tipo');
            $table->json('beneficios')->nullable()->after('sesiones_totales');
        });

        Schema::table('membresias', function (Blueprint $table) {
            $table->string('tipo')->default('individual')->index()->after('plan_id');
            $table->unsignedInteger('sesiones_totales')->nullable()->after('tipo');
            $table->unsignedInteger('sesiones_restantes')->nullable()->after('sesiones_totales');
            $table->unsignedSmallInteger('cuotas_totales')->default(1)->after('precio_pagado');
            $table->unsignedSmallInteger('cuotas_pagadas')->default(1)->after('cuotas_totales');
            $table->decimal('saldo_pendiente', 10, 2)->default(0.00)->after('cuotas_pagadas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membresias', function (Blueprint $table) {
            $table->dropColumn([
                'tipo',
                'sesiones_totales',
                'sesiones_restantes',
                'cuotas_totales',
                'cuotas_pagadas',
                'saldo_pendiente',
            ]);
        });

        Schema::table('planes', function (Blueprint $table) {
            $table->dropColumn([
                'tipo',
                'sesiones_totales',
                'beneficios',
            ]);
        });
    }
};
