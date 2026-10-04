<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoUsersSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ProfesorSeeder;
use Database\Seeders\RolSeeder;
use Database\Seeders\SucursalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $this->actingAs($user = User::factory()->create());

        $this->get('/dashboard')->assertOk();
    }

    public function test_dashboard_provides_access_control_metrics_and_realtime_room_occupancy()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->has('metricas.total_en_sala')
            ->has('metricas.asistencias_denegadas_hoy')
            ->has('socios_en_sala')
            ->has('clases_hoy')
            ->has('sucursales')
        );
    }

    public function test_dashboard_with_seeded_data()
    {
        $this->seed(RolSeeder::class);
        $this->seed(SucursalSeeder::class);
        $this->seed(PlanSeeder::class);
        $this->seed(ProfesorSeeder::class);
        $this->seed(DemoUsersSeeder::class);

        $admin = User::where('email', 'admin@fitlife.test')->first();
        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertOk();
    }
}
