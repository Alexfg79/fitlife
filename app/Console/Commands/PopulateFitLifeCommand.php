<?php

namespace App\Console\Commands;

use Database\Seeders\FitLifeMassiveSeeder;
use Illuminate\Console\Command;

class PopulateFitLifeCommand extends Command
{
    /**
     * Nombre y firma del comando en consola.
     *
     * @var string
     */
    protected $signature = 'fitlife:populate {--count=500 : Cantidad de socios a generar}';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Poblamiento masivo optimizado de la base de datos FitLife con lógica de negocio realista.';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle(): int
    {
        $count = (int) $this->option('count');
        $this->info("⚡ Iniciando generación masiva de datos para FitLife (~{$count} socios)...");

        $seeder = new FitLifeMassiveSeeder;
        $seeder->setCommand($this);
        $seeder->run($count);

        $this->newLine();
        $this->info('✨ ¡Proceso completado exitosamente! Base de datos poblada en PostgreSQL.');

        return Command::SUCCESS;
    }
}
