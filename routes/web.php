<?php

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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
