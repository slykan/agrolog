<?php

namespace App\Http\Controllers;

use App\Models\Oglas;
use App\Support\Oglasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class OglasController extends Controller
{
    /** Oglas je stvarno bio vidljiv na ekranu (šalje preglednik). */
    public function prikaz(Request $request, Oglas $oglas, string $mjesto): Response
    {
        abort_unless(isset(Oglasi::MJESTA[$mjesto]), 404);
        if (! Oglasi::jeBot($request->userAgent()) && $this->smijeBrojati($request, 'prikaz', 60)) {
            Oglasi::zabiljezi($oglas->id, $mjesto, 'prikazi');
        }

        return response()->noContent();
    }

    /** Klik: broji se i odmah vodi dalje. I kad je brojanje ograničeno, posjetitelj stiže na odredište. */
    public function klik(Request $request, Oglas $oglas, string $mjesto): RedirectResponse
    {
        abort_unless(isset(Oglasi::MJESTA[$mjesto]), 404);
        if (! Oglasi::jeBot($request->userAgent()) && $this->smijeBrojati($request, 'klik', 20)) {
            Oglasi::zabiljezi($oglas->id, $mjesto, 'klikovi');
        }

        return redirect()->to($oglas->url)->withHeaders(['X-Robots-Tag' => 'noindex']);
    }

    private function smijeBrojati(Request $request, string $vrsta, int $uMinuti): bool
    {
        $kljuc = 'oglas-' . $vrsta . ':' . sha1((string) $request->ip());
        if (RateLimiter::tooManyAttempts($kljuc, $uMinuti)) {
            return false;
        }
        RateLimiter::hit($kljuc, 60);

        return true;
    }

    // ---- admin ----

    public function index(Request $request): View
    {
        $period = in_array($request->query('period'), ['30', 'godina', 'sve'], true) ? $request->query('period') : '30';
        $od = match ($period) { '30' => now()->subDays(29)->toDateString(), 'godina' => now()->startOfYear()->toDateString(), 'sve' => null };

        $redovi = DB::table('oglas_statistika')->when($od, fn ($q) => $q->where('datum', '>=', $od))->get();
        $oglasi = Oglas::orderBy('rezervni')->latest('id')->get();
        $nazivi = $oglasi->pluck('naziv', 'id');
        $poMjestu = $redovi->groupBy(fn ($r) => $r->oglas_id . '|' . $r->mjesto)->map(fn ($g) => [
            'oglas' => $nazivi[$g->first()->oglas_id] ?? '', 'mjesto' => Oglasi::NAZIVI[$g->first()->mjesto] ?? $g->first()->mjesto,
            'prikazi' => (int) $g->sum('prikazi'), 'klikovi' => (int) $g->sum('klikovi'),
        ])->sortByDesc('prikazi')->values();
        $zbroj = $redovi->groupBy('oglas_id')->map(fn ($g) => ['prikazi' => (int) $g->sum('prikazi'), 'klikovi' => (int) $g->sum('klikovi')]);

        return view('admin.oglasi', [
            'oglasi' => $oglasi, 'period' => $period, 'poMjestu' => $poMjestu, 'zbroj' => $zbroj,
            'uredi' => $request->filled('uredi') ? Oglas::find((int) $request->query('uredi')) : null,
            'nova' => $request->boolean('nova'),
        ]);
    }

    public function spremi(Request $request, ?Oglas $oglas = null): RedirectResponse
    {
        $novi = ! $oglas?->exists;
        $slika = ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096', 'dimensions:min_width=300'];
        $d = $request->validate([
            'naziv' => 'required|string|max:120',
            'url' => ['required', 'string', 'max:500', 'regex:#^(https?://[^\s]+|/(?!/)[^\s]*)$#i'],
            'slika' => [$novi ? 'required' : 'nullable', ...$slika],
            'slika_mobitel' => ['nullable', ...$slika],
            'od' => 'nullable|date',
            'do' => 'nullable|date|after_or_equal:od',
        ], [], ['naziv' => 'naziv', 'url' => 'poveznica', 'slika' => 'slika', 'slika_mobitel' => 'slika za mobitel', 'od' => 'datum od', 'do' => 'datum do']);

        $podaci = [
            'naziv' => $d['naziv'], 'url' => $d['url'], 'od' => $d['od'] ?? null, 'do' => $d['do'] ?? null,
            'na_naslovnoj' => $request->boolean('na_naslovnoj'), 'na_dashboardu' => $request->boolean('na_dashboardu'),
            'na_clancima' => $request->boolean('na_clancima'), 'aktivan' => $request->boolean('aktivan'), 'rezervni' => $request->boolean('rezervni'),
        ];
        if ($request->hasFile('slika')) {
            Oglasi::obrisiSliku($oglas?->slika);
            [$podaci['slika'], $podaci['slika_w'], $podaci['slika_h']] = Oglasi::spremiSliku($request->file('slika'));
        }
        if ($request->hasFile('slika_mobitel')) {
            Oglasi::obrisiSliku($oglas?->slika_mobitel);
            [$podaci['slika_mobitel'], $podaci['mobitel_w'], $podaci['mobitel_h']] = Oglasi::spremiSliku($request->file('slika_mobitel'));
        } elseif ($request->boolean('makni_mobitel') && $oglas) {
            Oglasi::obrisiSliku($oglas->slika_mobitel);
            $podaci += ['slika_mobitel' => null, 'mobitel_w' => null, 'mobitel_h' => null];
        }

        $novi ? Oglas::create($podaci) : $oglas->update($podaci);

        return redirect()->route('admin.oglasi')->with('poruka', 'Oglas je spremljen.');
    }

    public function obrisi(Oglas $oglas): RedirectResponse
    {
        Oglasi::obrisiSliku($oglas->slika);
        Oglasi::obrisiSliku($oglas->slika_mobitel);
        $oglas->delete();

        return redirect()->route('admin.oglasi')->with('poruka', 'Oglas i njegova statistika su obrisani.');
    }
}
