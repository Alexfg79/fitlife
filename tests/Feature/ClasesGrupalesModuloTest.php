<?php

namespace Tests\Feature;

use App\Models\ClaseGrupal;
use App\Models\InscripcionClase;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Profesor;
use App\Models\Rol;
use App\Models\Socio;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ProfesorSeeder;
use Database\Seeders\RolSeeder;
use Database\Seeders\SucursalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClasesGrupalesModuloTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolSeeder::class);
        $this->seed(SucursalSeeder::class);
        $this->seed(PlanSeeder::class);
        $this->seed(ProfesorSeeder::class);
    }

    public function test_usuario_autenticado_puede_visitar_catalogo_de_clases_grupales(): void
    {
        $admin = User::factory()->create([
            'rol_id' => Rol::where('nombre', 'admin')->value('id'),
        ]);

        $respuesta = $this->actingAs($admin)->get(route('clases.index'));

        $respuesta->assertStatus(200);
        $props = $respuesta->viewData('page')['props'];

        $this->assertArrayHasKey('clases', $props);
        $this->assertArrayHasKey('sucursales', $props);
        $this->assertArrayHasKey('profesores', $props);
        $this->assertArrayHasKey('disciplinas', $props);
        $this->assertCount(5, $props['clases']);
    }

    public function test_admin_puede_crear_nueva_clase_grupal(): void
    {
        $admin = User::factory()->create([
            'rol_id' => Rol::where('nombre', 'admin')->value('id'),
        ]);

        $profesor = Profesor::first();
        $sucursal = Sucursal::first();

        $respuesta = $this->actingAs($admin)->post(route('clases.store'), [
            'nombre' => 'Spinning RPM Extreme',
            'profesor_id' => $profesor->id,
            'sucursal_id' => $sucursal->id,
            'dia_semana' => 'Sábado',
            'hora_inicio' => '09:00',
            'duracion_minutos' => 45,
            'cupo_maximo' => 18,
            'sala' => 'Sala Cardio 2',
            'descripcion' => 'Sesión intensiva de fin de semana.',
        ]);

        $respuesta->assertRedirect(route('clases.index'));

        $this->assertDatabaseHas('clases_grupales', [
            'nombre' => 'Spinning RPM Extreme',
            'profesor_id' => $profesor->id,
            'sucursal_id' => $sucursal->id,
            'dia_semana' => 'Sábado',
            'cupo_maximo' => 18,
        ]);
    }

    public function test_admin_puede_actualizar_clase_grupal(): void
    {
        $admin = User::factory()->create([
            'rol_id' => Rol::where('nombre', 'admin')->value('id'),
        ]);

        $clase = ClaseGrupal::first();

        $respuesta = $this->actingAs($admin)->put(route('clases.update', $clase->id), [
            'nombre' => 'Yoga Restaurativo Matutino',
            'profesor_id' => $clase->profesor_id,
            'sucursal_id' => $clase->sucursal_id,
            'dia_semana' => 'Martes',
            'hora_inicio' => '07:30',
            'duracion_minutos' => 60,
            'cupo_maximo' => 22,
            'sala' => 'Studio Zen',
        ]);

        $respuesta->assertRedirect(route('clases.index'));

        $this->assertEquals('Yoga Restaurativo Matutino', $clase->fresh()->nombre);
        $this->assertEquals(22, $clase->fresh()->cupo_maximo);
    }

    public function test_socio_plan_basico_no_puede_inscribirse_en_clases_grupales(): void
    {
        $admin = User::factory()->create();
        $clase = ClaseGrupal::first();
        $planBasico = Plan::where('nombre', 'Plan Básico')->first();

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

        $respuesta = $this->actingAs($admin)->postJson(route('clases.inscribir', $clase->id), [
            'socio_id' => $socioBasico->id,
            'fecha_clase' => Carbon::today()->toDateString(),
        ]);

        $respuesta->assertStatus(422);
        $respuesta->assertJsonFragment([
            'error' => 'El Plan Básico no incluye acceso a clases grupales. Actualiza a Plan Completo o Premium. (El Plan Básico no permite inscripción a clases grupales.)',
        ]);

        $this->assertDatabaseMissing('inscripciones_clases', [
            'socio_id' => $socioBasico->id,
            'clase_grupal_id' => $clase->id,
        ]);
    }

    public function test_socio_plan_completo_paga_recargo_de_10_y_registra_transaccion_en_pagos(): void
    {
        $admin = User::factory()->create();
        $clase = ClaseGrupal::first();
        $planCompleto = Plan::where('nombre', 'Plan Completo')->first();

        $socioCompleto = Socio::factory()->create(['estado' => 'activo']);
        $membresia = Membresia::create([
            'socio_id' => $socioCompleto->id,
            'plan_id' => $planCompleto->id,
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => Carbon::today()->addDays(30),
            'precio_pagado' => $planCompleto->precio,
            'estado' => 'activa',
            'metodo_pago' => 'tarjeta',
        ]);

        $fechaClase = Carbon::today()->toDateString();

        $respuesta = $this->actingAs($admin)->postJson(route('clases.inscribir', $clase->id), [
            'socio_id' => $socioCompleto->id,
            'fecha_clase' => $fechaClase,
        ]);

        $respuesta->assertStatus(201);
        $respuesta->assertJson([
            'success' => true,
            'recargo_pagado' => 10.00,
        ]);

        // Verificar registro de inscripción con recargo y estado Pagado
        $this->assertDatabaseHas('inscripciones_clases', [
            'socio_id' => $socioCompleto->id,
            'clase_grupal_id' => $clase->id,
            'monto_recargo' => 10.00,
            'estado_pago' => 'Pagado',
            'estado' => 'confirmada',
        ]);

        // Verificar registro en la tabla de pagos
        $this->assertDatabaseHas('pagos', [
            'membresia_id' => $membresia->id,
            'monto' => 10.00,
            'metodo_pago' => 'Tarjeta',
        ]);
    }

    public function test_socio_plan_premium_tiene_acceso_ilimitado_sin_recargo(): void
    {
        $admin = User::factory()->create();
        $clase = ClaseGrupal::first();
        $planPremium = Plan::where('nombre', 'Plan Premium')->first();

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

        $fechaClase = Carbon::today()->toDateString();

        $respuesta = $this->actingAs($admin)->postJson(route('clases.inscribir', $clase->id), [
            'socio_id' => $socioPremium->id,
            'fecha_clase' => $fechaClase,
        ]);

        $respuesta->assertStatus(201);
        $respuesta->assertJson([
            'success' => true,
            'recargo_pagado' => 0.00,
        ]);

        $this->assertDatabaseHas('inscripciones_clases', [
            'socio_id' => $socioPremium->id,
            'clase_grupal_id' => $clase->id,
            'monto_recargo' => 0.00,
            'estado_pago' => 'Exento',
        ]);
    }

    public function test_control_de_cupo_en_tiempo_real_bloquea_cuando_la_clase_esta_llena(): void
    {
        $admin = User::factory()->create();
        $profesor = Profesor::first();
        $sucursal = Sucursal::first();
        $planPremium = Plan::where('nombre', 'Plan Premium')->first();

        // Crear una clase con cupo máximo de 2
        $clasePequena = ClaseGrupal::create([
            'nombre' => 'Pilates Reformer VIP',
            'profesor_id' => $profesor->id,
            'sucursal_id' => $sucursal->id,
            'dia_semana' => 'Viernes',
            'hora_inicio' => '17:00',
            'horario' => 'Viernes 17:00',
            'duracion_minutos' => 50,
            'cupo_maximo' => 2,
            'activa' => true,
        ]);

        $fecha = Carbon::today()->toDateString();

        // Socio 1
        $socio1 = Socio::factory()->create(['estado' => 'activo']);
        Membresia::create([
            'socio_id' => $socio1->id,
            'plan_id' => $planPremium->id,
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => Carbon::today()->addDays(30),
            'precio_pagado' => $planPremium->precio,
            'estado' => 'activa',
            'metodo_pago' => 'tarjeta',
        ]);

        // Socio 2
        $socio2 = Socio::factory()->create(['estado' => 'activo']);
        Membresia::create([
            'socio_id' => $socio2->id,
            'plan_id' => $planPremium->id,
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => Carbon::today()->addDays(30),
            'precio_pagado' => $planPremium->precio,
            'estado' => 'activa',
            'metodo_pago' => 'tarjeta',
        ]);

        // Socio 3
        $socio3 = Socio::factory()->create(['estado' => 'activo']);
        Membresia::create([
            'socio_id' => $socio3->id,
            'plan_id' => $planPremium->id,
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => Carbon::today()->addDays(30),
            'precio_pagado' => $planPremium->precio,
            'estado' => 'activa',
            'metodo_pago' => 'tarjeta',
        ]);

        // Inscripción 1 -> Éxito (1/2)
        $r1 = $this->actingAs($admin)->postJson(route('clases.inscribir', $clasePequena->id), [
            'socio_id' => $socio1->id,
            'fecha_clase' => $fecha,
        ]);
        $r1->assertStatus(201);
        $this->assertEquals(1, $clasePequena->cuposDisponiblesParaFecha($fecha));

        // Inscripción 2 -> Éxito (2/2 - Llena)
        $r2 = $this->actingAs($admin)->postJson(route('clases.inscribir', $clasePequena->id), [
            'socio_id' => $socio2->id,
            'fecha_clase' => $fecha,
        ]);
        $r2->assertStatus(201);
        $this->assertEquals(0, $clasePequena->cuposDisponiblesParaFecha($fecha));

        // Inscripción 3 -> Rechazada por aforo completo
        $r3 = $this->actingAs($admin)->postJson(route('clases.inscribir', $clasePequena->id), [
            'socio_id' => $socio3->id,
            'fecha_clase' => $fecha,
        ]);
        $r3->assertStatus(422);
        $this->assertStringContainsString('No hay cupos disponibles', $r3->json('error'));
    }

    public function test_confirmacion_de_asistencia_y_cancelacion_de_inscripcion(): void
    {
        $admin = User::factory()->create();
        $clase = ClaseGrupal::first();
        $planPremium = Plan::where('nombre', 'Plan Premium')->first();

        $socio = Socio::factory()->create(['estado' => 'activo']);
        Membresia::create([
            'socio_id' => $socio->id,
            'plan_id' => $planPremium->id,
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => Carbon::today()->addDays(30),
            'precio_pagado' => $planPremium->precio,
            'estado' => 'activa',
            'metodo_pago' => 'tarjeta',
        ]);

        $inscripcion = InscripcionClase::create([
            'socio_id' => $socio->id,
            'clase_grupal_id' => $clase->id,
            'fecha_clase' => Carbon::today()->toDateString(),
            'monto_recargo' => 0.00,
            'estado_pago' => 'Exento',
            'asistencia_confirmada' => false,
            'estado' => 'confirmada',
        ]);

        // Confirmar asistencia
        $rAsistencia = $this->actingAs($admin)->patchJson(route('clases.inscripciones.confirmar', $inscripcion->id));
        $rAsistencia->assertStatus(200);
        $this->assertTrue((bool) $inscripcion->fresh()->asistencia_confirmada);
        $this->assertEquals('asistio', $inscripcion->fresh()->estado);

        // Cancelar inscripción
        $rCancelar = $this->actingAs($admin)->deleteJson(route('clases.inscripciones.destroy', $inscripcion->id));
        $rCancelar->assertStatus(200);
        $this->assertEquals('cancelada', $inscripcion->fresh()->estado);
    }
}
