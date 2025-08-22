<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



Schedule::command('integracion:sync-pedidos')->everyTenMinutes()->withoutOverlapping();
Schedule::command('integracion:sync-rutas')->everyTenMinutes()->withoutOverlapping();
Schedule::command('integracion:sync-facturas')->everyTenMinutes()->withoutOverlapping();

