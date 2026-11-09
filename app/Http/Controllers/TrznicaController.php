<?php

namespace App\Http\Controllers;

use App\Models\Prezentacija;
use App\Models\Proizvod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Tržnica: proizvodi gospodarstava s javnog popisa. Oglas uvijek vodi na prodavača (kontakt iz prezentacije).
 * Bez košarice i plaćanja: dogovor ide izravno s gospodarstvom.
 */
class TrznicaController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $vrsta = in_array($request->query('vrsta'), Prezentacija::OZNAKE, true) ? $request->query('vrsta') : null;
        $zupanija = in_array($request->query('zupanija'), Prezentacija::ZUPANIJE, true) ? $request->query('zupanija') : null;
        $prodavac = $request->query('prodavac') ? Prezentacija::javne()->where('slug', (string) $request->query('prodavac'))->first() : null;

        $proizvodi = Proizvod::javni()->with('prezentacija')
            ->when($q !== '', fn ($u) => $u->where(fn ($w) => $w->where('naziv', 'like', "%{$q}%")->orWhere('opis', 'like', "%{$q}%")
                ->orWhereHas('prezentacija', fn ($p) => $p->where('mjesto', 'like', "%{$q}%")->orWhere('naziv', 'like', "%{$q}%"))))
            ->when($vrsta, fn ($u) => $u->where('vrsta', $vrsta))
            ->when($zupanija, fn ($u) => $u->whereHas('prezentacija', fn ($p) => $p->where('zupanija', $zupanija)))
            ->when($prodavac, fn ($u) => $u->where('prezentacija_id', $prodavac->id))
            ->latest('updated_at')->paginate(24)->withQueryString();

        return view('trznica.index', [
            'proizvodi' => $proizvodi, 'q' => $q, 'vrsta' => $vrsta, 'zupanija' => $zupanija, 'prodavac' => $prodavac,
            'filtrirano' => $q !== '' || $vrsta || $zupanija || $prodavac,
        ]);
    }

    public function show(Proizvod $proizvod): View
    {
        abort_unless($proizvod->dostupno && $proizvod->prezentacija?->jeJavna(), 404);
        $p = $proizvod->prezentacija;

        return view('trznica.show', [
            'proizvod' => $proizvod, 'p' => $p,
            'jos' => $p->proizvodi()->where('dostupno', true)->where('id', '!=', $proizvod->id)->latest('updated_at')->take(6)->get(),
            'ukupno' => $p->proizvodi()->where('dostupno', true)->count(),
        ]);
    }

    // ---------------- moji proizvodi

    public function moji(Request $request): View
    {
        $p = Prezentacija::where('user_id', $request->user()->id)->first();
        $uredi = $request->query('uredi') && $p ? $p->proizvodi()->find((int) $request->query('uredi')) : null;

        return view('trznica.moji', [
            'p' => $p, 'proizvodi' => $p ? $p->proizvodi()->latest('updated_at')->get() : collect(), 'uredi' => $uredi,
        ]);
    }

    public function spremi(Request $request, ?Proizvod $proizvod = null): RedirectResponse
    {
        $p = Prezentacija::where('user_id', $request->user()->id)->where('vidljivo', true)->firstOrFail();
        if ($proizvod) {
            abort_unless($proizvod->prezentacija_id === $p->id, 403);
        } elseif ($p->proizvodi()->count() >= Proizvod::NAJVISE) {
            return back()->withErrors(['naziv' => 'Možete imati najviše ' . Proizvod::NAJVISE . ' proizvoda. Obrišite one koje više ne nudite.'])->withInput();
        }

        $request->merge(['cijena' => str_replace(',', '.', (string) $request->input('cijena'))]);   // 8,50 → 8.50
        $v = $request->validate([
            'naziv' => 'required|string|min:2|max:100',
            'vrsta' => ['required', Rule::in(Prezentacija::OZNAKE)],
            'cijena' => 'required|numeric|min:0|max:1000000',
            'jedinica' => ['required', Rule::in(array_keys(Proizvod::JEDINICE))],
            'opis' => 'nullable|string|max:1500',
            'slika' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
            'ukloni_sliku' => 'nullable|boolean',
        ], [], ['naziv' => 'naziv', 'vrsta' => 'vrsta', 'cijena' => 'cijena', 'jedinica' => 'jedinica', 'opis' => 'opis', 'slika' => 'slika']);

        $podaci = ['naziv' => trim($v['naziv']), 'vrsta' => $v['vrsta'], 'cijena' => round((float) $v['cijena'], 2),
            'jedinica' => $v['jedinica'], 'opis' => blank($v['opis'] ?? null) ? null : trim($v['opis']), 'dostupno' => $request->boolean('dostupno', true)];
        if ($request->hasFile('slika')) {
            Prezentacija::obrisiSliku($proizvod?->slika);
            $podaci['slika'] = Prezentacija::spremiSliku($request->file('slika'));
        } elseif ($request->boolean('ukloni_sliku')) {
            Prezentacija::obrisiSliku($proizvod?->slika);
            $podaci['slika'] = null;
        }

        $proizvod ? $proizvod->update($podaci) : $p->proizvodi()->create($podaci);

        return redirect()->route('trznica.moji')->with('status', 'Proizvod je spremljen.');
    }

    public function dostupnost(Request $request, Proizvod $proizvod): RedirectResponse
    {
        $this->mojProizvod($request, $proizvod);
        $proizvod->update(['dostupno' => ! $proizvod->dostupno]);

        return back();
    }

    public function obrisi(Request $request, Proizvod $proizvod): RedirectResponse
    {
        $this->mojProizvod($request, $proizvod);
        Prezentacija::obrisiSliku($proizvod->slika);
        $proizvod->delete();

        return redirect()->route('trznica.moji')->with('status', 'Proizvod je obrisan.');
    }

    private function mojProizvod(Request $request, Proizvod $proizvod): void
    {
        abort_unless($proizvod->prezentacija?->user_id === $request->user()->id, 403);
    }
}
