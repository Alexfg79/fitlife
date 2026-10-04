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
        Schema::create('notificaciones_membresia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')
                ->constrained('socios')
                ->cascadeOnDelete();
            $table->foreignId('membresia_id')
                ->constrained('membresias')
                ->cascadeOnDelete();
            $table->string('tipo')->index(); // 'vencimiento_5_dias', 'vencimiento_1_dia', 'vencida', 'congelada', 'reanudada', 'cuota_pendiente'
            $table->string('titulo');
            $table->text('mensaje');
            $table->string('canal')->default('sistema'); // 'sistema', 'correo', 'sms', 'whatsapp'
            $table->boolean('leida')->default(false)->index();
            $table->timestamp('enviada_el')->index();
            $table->timestamps();

            $table->index(['socio_id', 'leida']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones_membresia');
    }
};
