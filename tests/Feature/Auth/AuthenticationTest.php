<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\DemoUsersSeeder;
use Database\Seeders\EjercicioSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('home'));
    }

    public function test_users_can_logout_via_inertia_returns_location()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout', [], [
            'X-Inertia' => 'true',
        ]);

        $this->assertGuest();
        $response->assertStatus(409);
        $response->assertHeader('X-Inertia-Location', route('home'));
    }

    public function test_quick_login_demo_roles_can_authenticate()
    {
        $this->seed(RolSeeder::class);
        $this->seed(PlanSeeder::class);
        $this->seed(EjercicioSeeder::class);
        $this->seed(DemoUsersSeeder::class);

        $rolesDemo = [
            'admin@fitlife.test' => 'admin',
            'coach@fitlife.test' => 'entrenador',
            'socio@fitlife.test' => 'socio',
        ];

        foreach ($rolesDemo as $email => $rolEsperado) {
            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'password',
            ]);

            $rutaEsperada = $rolEsperado === 'socio'
                ? route('mi-perfil', absolute: false)
                : route('dashboard', absolute: false);

            $response->assertRedirect($rutaEsperada);
            $this->assertAuthenticated();

            $usuario = auth()->user();
            $this->assertEquals($email, $usuario->email);
            $this->assertEquals($rolEsperado, $usuario->rol->nombre);

            // Cerrar sesión para el siguiente rol
            auth()->logout();
        }
    }
}
