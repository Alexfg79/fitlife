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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membresia_id')
                ->constrained('membresias')
                ->cascadeOnDelete();
            $table->timestamp('fecha_pago')->index();
            $table->decimal('monto', 10, 2);
            $table->string('metodo_pago')->index(); // 'Efectivo', 'Transferencia', 'Tarjeta', 'Pago_Movil'
            $table->string('referencia')->nullable()->index();
            $table->string('comprobante')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['membresia_id', 'fecha_pago']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
