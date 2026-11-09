<?php

use App\Http\Controllers\PrezentacijaController;
use Illuminate\Support\Facades\Route;

// Javni popis gospodarstava (učitava se iz bootstrap/app.php uz web middleware)
Route::get('/gospodarstva', [PrezentacijaController::class, 'index'])->name('gospodarstva');
Route::get('/gospodarstva/{slug}', [PrezentacijaController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('gospodarstvo');
Route::get('/gospodarstva/{slug}/telefon', [PrezentacijaController::class, 'telefon'])->where('slug', '[a-z0-9-]+')
    ->middleware('throttle:20,1')->name('gospodarstvo.telefon');

// Tržnica: proizvodi gospodarstava s popisa
Route::get('/trznica', [\App\Http\Controllers\TrznicaController::class, 'index'])->name('trznica');
Route::get('/trznica/proizvod/{proizvod}', [\App\Http\Controllers\TrznicaController::class, 'show'])->whereNumber('proizvod')->name('trznica.proizvod');

Route::middleware('auth')->group(function () {
    // moji proizvodi (trznica.moji* je zabranjen u klijentovom gospodarstvu)
    Route::get('/trznica/moji', [\App\Http\Controllers\TrznicaController::class, 'moji'])->name('trznica.moji');
    Route::post('/trznica/moji', [\App\Http\Controllers\TrznicaController::class, 'spremi'])->middleware('throttle:30,1')->name('trznica.moji.spremi');
    Route::post('/trznica/moji/{proizvod}', [\App\Http\Controllers\TrznicaController::class, 'spremi'])->middleware('throttle:30,1')->name('trznica.moji.azuriraj');
    Route::post('/trznica/moji/{proizvod}/dostupnost', [\App\Http\Controllers\TrznicaController::class, 'dostupnost'])->name('trznica.moji.dostupnost');
    Route::delete('/trznica/moji/{proizvod}', [\App\Http\Controllers\TrznicaController::class, 'obrisi'])->name('trznica.moji.obrisi');

    // u profilu (profile.* je zabranjen savjetniku u klijentovom gospodarstvu)
    Route::post('/profile/prezentacija', [PrezentacijaController::class, 'spremi'])->middleware('throttle:20,1')->name('profile.prezentacija');
    Route::delete('/profile/prezentacija', [PrezentacijaController::class, 'obrisi'])->name('profile.prezentacija.obrisi');
    Route::post('/profile/prezentacija/odustani', [PrezentacijaController::class, 'odustani'])->name('profile.prezentacija.odustani');
    // obrazac odmah nakon registracije (prezentacija.* je zabranjen u klijentovom gospodarstvu, vidi GospodarstvoSavjetnika)
    Route::get('/predstavite-gospodarstvo', [PrezentacijaController::class, 'uvod'])->name('prezentacija.uvod');

    Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/gospodarstva', [PrezentacijaController::class, 'admin'])->name('gospodarstva');
        Route::post('/gospodarstva/{prezentacija}/odobri', [PrezentacijaController::class, 'odobri'])->name('gospodarstva.odobri');
        Route::post('/gospodarstva/{prezentacija}/odbij', [PrezentacijaController::class, 'odbij'])->name('gospodarstva.odbij');
        Route::post('/korisnici/{user}/pozovi-na-popis', [PrezentacijaController::class, 'pozovi'])->name('korisnik.pozovi-popis');
    });
});
