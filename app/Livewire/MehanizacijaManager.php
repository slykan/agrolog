<?php

namespace App\Livewire;

use App\Models\Stroj;
use App\Models\StrojRok;
use App\Models\StrojServis;
use App\Support\Mehanizacija;
use Illuminate\Support\Carbon;
use Livewire\Component;

class MehanizacijaManager extends Component
{
    /** Otvorena forma: null | stroj | rok | servis */
    public ?string $forma = null;
    public ?int $urediId = null;
    /** Rok koji se obnavlja (označi se obnovljenim tek kad se novi rok spremi). */
    public ?int $obnavljaId = null;
    public array $f = [];
    /** Stroj s otvorenom poviješću (servisi i obnovljeni rokovi). */
    public ?int $otvoren = null;

    protected array $validationAttributes = [
        'f.naziv' => 'naziv', 'f.vrsta' => 'vrsta', 'f.registracija' => 'registracija', 'f.godina' => 'godina',
        'f.datum_isteka' => 'datum isteka', 'f.podsjetnik_dana' => 'podsjetnik', 'f.iznos' => 'iznos',
        'f.datum' => 'datum', 'f.opis' => 'opis', 'f.radni_sati' => 'radni sati', 'f.trosak' => 'trošak',
    ];

    private function otvoriFormu(string $forma, array $f, ?int $id = null): void
    {
        $this->forma = $forma;
        $this->urediId = $id;
        $this->f = $f;
        $this->resetErrorBag();
        $this->js('window.scrollTo({ top: 0, behavior: "smooth" })');
    }

    public function odustani(): void
    {
        $this->forma = null;
        $this->urediId = null;
        $this->obnavljaId = null;
        $this->f = [];
        $this->resetErrorBag();
    }

    private function stroj(int $id): Stroj
    {
        return Stroj::where('user_id', auth()->id())->findOrFail($id);
    }

    // ---- strojevi ----

    public function noviStroj(): void
    {
        $this->otvoriFormu('stroj', ['vrsta' => 'traktor', 'naziv' => '', 'registracija' => '', 'godina' => '', 'serijski_broj' => '', 'napomena' => '', 'rokovi' => []]);
    }

    public function urediStroj(int $id): void
    {
        $s = $this->stroj($id);
        $this->otvoriFormu('stroj', $s->only(['vrsta', 'naziv', 'registracija', 'godina', 'serijski_broj', 'napomena']) + ['rokovi' => []], $s->id);
    }

    public function spremiStroj(): void
    {
        $this->validate([
            'f.vrsta' => 'required|in:' . implode(',', array_keys(Mehanizacija::VRSTE)),
            'f.naziv' => 'required|string|max:150',
            'f.registracija' => 'nullable|string|max:30',
            'f.godina' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'f.serijski_broj' => 'nullable|string|max:60',
            'f.napomena' => 'nullable|string|max:255',
            'f.rokovi.*' => 'nullable|date',
        ]);
        $podaci = collect($this->f)->only(['vrsta', 'naziv', 'registracija', 'godina', 'serijski_broj', 'napomena'])
            ->map(fn ($v) => blank($v) ? null : (is_string($v) ? trim($v) : $v))->all();
        $podaci['registracija'] = $podaci['registracija'] ? mb_strtoupper($podaci['registracija']) : null;

        if ($this->urediId) {
            $this->stroj($this->urediId)->update($podaci);
        } else {
            $s = Stroj::create($podaci + ['user_id' => auth()->id()]);
            // brzi unos rokova uz novi stroj (npr. registracija vrijedi do...)
            foreach ($this->f['rokovi'] ?? [] as $vrsta => $datum) {
                if (filled($datum) && isset(Mehanizacija::ROKOVI[$vrsta])) {
                    StrojRok::create(['user_id' => auth()->id(), 'stroj_id' => $s->id, 'vrsta' => $vrsta, 'datum_isteka' => $datum, 'podsjetnik_dana' => 30]);
                }
            }
        }
        $this->odustani();
    }

