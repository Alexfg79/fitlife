<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Socio;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalSocioTest extends TestCase
{
    use RefreshDatabase;

    public function test_socio_can_view_mi_perfil_portal()
    {
        $this->seed(RolSeeder::class);
        $this->seed(PlanSeeder::class);

        $rolSocio = Rol::where('nombre', 'socio')->first();

        $user = User::factory()->create([
            'rol_id' => $rolSocio->id,
        ]);

        $socio = Socio::create([
            'user_id' => $user->id,
            'codigo' => 'SOC-9999',
            'nombre' => 'Mateo',
            'apellido' => 'Silva',
            'dni' => 'V-11223344',
            'correo' => $user->email,
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($user)->get('/mi-perfil');

        $response->assertStatus(200);
    }

    public function test_socio_accessing_dashboard_is_redirected_to_mi_perfil()
    {
        $this->seed(RolSeeder::class);

        $rolSocio = Rol::where('nombre', 'socio')->first();

        $user = User::factory()->create([
            'rol_id' => $rolSocio->id,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('mi-perfil', absolute: false));
    }
}
