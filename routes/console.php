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

Artisan::command('szb:detalji', function () {
    // detalji registracije (kulture, karenca, broj tretmana) za sredstva iz evidencije zadnjih 150 dana
    $nazivi = \App\Models\Prskanje::where('datum_tretiranja', '>=', now()->subDays(150)->toDateString())->whereNotNull('trgovacki_naziv_sredstva')->distinct()->pluck('trgovacki_naziv_sredstva');
    $n = 0;
    foreach ($nazivi as $naziv) {
        $s = \App\Models\SzbSredstvo::poNazivu($naziv);
        if ($s && (! $s->detalji_at || $s->detalji_at->lt(now()->subDays(25))) && \App\Support\ProvjeraSzb::detalji($s) !== null) { $n++; usleep(300000); }
    }
    $this->info("Osvježeno sredstava: $n");
})->purpose('Dohvati iz FIS-a detalje registracije za sredstva iz evidencije');

Schedule::command('szb:detalji')->dailyAt('05:00');

Artisan::command('mehanizacija:podsjetnici', function () {
    $this->info('Poslano podsjetnika: ' . \App\Support\Mehanizacija::posaljiPodsjetnike());
})->purpose('Pošalji mailom podsjetnike na rokove mehanizacije (registracija, osiguranje, servis...)');

Schedule::command('mehanizacija:podsjetnici')->dailyAt('07:00');

Artisan::command('push:upozorenja', function () {
    $this->info('Poslano korisnicima: ' . \App\Support\PushUpozorenja::posalji());
})->purpose('Push obavijest o novim stavkama iz zvona: karence i skladište');

Schedule::command('push:upozorenja')->dailyAt('07:30');

Artisan::command('kalendar:jutro', function () {
    $this->info('Poslano korisnicima: ' . \App\Support\KalendarObavijesti::jutro());
})->purpose('Push: što je danas planirano u kalendaru');

Artisan::command('kalendar:vecer', function () {
    $this->info('Poslano korisnicima: ' . \App\Support\KalendarObavijesti::vecer());
})->purpose('Push: je li sutra povoljno za prskanje koje čeka (plan ili preporuka)');

Schedule::command('kalendar:jutro')->dailyAt('06:30')->timezone('Europe/Zagreb');
Schedule::command('kalendar:vecer')->dailyAt('18:00')->timezone('Europe/Zagreb');

// red čekanja (obavijesti mailom): obrađuje se svake minute preko cron schedule:run
Schedule::command('queue:work database --stop-when-empty --max-time=50 --tries=3')->everyMinute()->withoutOverlapping();
