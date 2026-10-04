<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ProfesorSeeder;
use Database\Seeders\RolSeeder;
use Database\Seeders\SucursalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
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

    /**
     * Verifica que la home page responda con status 200 y renderice la vista Blade home.
     */
    public function test_home_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('home');
        $response->assertSee('FitLife');
        $response->assertSee('TRANSFORMA TU');
        $response->assertSee('MÁXIMO POTENCIAL');
    }

    /**
     * Verifica que los 3 planes de membresía ($50, $80, $120) se carguen dinámicamente.
     */
    public function test_home_page_displays_membership_plans_from_database(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('planes');

        // Los 3 planes oficiales
        $response->assertSee('Plan Básico');
        $response->assertSee('Plan Completo');
        $response->assertSee('Plan Premium');

        // Precios
        $response->assertSee('50');
        $response->assertSee('80');
        $response->assertSee('120');

        // Reglas de negocio
        $response->assertSee('Sin acceso a clases grupales');
        $response->assertSee('recargo de $10 por clase');
        $response->assertSee('100% INCLUIDAS');
    }

    /**
     * Verifica que el catálogo de clases grupales y las 3 sucursales se listen.
     */
    public function test_home_page_displays_clases_and_sucursales(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('clasesGrupales');
        $response->assertViewHas('sucursales');

        // Disciplinas
        $response->assertSee('Spinning');
        $response->assertSee('Yoga');
        $response->assertSee('Pilates');
        $response->assertSee('Zumba');
        $response->assertSee('Funcional');

        // 3 Sucursales
        $response->assertSee('FitLife Las Mercedes');
        $response->assertSee('FitLife Chacao');
        $response->assertSee('FitLife El Viñedo');
    }

    /**
     * Verifica la barra de navegación para visitante (Guest) con enlaces directos a login y register.
     */
    public function test_home_page_shows_guest_buttons(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('INICIAR SESIÓN');
        $response->assertSee('REGISTRARSE');
        $response->assertSee(route('login'));
        $response->assertSee(route('register'));
    }

    /**
     * Verifica que si el usuario está autenticado muestre su nombre, rol y acceso a Dashboard/Perfil.
     */
    public function test_home_page_shows_auth_badge_for_authenticated_user(): void
    {
        $rolAdmin = Rol::where('nombre', 'admin')->first();
        $user = User::factory()->create([
            'name' => 'Roberto Director',
            'rol_id' => $rolAdmin->id,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('Roberto Director');
        $response->assertSee('DASHBOARD');
    }

    /**
     * Verifica que los módulos Nosotros y Servicios se visualicen correctamente en la Home Page.
     */
    public function test_home_page_displays_nosotros_and_servicios_sections(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Sección Nosotros
        $response->assertSee('SOBRE FITLIFE GYM');
        $response->assertSee('comunidad');
        $response->assertSee('Entrenadores Elite');
        $response->assertSee('Acceso Inteligente');
        $response->assertSee('Equipamiento Pro');

        // Sección Servicios
        $response->assertSee('Nuestros Servicios Especializados');
        $response->assertSee('Musculación y Peso Libre');
        $response->assertSee('Cardio y Resistencia');
        $response->assertSee('Clases Grupales Dinámicas');
        $response->assertSee('Rutinas y Seguimiento Digital');
        $response->assertSee('Carnet Digital y Kioscos QR');
        $response->assertSee('Vestuarios y Recuperación');
    }

    /**
     * Verifica que los botones de planes enlacen a la página de registro con el plan preseleccionado.
     */
    public function test_home_page_plan_buttons_link_to_register_page_with_plan(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('register', ['plan' => 1]));
    }

    /**
     * Verifica que si una petición Inertia solicita la Home Page, retorne Inertia::location con status 409
     * para forzar una redirección limpia en el navegador del cliente y evitar la ventana modal.
     */
    public function test_home_page_with_inertia_header_returns_location_redirect(): void
    {
        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => 'test',
        ])->get('/');

        $response->assertStatus(409);
        $response->assertHeader('X-Inertia-Location', route('home'));
    }
}
