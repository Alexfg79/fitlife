<?php

namespace Tests\Feature;

use App\Models\ClaseGrupal;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\PlantillaRutina;
use App\Models\Socio;
use Carbon\Carbon;
use Database\Seeders\EjercicioSeeder;
use Database\Seeders\FitLifeExpansionSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class FitLifeExpansionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolSeeder::class);
        $this->seed(PlanSeeder::class);
        $this->seed(EjercicioSeeder::class);
        $this->seed(FitLifeExpansionSeeder::class);
    }

    /**
     * Prueba: Congelamiento de membresía y reanudación con prórroga de fecha.
     */
    public function test_congelamiento_y_reanudacion_de_membresia(): void
    {
        $socio = Socio::factory()->create(['estado' => 'activo']);
        $plan = Plan::first();

        $fechaFinOriginal = Carbon::today()->addDays(30);
        $membresia = Membresia::create([
            'socio_id' => $socio->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => Carbon::today()->subDays(10),
            'fecha_fin' => $fechaFinOriginal,
            'precio_pagado' => $plan->precio,
            'estado' => 'activa',
            'metodo_pago' => 'efectivo',
        ]);

        $this->assertTrue($socio->tieneMembresiaActiva());

        // Congelar por 15 días
        $congelamiento = $socio->congelarMembresia(15, 'medico');

        $this->assertNotNull($congelamiento);
        $this->assertEquals('congelada', $membresia->fresh()->estado);
        $this->assertFalse($socio->tieneMembresiaActiva());

        // Reanudar membresía
        $reanudada = $socio->reanudarMembresia();
        $this->assertTrue($reanudada);

        $membresiaActualizada = $membresia->fresh();
        $this->assertEquals('activa', $membresiaActualizada->estado);
        $this->assertTrue($socio->tieneMembresiaActiva());
    }

    /**
     * Prueba: Pases por sesiones con decremento automático en escaneo.
     */
    public function test_membresia_por_sesiones_y_bloqueo_al_agotar(): void
    {
        $socio = Socio::factory()->create(['estado' => 'activo']);
        $plan = Plan::where('tipo', 'sesiones')->first();

        $membresia = Membresia::create([
            'socio_id' => $socio->id,
            'plan_id' => $plan->id,
            'tipo' => 'sesiones',
            'sesiones_totales' => 2,
            'sesiones_restantes' => 2,
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => Carbon::today()->addDays(60),
            'precio_pagado' => $plan->precio,
            'estado' => 'activa',
            'metodo_pago' => 'tarjeta',
        ]);

        // 1er ingreso: Exitoso y descuenta sesión
        $resp1 = $this->postJson('/api/asistencias/escanear', [
            'codigo' => $socio->codigo,
            'metodo' => 'qr',
        ]);
        $resp1->assertStatus(200);
        $this->assertEquals(1, $membresia->fresh()->sesiones_restantes);

        // Check-out de la primera sesión
        $this->postJson('/api/asistencias/check-out', [
            'asistencia_id' => $resp1->json('asistencia_id'),
        ])->assertStatus(200);

        // 2do ingreso: Exitoso y queda en 0 sesiones
        $resp2 = $this->postJson('/api/asistencias/escanear', [
            'codigo' => $socio->codigo,
            'metodo' => 'qr',
        ]);
        $resp2->assertStatus(200);
        $this->assertEquals(0, $membresia->fresh()->sesiones_restantes);

        // Check-out de la segunda sesión
        $this->postJson('/api/asistencias/check-out', [
            'asistencia_id' => $resp2->json('asistencia_id'),
        ])->assertStatus(200);

        // 3er ingreso: Denegado por sesiones agotadas
        $resp3 = $this->postJson('/api/asistencias/escanear', [
            'codigo' => $socio->codigo,
            'metodo' => 'qr',
        ]);
        $resp3->assertStatus(422);
        $resp3->assertJson([
            'exito' => false,
            'estado_acceso' => 'denegado_sesiones_agotadas',
        ]);
    }

    /**
     * Prueba: Check-in y posterior Check-out calculando minutos de permanencia.
     */
    public function test_check_in_y_check_out_con_tiempo_de_permanencia(): void
    {
        $socio = Socio::factory()->create(['estado' => 'activo']);
        $plan = Plan::first();

        Membresia::create([
            'socio_id' => $socio->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => Carbon::today()->subDays(5),
            'fecha_fin' => Carbon::today()->addDays(25),
            'precio_pagado' => $plan->precio,
            'estado' => 'activa',
            'metodo_pago' => 'efectivo',
        ]);

        // Check-in (Entrada)
        $respEntrada = $this->postJson('/api/asistencias/escanear', [
            'codigo' => $socio->codigo,
            'metodo' => 'kiosco',
        ]);

        $respEntrada->assertStatus(200);
        $asistenciaId = $respEntrada->json('asistencia_id');
        $this->assertNotNull($asistenciaId);

        // Check-out (Salida)
        $respSalida = $this->postJson('/api/asistencias/check-out', [
            'asistencia_id' => $asistenciaId,
        ]);

        $respSalida->assertStatus(200);
        $respSalida->assertJson([
            'exito' => true,
        ]);
        $this->assertNotNull($respSalida->json('tiempo_permanencia_minutos'));
    }

    /**
     * Prueba: Marcaje de clase grupal y validación de cupo disponible.
     */
    public function test_asistencia_a_clase_grupal_y_control_de_cupo(): void
    {
        $socio = Socio::factory()->create(['estado' => 'activo']);
        $plan = Plan::first();
        Membresia::create([
            'socio_id' => $socio->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => Carbon::today()->addDays(30),
            'precio_pagado' => $plan->precio,
            'estado' => 'activa',
            'metodo_pago' => 'efectivo',
        ]);

        $clase = ClaseGrupal::create([
            'nombre' => 'CrossFit Test Cupo',
            'horario' => '18:00',
            'cupo_maximo' => 1,
            'sala' => 'Sala 1',
            'activa' => true,
        ]);

        // 1er ingreso a la clase: Permitido
        $resp1 = $this->postJson('/api/asistencias/escanear', [
            'codigo' => $socio->codigo,
            'tipo_acceso' => 'clase_grupal',
            'clase_id' => $clase->id,
        ]);
        $resp1->assertStatus(200);

        // 2do ingreso (otro socio) cuando el cupo es 1: Denegado
        $socio2 = Socio::factory()->create(['estado' => 'activo']);
        Membresia::create([
            'socio_id' => $socio2->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => Carbon::today()->addDays(30),
            'precio_pagado' => $plan->precio,
            'estado' => 'activa',
            'metodo_pago' => 'efectivo',
        ]);

        $resp2 = $this->postJson('/api/asistencias/escanear', [
            'codigo' => $socio2->codigo,
            'tipo_acceso' => 'clase_grupal',
            'clase_id' => $clase->id,
        ]);
        $resp2->assertStatus(422);
        $resp2->assertJson([
            'exito' => false,
            'estado_acceso' => 'denegado_cupo_lleno',
        ]);
    }

    /**
     * Prueba: Asignación de rutina desde plantilla y generador automático.
     */
    public function test_asignacion_de_rutina_desde_plantilla_y_generador_automatico(): void
    {
        $socio = Socio::factory()->create(['estado' => 'activo']);
        $plantilla = PlantillaRutina::first();

        $this->assertNotNull($plantilla);

        // Asignar desde plantilla
        $rutina = $socio->asignarRutinaDesdePlantilla($plantilla);
        $this->assertEquals($plantilla->nombre, $rutina->nombre);
        $this->assertTrue($rutina->activa);
        $this->assertGreaterThan(0, $rutina->ejercicios()->count());

        // Generador automático por objetivo
        $rutinaAuto = $socio->generarRutinaAutomatica('perder_peso', 'intermedio');
        $this->assertNotNull($rutinaAuto);
        $this->assertTrue($rutinaAuto->activa);
        $this->assertGreaterThan(0, $rutinaAuto->ejercicios()->count());
    }

    /**
     * Prueba: Comando scheduled fitlife:procesar-vencimientos.
     */
    public function test_comando_procesar_vencimientos(): void
    {
        $socio = Socio::factory()->create(['estado' => 'activo']);
        $plan = Plan::first();

        // 1. Membresía que venció ayer
        $membresiaVencida = Membresia::create([
            'socio_id' => $socio->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => Carbon::today()->subDays(31),
            'fecha_fin' => Carbon::today()->subDays(1),
            'precio_pagado' => $plan->precio,
            'estado' => 'activa',
            'metodo_pago' => 'efectivo',
        ]);

        // 2. Membresía que vence en 5 días
        $socio2 = Socio::factory()->create(['estado' => 'activo']);
        $membresia5Dias = Membresia::create([
            'socio_id' => $socio2->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => Carbon::today()->subDays(25),
            'fecha_fin' => Carbon::today()->addDays(5),
            'precio_pagado' => $plan->precio,
            'estado' => 'activa',
            'metodo_pago' => 'efectivo',
        ]);

        $exitCode = Artisan::call('fitlife:procesar-vencimientos');
        $this->assertEquals(0, $exitCode);

        // La membresía vencida debe haber cambiado a 'vencida'
        $this->assertEquals('vencida', $membresiaVencida->fresh()->estado);

        // Se deben haber generado notificaciones
        $this->assertDatabaseHas('notificaciones_membresia', [
            'membresia_id' => $membresiaVencida->id,
            'tipo' => 'vencida',
        ]);

        $this->assertDatabaseHas('notificaciones_membresia', [
            'membresia_id' => $membresia5Dias->id,
            'tipo' => 'vencimiento_5_dias',
        ]);
    }
}
