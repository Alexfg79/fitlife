<?php

namespace Tests\Feature;

use App\Models\Ejercicio;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Rol;
use App\Models\Rutina;
use App\Models\Socio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FitLifeGymTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlanSeeder']);
        $this->artisan('db:seed', ['--class' => 'EjercicioSeeder']);
    }

    public function test_usuario_puede_tener_rol_y_verificar_permisos(): void
    {
        $rolAdmin = Rol::where('nombre', 'admin')->first();
        $usuario = User::factory()->create(['rol_id' => $rolAdmin->id]);

        $this->assertTrue($usuario->esAdmin());
        $this->assertFalse($usuario->esEntrenador());
        $this->assertTrue($usuario->tieneRol('admin'));
    }

    public function test_se_puede_crear_socio_mediante_controlador_y_genera_codigo(): void
    {
        $usuario = User::factory()->create();
        $plan = Plan::first();

        $respuesta = $this->actingAs($usuario)->post(route('socios.store'), [
            'nombre' => 'Alejandro',
            'apellido' => 'Morales',
            'dni' => '30112233',
            'correo' => 'alejandro.morales@gym.test',
            'telefono' => '04141234567',
            'plan_id' => $plan->id,
            'metodo_pago' => 'efectivo',
        ]);

        $respuesta->assertRedirect(route('socios.index'));

        $this->assertDatabaseHas('socios', [
            'dni' => '30112233',
            'correo' => 'alejandro.morales@gym.test',
            'codigo' => 'SOC-0001',
        ]);

        $socio = Socio::where('dni', '30112233')->first();
        $this->assertNotNull($socio->membresiaActiva);
        $this->assertEquals($plan->id, $socio->membresiaActiva->plan_id);
    }

    public function test_scopes_de_membresias_activas_y_vencidas(): void
    {
        $socio = Socio::factory()->create();
        $plan = Plan::first();
        $hoy = Carbon::today();

        // Membresía activa
        $membresiaActiva = Membresia::create([
            'socio_id' => $socio->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => $hoy->copy()->subDays(5),
            'fecha_fin' => $hoy->copy()->addDays(25),
            'precio_pagado' => $plan->precio,
            'estado' => 'activa',
            'metodo_pago' => 'efectivo',
        ]);

        // Membresía vencida
        $membresiaVencida = Membresia::create([
            'socio_id' => $socio->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => $hoy->copy()->subDays(40),
            'fecha_fin' => $hoy->copy()->subDays(10),
            'precio_pagado' => $plan->precio,
            'estado' => 'vencida',
            'metodo_pago' => 'efectivo',
        ]);

        $activas = Membresia::activas()->pluck('id');
        $vencidas = Membresia::vencidas()->pluck('id');

        $this->assertTrue($activas->contains($membresiaActiva->id));
        $this->assertFalse($activas->contains($membresiaVencida->id));
        $this->assertTrue($vencidas->contains($membresiaVencida->id));
    }

    public function test_asistencia_permite_acceso_a_socio_con_cuota_al_dia(): void
    {
        $usuario = User::factory()->create();
        $socio = Socio::factory()->create(['codigo' => 'SOC-9999', 'estado' => 'activo']);
        $plan = Plan::first();

        Membresia::create([
            'socio_id' => $socio->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => Carbon::today()->subDays(2),
            'fecha_fin' => Carbon::today()->addDays(28),
            'precio_pagado' => $plan->precio,
            'estado' => 'activa',
        ]);

        $respuesta = $this->actingAs($usuario)->postJson(route('asistencias.acceso'), [
            'codigo' => 'SOC-9999',
            'metodo' => 'qr',
        ]);

        $respuesta->assertOk();
        $respuesta->assertJson([
            'exito' => true,
            'estado_acceso' => 'permitido',
        ]);

        $this->assertDatabaseHas('asistencias', [
            'socio_id' => $socio->id,
            'estado_acceso' => 'permitido',
        ]);
    }

    public function test_asistencia_deniega_acceso_a_socio_con_cuota_vencida(): void
    {
        $usuario = User::factory()->create();
        $socio = Socio::factory()->create(['codigo' => 'SOC-8888', 'estado' => 'activo']);
        $plan = Plan::first();

        Membresia::create([
            'socio_id' => $socio->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => Carbon::today()->subDays(40),
            'fecha_fin' => Carbon::today()->subDays(5),
            'precio_pagado' => $plan->precio,
            'estado' => 'vencida',
        ]);

        $respuesta = $this->actingAs($usuario)->postJson(route('asistencias.acceso'), [
            'codigo' => 'SOC-8888',
            'metodo' => 'codigo',
        ]);

        $respuesta->assertStatus(422);
        $respuesta->assertJson([
            'exito' => false,
            'estado_acceso' => 'denegado_vencido',
        ]);

        $this->assertDatabaseHas('asistencias', [
            'socio_id' => $socio->id,
            'estado_acceso' => 'denegado_vencido',
        ]);
    }

    public function test_rutina_se_relaciona_con_ejercicios_mediante_tabla_pivote(): void
    {
        $usuario = User::factory()->create();
        $socio = Socio::factory()->create();
        $ejercicios = Ejercicio::take(2)->get();

        $rutina = Rutina::create([
            'socio_id' => $socio->id,
            'creada_por' => $usuario->id,
            'nombre' => 'Full Body Adaptación',
            'nivel' => 'principiante',
            'activa' => true,
        ]);

        $rutina->ejercicios()->attach($ejercicios[0]->id, [
            'orden' => 1,
            'series' => 3,
            'repeticiones' => '12',
            'descanso_segundos' => 60,
            'notas' => 'Cuidar postura',
        ]);

        $rutina->ejercicios()->attach($ejercicios[1]->id, [
            'orden' => 2,
            'series' => 4,
            'repeticiones' => '10',
            'descanso_segundos' => 90,
            'notas' => 'Incrementar carga',
        ]);

        $this->assertCount(2, $rutina->ejercicios);
        $this->assertEquals(3, $rutina->ejercicios[0]->pivot->series);
        $this->assertEquals('12', $rutina->ejercicios[0]->pivot->repeticiones);
        $this->assertEquals(60, $rutina->ejercicios[0]->pivot->descanso_segundos);
    }

    public function test_comando_artisan_actualiza_membresias_vencidas_y_estado_socio(): void
    {
        $socio = Socio::factory()->create(['estado' => 'activo']);
        $plan = Plan::first();

        $membresiaExpirada = Membresia::create([
            'socio_id' => $socio->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => Carbon::today()->subDays(35),
            'fecha_fin' => Carbon::today()->subDays(3),
            'precio_pagado' => $plan->precio,
            'estado' => 'activa', // Aún marcada como activa erróneamente
        ]);

        $this->artisan('fitlife:check-vencimientos')
            ->assertSuccessful();

        $this->assertEquals('vencida', $membresiaExpirada->fresh()->estado);
        $this->assertEquals('moroso', $socio->fresh()->estado);
    }

    public function test_usuario_autenticado_puede_visitar_modulo_de_rutinas(): void
    {
        $admin = User::first() ?? User::factory()->create();

        $respuesta = $this->actingAs($admin)->get(route('rutinas.index'));

        $respuesta->assertStatus(200);
        $this->assertNotNull($respuesta->viewData('page')['props']['rutinas']);
    }
}
