<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\EvidencijaIzvozController;
use App\Http\Controllers\ObavijestController;
use App\Http\Controllers\ParcelaArkodController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SadrzajController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/politika-privatnosti', function () {
    return view('privacy');
})->name('privacy');

Route::get('/o-nama', function () {
    return view('about');
})->name('about');

Route::get('/kontakt', function () {
    return view('contact');
})->name('contact');

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
        ['loc' => route('docs'),    'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => route('privacy'), 'priority' => '0.3', 'changefreq' => 'yearly'],
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
    Route::get('/prskanje/izvoz.csv', [EvidencijaIzvozController::class, 'prskanja'])->name('prskanje.csv');
    Route::get('/parcele/{parcela}/arkod', ParcelaArkodController::class)->name('parcela.arkod');
    Route::get('/skladiste', fn() => view('skladiste'))->name('skladiste');
    Route::get('/prskanje/izvoz.xlsx', [EvidencijaIzvozController::class, 'prskanjaXlsx'])->name('prskanje.xlsx');

    Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/korisnici', [AdminController::class, 'korisnici'])->name('korisnici');
        Route::get('/korisnici/{user}', [AdminController::class, 'korisnik'])->name('korisnik');
        Route::post('/korisnici/{user}/obavijesti', [ObavijestController::class, 'prekidac'])->name('korisnik.obavijesti');
        Route::get('/obavijesti', [ObavijestController::class, 'index'])->name('obavijesti');
        Route::post('/obavijesti', [ObavijestController::class, 'posalji'])->name('obavijesti.posalji');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/skladiste', [ProfileController::class, 'skladiste'])->name('profile.skladiste');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// odjava od obavijesti iz maila (potpisani link; POST za jednoklik odjavu iz mail klijenta, bez CSRF-a)
Route::match(['get', 'post'], '/obavijesti/odjava/{user}', [ObavijestController::class, 'odjava'])
    ->middleware('signed')
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
    ->name('obavijesti.odjava');

require __DIR__.'/auth.php';
