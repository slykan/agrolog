<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\EvidencijaIzvozController;
use App\Http\Controllers\ObavijestController;
use App\Http\Controllers\ParcelaArkodController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SadrzajController;
use Illuminate\Support\Facades\Route;

Route::get('/', function (\Illuminate\Http\Request $request) {
    // u mobilnoj aplikaciji prijavljeni korisnik s naslovnice (logo, poveznice) ide na Početnu; u pregledniku ostaje naslovnica
    if ($request->user() && str_contains((string) $request->userAgent(), 'AgroLogNative/')) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::get('/politika-privatnosti', function () {
    return view('privacy');
})->name('privacy');
Route::view('/uvjeti-koristenja', 'pravno.uvjeti')->name('uvjeti');
Route::post('/oglas/{oglas}/{mjesto}/prikaz', [\App\Http\Controllers\OglasController::class, 'prikaz'])->name('oglas.prikaz');
Route::get('/oglas/{oglas}/{mjesto}/klik', [\App\Http\Controllers\OglasController::class, 'klik'])->name('oglas.klik');
Route::view('/kolacici', 'pravno.kolacici')->name('kolacici');
Route::view('/brisanje-racuna', 'pravno.brisanje')->name('brisanje');

Route::get('/o-nama', function () {
    return view('about');
})->name('about');

Route::get('/kontakt', [\App\Http\Controllers\KontaktController::class, 'pokazi'])->name('contact');
Route::get('/oglasavanje', [\App\Http\Controllers\KontaktController::class, 'oglasavanje'])->name('oglasavanje');
Route::post('/kontakt', [\App\Http\Controllers\KontaktController::class, 'posalji'])->name('contact.posalji');

Route::get('/upute', function () {
    return view('docs');
})->name('docs');

Route::get('/alati', [SadrzajController::class, 'alati'])->name('alati');
Route::get('/alati/{slug}', [SadrzajController::class, 'alat'])->name('alat');
Route::get('/novosti', [SadrzajController::class, 'novostiIndex'])->name('novosti');
Route::get('/novosti/{slug}', [SadrzajController::class, 'novost'])->name('novost');
Route::get('/vodici', [SadrzajController::class, 'vodiciIndex'])->name('vodici');
Route::get('/vodici/{slug}', [SadrzajController::class, 'vodic'])->name('vodic');

Route::get('/sitemap.xml', function () {
    $urls = [
        ['loc' => route('home'),    'priority' => '1.0', 'changefreq' => 'weekly'],
        ['loc' => route('about'),   'priority' => '0.7', 'changefreq' => 'monthly'],
        ['loc' => route('contact'), 'priority' => '0.6', 'changefreq' => 'monthly'],
        ['loc' => route('oglasavanje'), 'priority' => '0.5', 'changefreq' => 'monthly'],
        ['loc' => route('docs'),    'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => route('privacy'), 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['loc' => route('uvjeti'), 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['loc' => route('kolacici'), 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['loc' => route('brisanje'), 'priority' => '0.3', 'changefreq' => 'yearly'],
    ];
    $urls[] = ['loc' => route('alati'), 'priority' => '0.9', 'changefreq' => 'monthly'];
    foreach (array_keys(SadrzajController::ALATI) as $s) {
        $urls[] = ['loc' => route('alat', $s), 'priority' => '0.9', 'changefreq' => 'monthly'];
    }
    $urls[] = ['loc' => route('novosti'), 'priority' => '0.8', 'changefreq' => 'weekly'];
    foreach (SadrzajController::novosti() as $n) {
        $urls[] = ['loc' => route('novost', $n['slug']), 'priority' => '0.8', 'changefreq' => 'monthly'];
    }
    $urls[] = ['loc' => route('vodici'), 'priority' => '0.8', 'changefreq' => 'weekly'];
    foreach (SadrzajController::vodici() as $v) {
        $urls[] = ['loc' => route('vodic', $v['slug']), 'priority' => '0.8', 'changefreq' => 'monthly'];
    }
    $urls[] = ['loc' => route('gospodarstva'), 'priority' => '0.8', 'changefreq' => 'daily'];
    $urls[] = ['loc' => route('trznica'), 'priority' => '0.8', 'changefreq' => 'daily'];
    foreach (\App\Models\Proizvod::javni()->pluck('id') as $id) {
        $urls[] = ['loc' => route('trznica.proizvod', $id), 'priority' => '0.5', 'changefreq' => 'weekly'];
    }
    foreach (\App\Models\Prezentacija::javne()->pluck('slug') as $s) {
        $urls[] = ['loc' => route('gospodarstvo', $s), 'priority' => '0.6', 'changefreq' => 'weekly'];
    }
    return response()->view('sitemap', compact('urls'))
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/parcele', fn() => view('parcele'))->name('parcele');
    Route::get('/gnojidba', fn() => view('gnojidba'))->name('gnojidba');
    Route::get('/prskanje', fn() => view('prskanje'))->name('prskanje');
    Route::get('/berba', fn() => view('berba'))->name('berba');
    Route::get('/radovi', fn() => view('radovi'))->name('radovi');
    Route::get('/kalendar', fn() => view('kalendar'))->name('kalendar');
    Route::get('/prskanje/izvoz.csv', [EvidencijaIzvozController::class, 'prskanja'])->name('prskanje.csv');
    Route::get('/parcele/{parcela}/arkod', ParcelaArkodController::class)->name('parcela.arkod');
    Route::get('/skladiste', fn() => view('skladiste'))->name('skladiste');
    Route::get('/mehanizacija', fn() => view('mehanizacija'))->name('mehanizacija');
    Route::get('/troskovi', fn() => view('troskovi'))->name('troskovi');
    Route::get('/izvjestaji', fn() => view('izvjestaji'))->name('izvjestaji');
    Route::get('/izvjestaji/ispis/{vrsta}', [\App\Http\Controllers\IzvjestajiController::class, 'ispis'])->name('izvjestaji.ispis');
    Route::get('/izvjestaji/pdf/{vrsta}', [\App\Http\Controllers\IzvjestajiController::class, 'pdf'])->middleware('throttle:20,1')->name('izvjestaji.pdf');
    Route::get('/izvjestaji/gnojidba.csv', [\App\Http\Controllers\IzvjestajiController::class, 'gnojidbeCsv'])->name('izvjestaji.gnojidba.csv');
    Route::get('/izvjestaji/berba.csv', [\App\Http\Controllers\IzvjestajiController::class, 'berbeCsv'])->name('izvjestaji.berba.csv');
    Route::get('/izvjestaji/skladiste.csv', [\App\Http\Controllers\IzvjestajiController::class, 'skladisteCsv'])->name('izvjestaji.skladiste.csv');
    Route::get('/prskanje/izvoz.xlsx', [EvidencijaIzvozController::class, 'prskanjaXlsx'])->name('prskanje.xlsx');

    Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/ponude', [\App\Http\Controllers\PonudaController::class, 'index'])->name('ponude');
        Route::post('/ponude', [\App\Http\Controllers\PonudaController::class, 'posalji'])->name('ponude.posalji');
        Route::post('/ponude/{ponuda}/ne-zeli', [\App\Http\Controllers\PonudaController::class, 'neZeli'])->name('ponude.ne-zeli');
        Route::get('/oglasi', [\App\Http\Controllers\OglasController::class, 'index'])->name('oglasi');
        Route::post('/oglasi', [\App\Http\Controllers\OglasController::class, 'spremi'])->name('oglasi.spremi');
        Route::post('/oglasi/{oglas}', [\App\Http\Controllers\OglasController::class, 'spremi'])->name('oglasi.azuriraj');
        Route::delete('/oglasi/{oglas}', [\App\Http\Controllers\OglasController::class, 'obrisi'])->name('oglasi.obrisi');
        Route::get('/korisnici', [AdminController::class, 'korisnici'])->name('korisnici');
        Route::get('/korisnici/{user}', [AdminController::class, 'korisnik'])->name('korisnik');
        Route::post('/korisnici/{user}/obavijesti', [ObavijestController::class, 'prekidac'])->name('korisnik.obavijesti');
        Route::get('/obavijesti', [ObavijestController::class, 'index'])->name('obavijesti');
        Route::post('/obavijesti', [ObavijestController::class, 'posalji'])->name('obavijesti.posalji');
    });

    // savjetnik: gospodarstvo poziva iz profila; savjetnik radi u "Klijenti"
    Route::post('/savjetnici', [\App\Http\Controllers\PristupController::class, 'posalji'])->name('savjetnici.posalji');
    Route::patch('/savjetnici/{pristup}', [\App\Http\Controllers\PristupController::class, 'prava'])->name('savjetnici.prava');
    Route::post('/savjetnici/{pristup}/ponovno', [\App\Http\Controllers\PristupController::class, 'ponovno'])->name('savjetnici.ponovno');
    Route::delete('/savjetnici/{pristup}', [\App\Http\Controllers\PristupController::class, 'ukini'])->name('savjetnici.ukini');
    Route::post('/poziv-savjetnika/{token}', [\App\Http\Controllers\PristupController::class, 'prihvati'])->name('poziv.prihvati');
    Route::get('/klijenti', [\App\Http\Controllers\PristupController::class, 'klijenti'])->name('klijenti');
    Route::post('/klijenti/{pristup}/otvori', [\App\Http\Controllers\PristupController::class, 'otvori'])->name('klijenti.otvori');
    Route::post('/klijenti/natrag', [\App\Http\Controllers\PristupController::class, 'zatvori'])->name('klijenti.zatvori');

    // preporuke: agronom šalje s Klijenata, gospodarstvo odgovara (u tuđem gospodarstvu preporuke.* nisu dostupne)
    Route::get('/klijenti/preporuke', [\App\Http\Controllers\PreporukaController::class, 'savjetnik'])->name('klijenti.preporuke');
    Route::post('/klijenti/preporuke', [\App\Http\Controllers\PreporukaController::class, 'posalji'])->middleware('throttle:30,1')->name('klijenti.preporuke.posalji');
    Route::post('/klijenti/preporuke/{preporuka}/odgovor', [\App\Http\Controllers\PreporukaController::class, 'odgovori'])->name('klijenti.preporuke.odgovori');
    Route::post('/klijenti/preporuke/{preporuka}/povuci', [\App\Http\Controllers\PreporukaController::class, 'povuci'])->name('klijenti.preporuke.povuci');
    Route::get('/preporuke', [\App\Http\Controllers\PreporukaController::class, 'index'])->name('preporuke.index');
    Route::post('/preporuke/{preporuka}/odgovor', [\App\Http\Controllers\PreporukaController::class, 'odgovor'])->middleware('throttle:60,1')->name('preporuke.odgovor');

    // savjetnik poziva poljoprivrednike; pristup nastaje tek kad poljoprivrednik prihvati
    Route::post('/klijenti/pozivi', [\App\Http\Controllers\PozivGospodarstvuController::class, 'posalji'])->middleware('throttle:30,1')->name('klijenti.pozivi.posalji');
    Route::post('/klijenti/pozivi/{poziv}/ponovno', [\App\Http\Controllers\PozivGospodarstvuController::class, 'ponovno'])->middleware('throttle:30,1')->name('klijenti.pozivi.ponovno');
    Route::delete('/klijenti/pozivi/{poziv}', [\App\Http\Controllers\PozivGospodarstvuController::class, 'povuci'])->name('klijenti.pozivi.povuci');
    Route::post('/poziv-gospodarstvu/{token}', [\App\Http\Controllers\PozivGospodarstvuController::class, 'prihvati'])->name('poziv.gospodarstvo.prihvati');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/obavijesti', [ProfileController::class, 'obavijesti'])->name('profile.obavijesti');
    Route::post('/push/pretplata', [\App\Http\Controllers\PushController::class, 'spremi'])->name('push.spremi');
    Route::post('/push/mobile/device', [\App\Http\Controllers\MobilePushController::class, 'register'])->middleware('throttle:30,1')->name('push.mobile.register');
    Route::post('/push/mobile/test', [\App\Http\Controllers\MobilePushController::class, 'test'])->middleware('throttle:5,1')->name('push.mobile.test');
    Route::delete('/push/pretplata', [\App\Http\Controllers\PushController::class, 'ukloni'])->name('push.ukloni');
    Route::post('/push/proba', [\App\Http\Controllers\PushController::class, 'proba'])->middleware('throttle:5,1')->name('push.proba');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// poveznica iz maila za savjetnika (radi i bez prijave: nudi prijavu ili registraciju)
Route::get('/poziv-savjetnika/{token}', [\App\Http\Controllers\PristupController::class, 'poziv'])->name('poziv.savjetnik');

// poveznica iz maila za poljoprivrednika: bez prijave nudi prijavu, registraciju ili odbijanje
Route::get('/poziv-gospodarstvu/{token}', [\App\Http\Controllers\PozivGospodarstvuController::class, 'poziv'])->name('poziv.gospodarstvo');
Route::post('/poziv-gospodarstvu/{token}/odbij', [\App\Http\Controllers\PozivGospodarstvuController::class, 'odbij'])->middleware('throttle:30,1')->name('poziv.gospodarstvo.odbij');

// odjava od obavijesti iz maila (potpisani link; POST za jednoklik odjavu iz mail klijenta, bez CSRF-a)
Route::match(['get', 'post'], '/obavijesti/odjava/{user}', [ObavijestController::class, 'odjava'])
    ->middleware('signed')
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
    ->name('obavijesti.odjava');

require __DIR__.'/auth.php';