    public function obrisiStroj(int $id): void
    {
        $this->stroj($id)->delete();
    }

    // ---- rokovi ----

    public function noviRok(int $strojId, string $vrsta = 'registracija'): void
    {
        $this->stroj($strojId);
        $this->obnavljaId = null;
        $this->otvoriFormu('rok', ['stroj_id' => $strojId, 'vrsta' => $vrsta, 'naziv' => '', 'datum_isteka' => '', 'podsjetnik_dana' => 30, 'iznos' => '', 'napomena' => '']);
    }

    public function urediRok(int $id): void
    {
        $r = StrojRok::where('user_id', auth()->id())->findOrFail($id);
        $this->obnavljaId = null;
        $this->otvoriFormu('rok', [
            'stroj_id' => $r->stroj_id, 'vrsta' => $r->vrsta, 'naziv' => $r->naziv, 'datum_isteka' => $r->datum_isteka->format('Y-m-d'),
            'podsjetnik_dana' => $r->podsjetnik_dana, 'iznos' => $r->iznos, 'napomena' => $r->napomena,
        ], $r->id);
    }

    /** Obavljeno (registrirano, servisirano...): forma za sljedeći rok s predloženim datumom. */
    public function obnovi(int $id): void
    {
        $r = StrojRok::where('user_id', auth()->id())->whereNull('obnovljen_at')->findOrFail($id);
        $mjeseci = Mehanizacija::ROKOVI[$r->vrsta][1] ?? 12;
        $sljedeci = $r->datum_isteka->copy()->addMonthsNoOverflow($mjeseci);
        if ($sljedeci->isPast()) {   // davno istekao: računa se od danas
            $sljedeci = now()->addMonthsNoOverflow($mjeseci);
        }
        $this->otvoriFormu('rok', [
            'stroj_id' => $r->stroj_id, 'vrsta' => $r->vrsta, 'naziv' => $r->naziv, 'datum_isteka' => $sljedeci->format('Y-m-d'),
            'podsjetnik_dana' => $r->podsjetnik_dana, 'iznos' => $r->iznos, 'napomena' => '',
        ]);
        $this->obnavljaId = $r->id;
    }

    public function spremiRok(): void
    {
        $this->validate([
            'f.vrsta' => 'required|in:' . implode(',', array_keys(Mehanizacija::ROKOVI)),
            'f.naziv' => 'nullable|required_if:f.vrsta,ostalo|string|max:100',
            'f.datum_isteka' => 'required|date',
            'f.podsjetnik_dana' => 'required|integer|min:0|max:365',
            'f.iznos' => 'nullable|numeric|min:0|max:9999999',
            'f.napomena' => 'nullable|string|max:255',
        ]);
        $this->stroj((int) $this->f['stroj_id']);
        $podaci = [
            'vrsta' => $this->f['vrsta'], 'naziv' => $this->f['vrsta'] === 'ostalo' ? trim($this->f['naziv']) : null,
            'datum_isteka' => $this->f['datum_isteka'], 'podsjetnik_dana' => (int) $this->f['podsjetnik_dana'],
            'iznos' => blank($this->f['iznos']) ? null : $this->f['iznos'], 'napomena' => blank($this->f['napomena']) ? null : trim($this->f['napomena']),
        ];

        if ($this->urediId) {
            $r = StrojRok::where('user_id', auth()->id())->findOrFail($this->urediId);
            if (! $r->datum_isteka->isSameDay(Carbon::parse($podaci['datum_isteka'])) || $r->podsjetnik_dana !== $podaci['podsjetnik_dana']) {
                $podaci += ['podsjetnik_poslan_at' => null, 'istek_poslan_at' => null];   // novi datum: podsjetnik opet
            }
            $r->update($podaci);
        } else {
            StrojRok::create($podaci + ['user_id' => auth()->id(), 'stroj_id' => (int) $this->f['stroj_id']]);
            if ($this->obnavljaId) {
                StrojRok::where('user_id', auth()->id())->whereKey($this->obnavljaId)->update(['obnovljen_at' => now()]);
            }
        }
        $this->odustani();
    }

