<?php

namespace Tests\Feature;

use App\Models\Asistencia;
use App\Models\Ejercicio;
use App\Models\Rutina;
use App\Models\Socio;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulosCrudYNavTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Sucursal $sucursal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sucursal = Sucursal::create([
            'nombre' => 'Sede Central',
            'direccion' => 'Av. Principal 123',
            'ciudad' => 'Metrópolis',
            'estado' => 'Activo',
        ]);

        $this->user = User::factory()->create([
            'sucursal_id' => $this->sucursal->id,
        ]);
    }

    public function test_socio_show_renders_with_navigation_and_details(): void
    {
        $socio1 = Socio::factory()->create();
        $socio2 = Socio::factory()->create();
        $socio3 = Socio::factory()->create();

        $response = $this->actingAs($this->user)->get(route('socios.show', $socio2->id));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('socios/show')
            ->has('socio')
            ->has('navegacion')
            ->where('navegacion.anterior.id', $socio1->id)
            ->where('navegacion.siguiente.id', $socio3->id)
        );
    }

    public function test_socio_can_be_updated_and_deleted(): void
    {
        $socio = Socio::factory()->create([
            'nombre' => 'Carlos',
            'apellido' => 'Perez',
        ]);

        // Edit page
        $editRes = $this->actingAs($this->user)->get(route('socios.edit', $socio->id));
        $editRes->assertStatus(200);

        // Update
        $updateRes = $this->actingAs($this->user)->put(route('socios.update', $socio->id), [
            'nombre' => 'Carlos Modificado',
            'apellido' => 'Perez Gomez',
            'dni' => $socio->dni,
            'correo' => 'carlos.mod@example.com',
            'telefono' => '999888777',
            'estado' => 'activo',
        ]);

        $updateRes->assertRedirect(route('socios.show', $socio->id));
        $this->assertDatabaseHas('socios', [
            'id' => $socio->id,
            'nombre' => 'Carlos Modificado',
            'correo' => 'carlos.mod@example.com',
        ]);

        // Delete
        $deleteRes = $this->actingAs($this->user)->delete(route('socios.destroy', $socio->id));
        $deleteRes->assertRedirect(route('socios.index'));
        $this->assertSoftDeleted('socios', ['id' => $socio->id]);
    }

    public function test_asistencias_filter_by_socio_id(): void
    {
        $socioA = Socio::factory()->create();
        $socioB = Socio::factory()->create();

        Asistencia::create([
            'socio_id' => $socioA->id,
            'fecha_hora' => Carbon::now(),
            'metodo' => 'qr',
            'estado_acceso' => 'permitido',
            'sucursal_id' => $this->sucursal->id,
        ]);

        Asistencia::create([
            'socio_id' => $socioB->id,
            'fecha_hora' => Carbon::now(),
            'metodo' => 'qr',
            'estado_acceso' => 'permitido',
            'sucursal_id' => $this->sucursal->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('asistencias.index', ['socio_id' => $socioA->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('asistencias/index')
            ->where('socio_seleccionado.id', $socioA->id)
            ->has('asistencias.data', 1)
        );
    }

    public function test_asistencia_can_be_updated_and_deleted(): void
    {
        $socio = Socio::factory()->create();
        $entrada = Carbon::now()->subHours(2);

        $asistencia = Asistencia::create([
            'socio_id' => $socio->id,
            'fecha_hora' => $entrada,
            'metodo' => 'manual',
            'estado_acceso' => 'permitido',
            'sucursal_id' => $this->sucursal->id,
        ]);

        $salida = Carbon::now();

        // Update with exit time and notes
        $updateRes = $this->actingAs($this->user)->put(route('asistencias.update', $asistencia->id), [
            'fecha_hora' => $entrada->toDateTimeString(),
            'fecha_hora_salida' => $salida->toDateTimeString(),
            'estado_acceso' => 'permitido',
            'mensaje_acceso' => 'Salida confirmada por recepción',
        ]);

        $updateRes->assertSessionHasNoErrors();
        $asistencia->refresh();
        $this->assertNotNull($asistencia->fecha_hora_salida);
        $this->assertEquals(120, $asistencia->tiempo_permanencia_minutos);
        $this->assertEquals('Salida confirmada por recepción', $asistencia->mensaje_acceso);

        // Delete
        $deleteRes = $this->actingAs($this->user)->delete(route('asistencias.destroy', $asistencia->id));
        $deleteRes->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('asistencias', ['id' => $asistencia->id]);
    }

    public function test_rutina_show_update_and_destroy_crud(): void
    {
        $socio = Socio::factory()->create();

        $ejercicio1 = Ejercicio::create([
            'nombre' => 'Press de Banca',
            'grupo_muscular' => 'pecho',
            'tipo' => 'fuerza',
        ]);
        $ejercicio2 = Ejercicio::create([
            'nombre' => 'Aperturas con Mancuernas',
            'grupo_muscular' => 'pecho',
            'tipo' => 'hipertrofia',
        ]);

        $rutina = Rutina::create([
            'socio_id' => $socio->id,
            'creada_por' => $this->user->id,
            'nombre' => 'Rutina Pecho Pesado',
            'descripcion' => 'Enfoque fuerza',
            'nivel' => 'avanzado',
            'activa' => true,
        ]);

        $rutina->ejercicios()->attach($ejercicio1->id, [
            'orden' => 1,
            'series' => 4,
            'repeticiones' => '6-8',
            'descanso_segundos' => 120,
        ]);

        // Show page
        $showRes = $this->actingAs($this->user)->get(route('rutinas.show', $rutina->id));
        $showRes->assertStatus(200);
        $showRes->assertInertia(fn ($page) => $page
            ->component('rutinas/show')
            ->where('rutina.id', $rutina->id)
            ->where('rutina.socio.id', $socio->id)
            ->has('rutina.ejercicios', 1)
        );

        // Update routine
        $updateRes = $this->actingAs($this->user)->put(route('rutinas.update', $rutina->id), [
            'socio_id' => $socio->id,
            'nombre' => 'Rutina Pecho y Tríceps',
            'descripcion' => 'Ajuste de volumen',
            'nivel' => 'intermedio',
            'ejercicios' => [
                [
                    'ejercicio_id' => $ejercicio1->id,
                    'series' => 3,
                    'repeticiones' => '10',
                    'descanso_segundos' => 90,
                    'orden' => 1,
                ],
                [
                    'ejercicio_id' => $ejercicio2->id,
                    'series' => 3,
                    'repeticiones' => '12',
                    'descanso_segundos' => 60,
                    'orden' => 2,
                ],
            ],
        ]);

        $updateRes->assertSessionHasNoErrors();
        $rutina->refresh();
        $this->assertEquals('Rutina Pecho y Tríceps', $rutina->nombre);
        $this->assertEquals('intermedio', $rutina->nivel);
        $this->assertCount(2, $rutina->ejercicios);

        // Delete routine
        $deleteRes = $this->actingAs($this->user)->delete(route('rutinas.destroy', $rutina->id));
        $deleteRes->assertRedirect(route('rutinas.index'));
        $this->assertDatabaseMissing('rutinas', ['id' => $rutina->id]);
    }
}
