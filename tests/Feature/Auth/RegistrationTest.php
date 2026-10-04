<?php

namespace Tests\Feature\Auth;

use App\Models\Plan;
use App\Models\Rol;
use App\Models\Socio;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered()
    {
        $this->seed(RolSeeder::class);
        $this->seed(PlanSeeder::class);

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_as_socio_with_linked_socio_record_and_redirect_to_mi_perfil()
    {
        $this->seed(RolSeeder::class);
        $this->seed(PlanSeeder::class);

        $plan = Plan::first();

        $response = $this->post('/register', [
            'name' => 'Carlos Mendoza',
            'dni' => 'V-28491023',
            'email' => 'carlos@fitlife.test',
            'telefono' => '+58 412 1234567',
            'password' => 'password',
            'password_confirmation' => 'password',
            'plan_id' => $plan ? $plan->id : null,
            'terminos' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('mi-perfil', absolute: false));

        // Verificar que el usuario tenga rol de Socio
        $user = User::where('email', 'carlos@fitlife.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->esSocio());

        // Verificar que se creó la ficha de socio vinculada
        $socio = Socio::where('dni', 'V-28491023')->first();
        $this->assertNotNull($socio);
        $this->assertEquals($user->id, $socio->user_id);
        $this->assertEquals('inactivo', $socio->estado);
        $this->assertStringStartsWith('SOC-', $socio->codigo);
    }
}
