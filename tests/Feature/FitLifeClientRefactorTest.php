<?php

namespace Tests\Feature;

use App\Models\Asistencia;
use App\Models\ClaseGrupal;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Rol;
use App\Models\Rutina;
use App\Models\Socio;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AsignarRutinasTodosSociosSeeder;
use Database\Seeders\EjercicioSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ProfesorSeeder;
use Database\Seeders\RolSeeder;
use Database\Seeders\SucursalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FitLifeClientRefactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolSeeder::class);
        $this->seed(SucursalSeeder::class);
        $this->seed(PlanSeeder::class);
        $this->seed(ProfesorSeeder::class);
        $this->seed(EjercicioSeeder::class);
    }

    /**
     * Test 1: Soporte Multi-sucursal en usuarios, membresías y asistencias.
     */
    public function test_soporte_multi_sucursal_en_modelos_y_relaciones(): void
    {
        $sucursales = Sucursal::all();
        $this->assertCount(3, $sucursales);

        $sucursal = $sucursales->first();
        $user = User::factory()->create([
            'sucursal_id' => $sucursal->id,
            'rol_id' => Rol::where('nombre', 'socio')->value('id'),
        ]);

        $socio = Socio::factory()->create(['user_id' => $user->id]);
        $plan = Plan::where('nombre', 'Plan Completo')->first();

        $membresia = Membresia::create([
            'socio_id' => $socio->id,
            'plan_id' => $plan->id,
            'sucursal_id' => $sucursal->id,
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => Carbon::today()->addDays(30),
            'precio_pagado' => $plan->precio,
            'estado' => 'activa',
            'metodo_pago' => 'tarjeta',
        ]);

        $asistencia = Asistencia::create([
            'socio_id' => $socio->id,
            'sucursal_id' => $sucursal->id,
            'fecha_hora' => Carbon::now(),
            'fecha_hora_entrada' => Carbon::now(),
            'metodo' => 'qr',
            'estado_acceso' => 'permitido',
            'mensaje_acceso' => 'Acceso autorizado',
        ]);

        $this->assertEquals($sucursal->id, $user->sucursal?->id);
        $this->assertEquals($sucursal->id, $membresia->sucursal?->id);
        $this->assertEquals($sucursal->id, $asistencia->sucursal?->id);
    }

    /**
     * Test 2: Control de asistencia con marcas de entrada y salida (Scan toggle).
     */
    public function test_control_de_asistencia_entrada_y_salida_con_calculo_de_permanencia(): void
    {
        $socio = Socio::factory()->create(['estado' => 'activo']);
        $plan = Plan::where('nombre', 'Plan Completo')->first();

        Membresia::create([
            'socio_id' => $socio->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => Carbon::today()->addDays(30),
            'precio_pagado' => $plan->precio,
            'estado' => 'activa',
            'metodo_pago' => 'efectivo',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 9, 21, 10, 0, 0));
        // 1er Escaneo: Entrada (Check-in)
        $respEntrada = $this->postJson('/api/asistencias/escanear', [
            'codigo' => $socio->codigo,
            'metodo' => 'qr',
        ]);

        $respEntrada->assertStatus(200);
        $respEntrada->assertJson([
            'exito' => true,
            'tipo_evento' => 'entrada',
            'estado_acceso' => 'permitido',
        ]);

        $asistencia = Asistencia::find($respEntrada->json('asistencia_id'));
        $this->assertNotNull($asistencia->fecha_hora_entrada);
        $this->assertNull($asistencia->fecha_hora_salida);
        $this->assertNull($asistencia->tiempo_permanencia_minutos);

        // Simular que pasaron 60 minutos
        Carbon::setTestNow(Carbon::create(2026, 9, 21, 11, 0, 0));

        // 2do Escaneo del mismo socio: Detecta entrada abierta y registra Check-out (Salida)
        $respSalida = $this->postJson('/api/asistencias/escanear', [
            'codigo' => $socio->codigo,
            'metodo' => 'qr',
        ]);

        $respSalida->assertStatus(200);
        $respSalida->assertJson([
            'exito' => true,
            'tipo_evento' => 'salida',
            'estado_acceso' => 'salida_registrada',
        ]);

        $asistenciaActualizada = $asistencia->fresh();
        $this->assertNotNull($asistenciaActualizada->fecha_hora_salida);
        $this->assertGreaterThanOrEqual(59, $asistenciaActualizada->tiempo_permanencia_minutos);

        Carbon::setTestNow();
    }

    /**
     * Test 3: Bloqueo de acceso por morosidad (2 o más cuotas vencidas).
     */
    public function test_bloqueo_de_acceso_a_socio_con_dos_o_mas_cuotas_vencidas(): void
    {
        $socioMoroso = Socio::factory()->create(['estado' => 'activo']);
        $plan = Plan::where('nombre', 'Plan Completo')->first();

        // Membresía con 3 cuotas totales y 1 pagada = 2 cuotas vencidas/impagas
        Membresia::create([
            'socio_id' => $socioMoroso->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => Carbon::today()->subMonths(2),
            'fecha_fin' => Carbon::today()->addDays(30),
            'precio_pagado' => $plan->precio,
            'cuotas_totales' => 3,
            'cuotas_pagadas' => 1,
            'saldo_pendiente' => $plan->precio * 2,
            'estado' => 'activa',
            'metodo_pago' => 'efectivo',
        ]);

        $this->assertTrue($socioMoroso->esMorosoBloqueado());

        $respuesta = $this->postJson('/api/asistencias/escanear', [
            'codigo' => $socioMoroso->codigo,
            'metodo' => 'qr',
        ]);

        $respuesta->assertStatus(422);
        $respuesta->assertJson([
            'exito' => false,
            'estado_acceso' => 'denegado_moroso',
        ]);
        $this->assertStringContainsString('cuotas vencidas', $respuesta->json('mensaje'));
    }

    /**
     * Test 4: Restricción horaria para Plan Básico (Lunes a Viernes 08:00 a 18:00).
     */
    public function test_plan_basico_restringe_acceso_fuera_de_horario_permitido(): void
    {
        $socio = Socio::factory()->create(['estado' => 'activo']);
        $planBasico = Plan::where('nombre', 'Plan Básico')->first();

        Membresia::create([
            'socio_id' => $socio->id,
            'plan_id' => $planBasico->id,
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => Carbon::today()->addDays(30),
            'precio_pagado' => $planBasico->precio,
            'estado' => 'activa',
            'metodo_pago' => 'efectivo',
        ]);

        // Simular intento a las 21:00 hrs
        $horaFueraDeRango = Carbon::create(2026, 9, 21, 21, 0, 0); // Lunes 21:00
        $this->assertFalse($planBasico->estaEnHorarioPermitido($horaFueraDeRango));

        // Simular intento en domingo
        $domingo = Carbon::create(2026, 9, 27, 10, 0, 0); // Domingo 10:00
        $this->assertFalse($planBasico->estaEnHorarioPermitido($domingo));

        // Simular intento en horario permitido (Lunes 10:00)
        $horarioPermitido = Carbon::create(2026, 9, 21, 10, 0, 0); // Lunes 10:00
        $this->assertTrue($planBasico->estaEnHorarioPermitido($horarioPermitido));
    }

    /**
     * Test 5: Clases grupales, profesores y reglas de inscripción por plan.
     */
    public function test_reglas_de_inscripcion_a_clases_segun_tipo_de_plan(): void
    {
        $clase = ClaseGrupal::first();
        $this->assertNotNull($clase);
        $this->assertNotNull($clase->profesor);

        $planBasico = Plan::where('nombre', 'Plan Básico')->first();
        $planCompleto = Plan::where('nombre', 'Plan Completo')->first();
        $planPremium = Plan::where('nombre', 'Plan Premium')->first();

        // 1. Socio con Plan Básico NO puede inscribirse a clases
        $socioBasico = Socio::factory()->create(['estado' => 'activo']);
        Membresia::create([
            'socio_id' => $socioBasico->id,
            'plan_id' => $planBasico->id,
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => Carbon::today()->addDays(30),
            'precio_pagado' => $planBasico->precio,
            'estado' => 'activa',
            'metodo_pago' => 'efectivo',
        ]);

        $respBasico = $this->postJson("/api/clases/{$clase->id}/inscribir", [
            'socio_id' => $socioBasico->id,
            'fecha_clase' => Carbon::today()->toDateString(),
        ]);
        $respBasico->assertStatus(422);
        $this->assertStringContainsString('Plan Básico no permite inscripción', $respBasico->json('error'));

        // 2. Socio con Plan Completo paga recargo obligatorio de $10
        $socioCompleto = Socio::factory()->create(['estado' => 'activo']);
        Membresia::create([
            'socio_id' => $socioCompleto->id,
            'plan_id' => $planCompleto->id,
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => Carbon::today()->addDays(30),
            'precio_pagado' => $planCompleto->precio,
            'estado' => 'activa',
            'metodo_pago' => 'tarjeta',
        ]);

        $respCompleto = $this->postJson("/api/clases/{$clase->id}/inscribir", [
            'socio_id' => $socioCompleto->id,
            'fecha_clase' => Carbon::today()->toDateString(),
        ]);
        $respCompleto->assertStatus(201);
        $respCompleto->assertJson([
            'success' => true,
            'recargo_pagado' => 10.00,
        ]);

        // 3. Socio con Plan Premium tiene clases 100% incluidas ($0.00 recargo)
        $socioPremium = Socio::factory()->create(['estado' => 'activo']);
        Membresia::create([
            'socio_id' => $socioPremium->id,
            'plan_id' => $planPremium->id,
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => Carbon::today()->addDays(30),
            'precio_pagado' => $planPremium->precio,
            'estado' => 'activa',
            'metodo_pago' => 'tarjeta',
        ]);

        $respPremium = $this->postJson("/api/clases/{$clase->id}/inscribir", [
            'socio_id' => $socioPremium->id,
            'fecha_clase' => Carbon::today()->toDateString(),
        ]);
        $respPremium->assertStatus(201);
        $respPremium->assertJson([
            'success' => true,
            'recargo_pagado' => 0.00,
        ]);
    }

    /**
     * Test 6: Historial de rutinas y nivel de dificultad sin sobrescribir.
     */
    public function test_historial_de_rutinas_y_niveles_de_dificultad(): void
    {
        $socio = Socio::factory()->create(['estado' => 'activo']);

        $rutina1 = $socio->generarRutinaAutomatica('perder_peso', 'Principiante');
        $this->assertEquals('Principiante', $rutina1->nivel_dificultad);
        $this->assertEquals('Activa', $rutina1->estado);

        // Generar una segunda rutina debe archivar la primera
        $rutina2 = $socio->generarRutinaAutomatica('fuerza', 'Avanzado');
        $this->assertEquals('Avanzado', $rutina2->nivel_dificultad);
        $this->assertEquals('Activa', $rutina2->estado);

        $rutina1Actualizada = $rutina1->fresh();
        $this->assertEquals('Histórica/Finalizada', $rutina1Actualizada->estado);
        $this->assertFalse((bool) $rutina1Actualizada->activa);
        $this->assertNotNull($rutina1Actualizada->fecha_fin);

        // Ambas existen en el historial acumulativo del socio
        $this->assertEquals(2, $socio->rutinas()->count());
        $this->assertEquals(1, $socio->rutinas()->activas()->count());
        $this->assertEquals(1, $socio->rutinas()->historicas()->count());
    }

    /**
     * Test 7: Métricas y reportes obligatorios en el Dashboard.
     */
    public function test_dashboard_incluye_los_cinco_reportes_analiticos(): void
    {
        $admin = User::factory()->create([
            'rol_id' => Rol::where('nombre', 'admin')->value('id'),
        ]);

        $respuesta = $this->actingAs($admin)->get(route('dashboard'));
        $respuesta->assertStatus(200);

        $propReportes = $respuesta->viewData('page')['props']['reportes'];
        $this->assertNotNull($propReportes);

        // 1. Altas y bajas
        $this->assertArrayHasKey('altas_bajas', $propReportes);
        $this->assertArrayHasKey('altas', $propReportes['altas_bajas']);
        $this->assertArrayHasKey('bajas', $propReportes['altas_bajas']);

        // 2. Reporte de morosos (1 vs 2+ cuotas)
        $this->assertArrayHasKey('morosos', $propReportes);
        $this->assertArrayHasKey('una_cuota', $propReportes['morosos']);
        $this->assertArrayHasKey('dos_o_mas_cuotas_bloqueados', $propReportes['morosos']);

        // 3. Ocupación semanal
        $this->assertArrayHasKey('ocupacion_semanal', $propReportes);

        // 4. Ranking clases y profesores
        $this->assertArrayHasKey('ranking_clases', $propReportes);

        // 5. Recaudación por plan
        $this->assertArrayHasKey('recaudacion_por_plan', $propReportes);
    }

    /**
     * Test 8: Generar socio automáticamente le asigna membresía y rutina activa con ejercicios.
     */
    public function test_generacion_automatica_de_socio_incluye_rutina_activa(): void
    {
        $admin = User::factory()->create([
            'rol_id' => Rol::where('nombre', 'admin')->value('id'),
        ]);

        $respuesta = $this->actingAs($admin)->postJson(route('socios.generar-automatico'));
        $respuesta->assertStatus(201);
        $respuesta->assertJson(['exito' => true]);

        $socioId = $respuesta->json('socio.id');
        $socio = Socio::findOrFail($socioId);

        $this->assertNotNull($socio->membresiaActiva);
        $this->assertTrue($socio->rutinas()->where('activa', true)->exists());
        $rutina = $socio->rutinas()->where('activa', true)->first();
        $this->assertGreaterThanOrEqual(4, $rutina->ejercicios()->count());
    }

    /**
     * Test 9: Seeder asigna rutinas activas a todos los socios que no tienen una.
     */
    public function test_seeder_asigna_rutinas_activas_a_todos_los_socios(): void
    {
        Socio::factory()->count(5)->create(['estado' => 'activo']);

        $this->seed(AsignarRutinasTodosSociosSeeder::class);

        $sociosSinRutinaActiva = Socio::whereDoesntHave('rutinas', function ($q) {
            $q->where('activa', true);
        })->count();

        $this->assertEquals(0, $sociosSinRutinaActiva);
    }
}
