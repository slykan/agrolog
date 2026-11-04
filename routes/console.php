<?php

use App\Support\FisRegistar;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('szb:registar', function () {
    try {
        $this->info('Sredstava u registru: ' . FisRegistar::sinkroniziraj());
    } catch (\Throwable $e) {
        $this->error($e->getMessage());   // postojeći registar ostaje
        return 1;
    }
})->purpose('Preuzmi registar sredstava za zaštitu bilja iz FIS-a');

Schedule::command('szb:registar')->weeklyOn(1, '04:30');

// red čekanja (obavijesti mailom): obrađuje se svake minute preko cron schedule:run
Schedule::command('queue:work database --stop-when-empty --max-time=50 --tries=3')->everyMinute()->withoutOverlapping();
