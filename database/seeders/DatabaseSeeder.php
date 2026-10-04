<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Ejecutar seeders estructurales básicos
        $this->call([
            RolSeeder::class,
            SucursalSeeder::class,
            PlanSeeder::class,
            EjercicioSeeder::class,
            ProfesorSeeder::class,
        ]);

        // 2. Ejecutar seeder masivo con lógica de negocio integral (500+ registros)
        $this->call(FitLifeMassiveSeeder::class);

        // 3. Garantizar que todos los socios tengan rutinas de entrenamiento activas
        $this->call(AsignarRutinasTodosSociosSeeder::class);

        // 4. Garantizar cuentas demo de acceso rápido para auditoría y evaluación
        $this->call(DemoUsersSeeder::class);
    }
}