    public function obrisiRok(int $id): void
    {
        StrojRok::where('user_id', auth()->id())->whereKey($id)->delete();
    }

    // ---- servisna knjiga ----

    public function noviServis(int $strojId): void
    {
        $this->stroj($strojId);
        $imaRokServisa = StrojRok::where('user_id', auth()->id())->where('stroj_id', $strojId)->where('vrsta', 'servis')->whereNull('obnovljen_at')->exists();
        $this->otvoriFormu('servis', ['stroj_id' => $strojId, 'datum' => date('Y-m-d'), 'opis' => '', 'radni_sati' => '', 'trosak' => '', 'serviser' => '',
            'pomakni_rok' => $imaRokServisa, 'ima_rok' => $imaRokServisa]);
    }

    public function urediServis(int $id): void
    {
        $s = StrojServis::where('user_id', auth()->id())->findOrFail($id);
        $this->otvoriFormu('servis', $s->only(['stroj_id', 'opis', 'radni_sati', 'trosak', 'serviser']) + ['datum' => $s->datum->format('Y-m-d'), 'pomakni_rok' => false, 'ima_rok' => false], $s->id);
    }

    public function spremiServis(): void
    {
        $this->validate([
            'f.datum' => 'required|date',
            'f.opis' => 'required|string|max:255',
            'f.radni_sati' => 'nullable|integer|min:0|max:999999',
            'f.trosak' => 'nullable|numeric|min:0|max:9999999',
            'f.serviser' => 'nullable|string|max:150',
        ]);
        $strojId = (int) $this->f['stroj_id'];
        $this->stroj($strojId);
        $podaci = [
            'datum' => $this->f['datum'], 'opis' => trim($this->f['opis']),
            'radni_sati' => blank($this->f['radni_sati']) ? null : (int) $this->f['radni_sati'],
            'trosak' => blank($this->f['trosak']) ? null : $this->f['trosak'],
            'serviser' => blank($this->f['serviser']) ? null : trim($this->f['serviser']),
        ];

        if ($this->urediId) {
            StrojServis::where('user_id', auth()->id())->whereKey($this->urediId)->update($podaci);
        } else {
            StrojServis::create($podaci + ['user_id' => auth()->id(), 'stroj_id' => $strojId]);
            // servis obavljen: rok sljedećeg servisa pomakne se godinu dana od datuma servisa
            if (! empty($this->f['pomakni_rok'])) {
                $rok = StrojRok::where('user_id', auth()->id())->where('stroj_id', $strojId)->where('vrsta', 'servis')->whereNull('obnovljen_at')->first();
                if ($rok) {
                    $rok->update(['obnovljen_at' => now()]);
                    StrojRok::create(['user_id' => auth()->id(), 'stroj_id' => $strojId, 'vrsta' => 'servis',
                        'datum_isteka' => Carbon::parse($podaci['datum'])->addMonthsNoOverflow(Mehanizacija::ROKOVI['servis'][1]),
                        'podsjetnik_dana' => $rok->podsjetnik_dana]);
                }
            }
        }
        $this->otvoren = $strojId;
        $this->odustani();
    }

    public function obrisiServis(int $id): void
    {
        StrojServis::where('user_id', auth()->id())->whereKey($id)->delete();
    }

    public function otvori(int $id): void
    {
        $this->otvoren = $this->otvoren === $id ? null : $id;
    }

    public function getStrojeviProperty()
    {
        return Stroj::where('user_id', auth()->id())
            ->with(['rokovi' => fn ($q) => $q->orderBy('datum_isteka'), 'servisi' => fn ($q) => $q->orderByDesc('datum')->orderByDesc('id')])
            ->orderBy('naziv')->get();
    }

    public function render()
    {
        return view('livewire.mehanizacija-manager');
    }
}
