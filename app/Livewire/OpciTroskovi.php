<?php

namespace App\Livewire;

use App\Models\Kultura;
use App\Models\OpciTrosak;
use App\Models\Pristup;
use App\Models\Ruta;
use App\Support\Radovi;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Opći troškovi gospodarstva (vožnja, zakup, osiguranje...). Bez kulture ulaze samo u ukupni trošak,
 * s kulturom i u €/ha te kulture. Vožnja: km × €/km, s povratkom dvostruko; spremljene rute za brzi upis.
 * Savjetnik (u svom računu) može trošak vezati na klijenta i vidjeti zbroj po klijentu.
 */
class OpciTroskovi extends Component
{
    public bool $showForm = false;
    public ?int $editId = null;
    public array $form = [];
    public int $godina;
    public bool $zapamtiRutu = false;

    protected array $validationAttributes = [
        'form.datum' => 'datum', 'form.vrsta' => 'vrsta', 'form.opis' => 'opis', 'form.km' => 'kilometri', 'form.cijena_km' => 'cijena po km',
        'form.iznos' => 'iznos', 'form.kultura_id' => 'kultura', 'form.klijent_id' => 'klijent',
    ];

    public function mount(): void
    {
        $this->godina = (int) date('Y');
        $this->form = $this->praznaForma();
    }

    private function praznaForma(string $vrsta = ''): array
    {
        $zadnja = OpciTrosak::where('user_id', auth()->id())->whereNotNull('cijena_km')->latest('id')->value('cijena_km');

        return ['datum' => date('Y-m-d'), 'vrsta' => $vrsta, 'opis' => '', 'km' => '', 'povratno' => true,
            'cijena_km' => $zadnja ?? Radovi::CIJENA_KM, 'iznos' => '', 'kultura_id' => '', 'klijent_id' => ''];
    }

    public function dodaj(string $vrsta = ''): void
    {
        $this->editId = null;
        $this->form = $this->praznaForma(mb_substr($vrsta, 0, 100));
        $this->zapamtiRutu = false;
        $this->showForm = true;
        $this->resetErrorBag();
    }

    /** Brzi upis vožnje iz spremljene rute. */
    public function izRute(int $id): void
    {
        $r = Ruta::where('user_id', auth()->id())->findOrFail($id);
        $this->dodaj('Vožnja');
        $this->form['opis'] = $r->naziv;
        $this->form['km'] = (float) $r->km;
        $this->form['povratno'] = $r->povratno;
    }

    public function obrisiRutu(int $id): void
    {
        Ruta::where('user_id', auth()->id())->findOrFail($id)->delete();
    }

