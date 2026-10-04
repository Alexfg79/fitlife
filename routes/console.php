<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('fitlife:procesar-vencimientos')
    ->dailyAt('06:00')
    ->description('Auditoría automática de membresías, congelamientos y notificaciones de cuotas.');
