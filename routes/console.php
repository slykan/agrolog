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

// Primjeri oglasa za naslovnicu ("Izdvajamo proizvode") na prezentaciji jednog korisnika; --obrisi ih makne (po nazivu)
Artisan::command('trznica:primjeri {email} {--obrisi}', function (string $email) {
    $primjeri = [
        ['Bagremov med', 'Med', 9.50, 'staklenka', 'Domaći bagremov med, staklenka 900 g.'],
        ['Jabuke Idared', 'Jabuke', 1.20, 'kg', 'Jabuke iz vlastitog voćnjaka, ručno brane.'],
        ['Graševina 2025.', 'Vino', 7.00, 'boca', 'Suha graševina, boca 0,75 L.'],
        ['Domaća jaja', 'Jaja', 0.35, 'kom', 'Jaja kokoši s ispaše.'],
        ['Ekstra djevičansko maslinovo ulje', 'Maslinovo ulje', 16.00, 'L', 'Hladno prešano, berba 2025.'],
        ['Kravlji sir', 'Mlijeko i sir', 8.00, 'kg', 'Svježi domaći sir.'],
        ['Krumpir', 'Povrće', 0.80, 'kg', 'Mladi krumpir, pakiranje po 10 kg.'],
        ['Šljivovica', 'Rakija i likeri', 15.00, 'boca', 'Domaća šljivovica, boca 0,5 L.'],
    ];
    $user = \App\Models\User::where('email', $email)->first();
    $prez = $user ? \App\Models\Prezentacija::where('user_id', $user->id)->first() : null;
    if (! $prez) {
        $this->error('Korisnik nema prezentaciju gospodarstva (Profil → Predstavite gospodarstvo).');
        return 1;
    }
    $nazivi = array_column($primjeri, 0);
    if ($this->option('obrisi')) {
        $n = 0;
        foreach (\App\Models\Proizvod::where('prezentacija_id', $prez->id)->whereIn('naziv', $nazivi)->get() as $p) {
            \App\Models\Prezentacija::obrisiSliku($p->slika);
            $p->delete();
            $n++;
        }
        $this->info("Obrisano primjera: $n");
        return 0;
    }
    $n = 0;
    foreach ($primjeri as [$naziv, $vrsta, $cijena, $jedinica, $opis]) {
        $p = \App\Models\Proizvod::firstOrCreate(['prezentacija_id' => $prez->id, 'naziv' => $naziv],
            ['vrsta' => $vrsta, 'cijena' => $cijena, 'jedinica' => $jedinica, 'opis' => $opis, 'dostupno' => true]);
        $n += $p->wasRecentlyCreated ? 1 : 0;
    }
    $this->info("Dodano primjera: $n (prezentacija: {$prez->naziv}, " . ($prez->jeJavna() ? 'javna' : 'NIJE javna: ' . $prez->status() . ', proizvodi se neće vidjeti') . ')');
})->purpose('Dodaj (ili --obrisi) 8 primjera proizvoda na Tržnici za korisnika');

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
