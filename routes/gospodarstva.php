<?php

use App\Http\Controllers\PrezentacijaController;
use Illuminate\Support\Facades\Route;

// Javni popis gospodarstava (učitava se iz bootstrap/app.php uz web middleware)
Route::get('/gospodarstva', [PrezentacijaController::class, 'index'])->name('gospodarstva');
Route::get('/gospodarstva/{slug}', [PrezentacijaController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('gospodarstvo');
Route::get('/gospodarstva/{slug}/telefon', [PrezentacijaController::class, 'telefon'])->where('slug', '[a-z0-9-]+')
    ->middleware('throttle:20,1')->name('gospodarstvo.telefon');

Route::middleware('auth')->group(function () {
    // u profilu (profile.* je zabranjen savjetniku u klijentovom gospodarstvu)
    Route::post('/profile/prezentacija', [PrezentacijaController::class, 'spremi'])->middleware('throttle:20,1')->name('profile.prezentacija');
    Route::delete('/profile/prezentacija', [PrezentacijaController::class, 'obrisi'])->name('profile.prezentacija.obrisi');

    Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/gospodarstva', [PrezentacijaController::class, 'admin'])->name('gospodarstva');
        Route::post('/gospodarstva/{prezentacija}/odobri', [PrezentacijaController::class, 'odobri'])->name('gospodarstva.odobri');
        Route::post('/gospodarstva/{prezentacija}/odbij', [PrezentacijaController::class, 'odbij'])->name('gospodarstva.odbij');
    });
});