    public function uredi(int $id): void
    {
        $t = OpciTrosak::where('user_id', auth()->id())->findOrFail($id);
        $this->editId = $t->id;
        $this->form = [
            'datum' => $t->datum->format('Y-m-d'), 'vrsta' => $t->vrsta, 'opis' => $t->opis ?? '', 'km' => $t->km ?? '', 'povratno' => $t->povratno,
            'cijena_km' => $t->cijena_km ?? Radovi::CIJENA_KM, 'iznos' => $t->iznos, 'kultura_id' => $t->kultura_id ?? '', 'klijent_id' => $t->klijent_id ?? '',
        ];
        $this->zapamtiRutu = false;
        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function odustani(): void
    {
        $this->showForm = false;
        $this->editId = null;
        $this->resetErrorBag();
    }

    private function jeVoznja(): bool
    {
        return mb_strtolower(trim($this->form['vrsta'] ?? '')) === 'vožnja';
    }

    /** Iznos vožnje iz km; null ako nije vožnja ili nedostaje podatak. */
    public function getIznosVoznjeProperty(): ?float
    {
        if (! $this->jeVoznja() || ! is_numeric($this->form['km'] ?? null) || ! is_numeric($this->form['cijena_km'] ?? null)) {
            return null;
        }

        return round((float) $this->form['km'] * (! empty($this->form['povratno']) ? 2 : 1) * (float) $this->form['cijena_km'], 2);
    }

    /** Klijenti savjetnika (aktivni pristupi); prazno za obično gospodarstvo. */
    private function klijenti()
    {
        return Pristup::aktivni()->with('vlasnik:id,name')->where('savjetnik_id', auth()->id())->get()
            ->map(fn ($p) => $p->vlasnik)->filter()->unique('id')->sortBy('name')->values();
    }

    public function spremi(): void
    {
        $uid = auth()->id();
        $voznja = $this->jeVoznja();
        $klijenti = $this->klijenti()->pluck('id')->all();
        $this->validate([
            'form.datum' => 'required|date',
            'form.vrsta' => 'required|string|max:100',
            'form.opis' => ($voznja && $this->zapamtiRutu ? 'required' : 'nullable') . '|string|max:300',
            'form.km' => ($voznja ? 'required|numeric|gt:0' : 'nullable|numeric|min:0') . '|max:100000',
            'form.cijena_km' => ($voznja ? 'required' : 'nullable') . '|numeric|min:0|max:100',
            'form.iznos' => ($voznja ? 'nullable' : 'required') . '|numeric|min:0|max:10000000',
            'form.kultura_id' => ['nullable', Rule::exists('kulture', 'id')->where('user_id', $uid)],
            'form.klijent_id' => ['nullable', Rule::in($klijenti)],
        ], ['form.opis.required' => 'Upišite naziv rute u opis, npr. "Voćnjak Hrastin".']);

        $podaci = [
            'datum' => $this->form['datum'], 'vrsta' => $voznja ? 'Vožnja' : mb_substr(trim($this->form['vrsta']), 0, 100),
            'opis' => blank($this->form['opis']) ? null : trim($this->form['opis']),
            'km' => $voznja ? $this->form['km'] : null, 'povratno' => $voznja && ! empty($this->form['povratno']),
            'cijena_km' => $voznja ? $this->form['cijena_km'] : null,
            'iznos' => $voznja ? $this->iznosVoznje : round((float) $this->form['iznos'], 2),
            'kultura_id' => blank($this->form['kultura_id']) ? null : (int) $this->form['kultura_id'],
            'klijent_id' => blank($this->form['klijent_id']) ? null : (int) $this->form['klijent_id'],
        ];

        if ($this->editId) {
            OpciTrosak::where('user_id', $uid)->findOrFail($this->editId)->update($podaci);
        } else {
            OpciTrosak::create($podaci + ['user_id' => $uid]);
        }
        if ($voznja && $this->zapamtiRutu && ! Ruta::where('user_id', $uid)->where('naziv', $podaci['opis'])->exists()) {
            Ruta::create(['user_id' => $uid, 'naziv' => mb_substr($podaci['opis'], 0, 100), 'km' => $podaci['km'], 'povratno' => $podaci['povratno']]);
        }
        $this->godina = (int) substr($this->form['datum'], 0, 4);
        $this->odustani();
        $this->dispatch('troskovi-promijenjeni');
    }

    public function obrisi(int $id): void
    {
        OpciTrosak::where('user_id', auth()->id())->findOrFail($id)->delete();
        $this->dispatch('troskovi-promijenjeni');
    }

    public function render()
    {
        $uid = auth()->id();
        $troskovi = OpciTrosak::with(['kultura.parcela', 'klijent:id,name'])->where('user_id', $uid)
            ->where('datum', '>=', $this->godina . '-01-01')->where('datum', '<', ($this->godina + 1) . '-01-01')
            ->orderByDesc('datum')->orderByDesc('id')->get();
        $godine = OpciTrosak::where('user_id', $uid)->pluck('datum')->map(fn ($d) => (int) $d->format('Y'))
            ->push((int) date('Y'), $this->godina)->unique()->sortDesc()->values();
        $klijenti = $this->klijenti();

        $kulture = $this->showForm
            ? Kultura::join('parcele', 'parcele.id', '=', 'kulture.parcela_id')->where('kulture.user_id', $uid)
                ->where(fn ($q) => $q->whereNull('kulture.zavrsena_at')->orWhere('kulture.zavrsena_at', '>=', now()->subYear()->toDateString())
                    ->orWhere('kulture.id', (int) ($this->form['kultura_id'] ?? 0)))
                ->orderBy('parcele.arkod_broj')->orderBy('kulture.naziv')->select('kulture.*', 'parcele.arkod_broj')->get()
            : collect();

        return view('livewire.opci-troskovi', [
            'troskovi' => $troskovi, 'godine' => $godine, 'kulture' => $kulture, 'klijenti' => $klijenti,
            'rute' => Ruta::where('user_id', $uid)->orderBy('naziv')->get(),
            'vrste' => $this->showForm ? Radovi::opceVrste($uid) : [],
            'voznja' => $this->jeVoznja(),
            'ukupno' => round((float) $troskovi->sum('iznos'), 2),
            'km' => (float) $troskovi->sum(fn ($t) => $t->ukupno_km ?? 0),
            'nevezano' => round((float) $troskovi->whereNull('kultura_id')->sum('iznos'), 2),
            'poKlijentu' => $troskovi->whereNotNull('klijent_id')->groupBy('klijent_id')->map(fn ($g) => [
                'ime' => $g->first()->klijent?->name ?? 'Bivši klijent', 'broj' => $g->count(),
                'km' => (float) $g->sum(fn ($t) => $t->ukupno_km ?? 0), 'iznos' => round((float) $g->sum('iznos'), 2),
            ])->sortByDesc('iznos')->values(),
        ]);
    }
}
