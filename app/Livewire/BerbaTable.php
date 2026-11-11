<?php

namespace App\Livewire;

use App\Models\Berba;
use App\Models\Kultura;
use App\Support\Berbe;
use App\Support\ProvjeraSzb;
use Illuminate\Validation\Rule;
use Livewire\Component;

/** Berba i žetva: više zapisa po kulturi, provjera karence pri unosu i prihod za Troškove. */
class BerbaTable extends Component
{
    use Concerns\IzKalendara;

    public bool $showForm = false;
    public ?int $editId = null;
    public array $form = [];
    public int $godina;
    public string $kulturaFilter = '';

    protected array $validationAttributes = [
        'form.kultura_id' => 'kultura', 'form.datum' => 'datum', 'form.kolicina' => 'količina', 'form.jedinica' => 'jedinica',
        'form.cijena' => 'cijena', 'form.povrsina_ha' => 'površina', 'form.sorta' => 'sorta', 'form.klasa' => 'klasa',
        'form.kupac' => 'kupac', 'form.napomena' => 'napomena',
    ];

    public function mount(): void
    {
        $this->godina = (int) date('Y');
        $this->form = $this->praznaForma();
        $plan = $this->izKalendara('berba', 'datum', $otvori);
        if ($plan?->opis) {
            $this->form['napomena'] = mb_substr($plan->opis, 0, 500);
        }
        $this->showForm = $otvori;
    }

    private function praznaForma(): array
    {
        return ['kultura_id' => '', 'datum' => date('Y-m-d'), 'kolicina' => '', 'jedinica' => 'kg', 'cijena' => '',
            'povrsina_ha' => '', 'sorta' => '', 'klasa' => '', 'kupac' => '', 'napomena' => ''];
    }

    public function dodaj(): void
    {
        $this->editId = null;
        $this->form = $this->praznaForma();
        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function uredi(int $id): void
    {
        $b = Berba::where('user_id', auth()->id())->findOrFail($id);
        $this->editId = $b->id;
        $this->form = [
            'kultura_id' => $b->kultura_id, 'datum' => $b->datum->format('Y-m-d'), 'kolicina' => $b->kolicina, 'jedinica' => $b->jedinica,
            'cijena' => $b->cijena ?? '', 'povrsina_ha' => $b->povrsina_ha ?? '', 'sorta' => $b->sorta ?? '', 'klasa' => $b->klasa ?? '',
            'kupac' => $b->kupac ?? '', 'napomena' => $b->napomena ?? '',
        ];
        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function odustani(): void
    {
        $this->planId = null;
        $this->planOpis = '';
        $this->showForm = false;
        $this->editId = null;
        $this->resetErrorBag();
    }

    /** Odabrana kultura predloži svoju sortu. */
    public function updatedFormKulturaId($id): void
    {
        $k = Kultura::where('user_id', auth()->id())->find((int) $id);
        if ($k && blank($this->form['sorta'])) {
            $this->form['sorta'] = $k->sorta ?? '';
        }
    }

    public function spremi(): void
    {
        $this->validate([
            'form.kultura_id' => ['required', Rule::exists('kulture', 'id')->where('user_id', auth()->id())],
            'form.datum' => 'required|date',
            'form.kolicina' => 'required|numeric|gt:0|max:99999999',
            'form.jedinica' => 'required|in:kg,t',
            'form.cijena' => 'nullable|numeric|min:0|max:1000000',
            'form.povrsina_ha' => 'nullable|numeric|gt:0|max:100000',
            'form.sorta' => 'nullable|string|max:100',
            'form.klasa' => 'nullable|string|max:50',
            'form.kupac' => 'nullable|string|max:150',
            'form.napomena' => 'nullable|string|max:500',
        ]);

        $faktor = $this->form['jedinica'] === 't' ? 1000 : 1;
        $tekst = fn ($k) => blank($this->form[$k]) ? null : trim($this->form[$k]);
        $podaci = [
            'kultura_id' => (int) $this->form['kultura_id'], 'datum' => $this->form['datum'],
            'kolicina_kg' => round((float) $this->form['kolicina'] * $faktor, 2), 'jedinica' => $this->form['jedinica'],
            'cijena_kg' => blank($this->form['cijena']) ? null : round((float) $this->form['cijena'] / $faktor, 4),
            'povrsina_ha' => blank($this->form['povrsina_ha']) ? null : $this->form['povrsina_ha'],
            'sorta' => $tekst('sorta'), 'klasa' => $tekst('klasa'), 'kupac' => $tekst('kupac'), 'napomena' => $tekst('napomena'),
        ];

        if ($this->editId) {
            Berba::where('user_id', auth()->id())->findOrFail($this->editId)->update($podaci);
        } else {
            $zapis = Berba::create($podaci + ['user_id' => auth()->id()]);
            $this->zatvoriPlan('berba', $zapis->id);
        }
        $this->godina = (int) substr($this->form['datum'], 0, 4);
        $this->odustani();
    }

    public function obrisi(int $id): void
    {
        Berba::where('user_id', auth()->id())->findOrFail($id)->delete();
    }

    /** Provjera karence za datum u formi (s dohvatom iz FIS-a ako podaci sredstva još nisu spremljeni). */
    public function getProvjeraKarenceProperty(): ?array
    {
        return blank($this->form['kultura_id'] ?? null) || blank($this->form['datum'] ?? null)
            ? null
            : ProvjeraSzb::karencaZaBerbu(auth()->user(), $this->form['kultura_id'], $this->form['datum'], true);
    }

    public function render()
    {
        $uid = auth()->id();
        $kulture = Kultura::join('parcele', 'parcele.id', '=', 'kulture.parcela_id')
            ->where('kulture.user_id', $uid)
            ->where(fn ($q) => $q->whereNull('kulture.zavrsena_at')->orWhere('kulture.zavrsena_at', '>=', now()->subYear()->toDateString())
                ->orWhere('kulture.id', (int) ($this->form['kultura_id'] ?? 0)))
            ->orderByRaw('kulture.zavrsena_at IS NOT NULL')->orderBy('parcele.arkod_broj')->orderBy('kulture.naziv')
            ->select('kulture.*', 'parcele.arkod_broj')->get();

        $godine = Berba::where('user_id', $uid)->pluck('datum')->map(fn ($d) => (int) $d->format('Y'))
            ->push((int) date('Y'), $this->godina)->unique()->sortDesc()->values();

        $berbe = Berba::with('kultura.parcela')->where('user_id', $uid)
            ->where('datum', '>=', $this->godina . '-01-01')->where('datum', '<', ($this->godina + 1) . '-01-01')
            ->when($this->kulturaFilter !== '', fn ($q) => $q->where('kultura_id', (int) $this->kulturaFilter))
            ->orderByDesc('datum')->orderByDesc('id')->get();

        // upozorenje u popisu: bez poziva FIS-u, samo iz spremljenih podataka sredstva
        $karence = [];
        foreach ($berbe as $b) {
            $kljuc = $b->kultura_id . '|' . $b->datum->format('Y-m-d');
            $karence[$kljuc] ??= ProvjeraSzb::karencaZaBerbu(auth()->user(), $b->kultura_id, $b->datum, false)['krsi'] ?? false;
            $b->karenca = $karence[$kljuc] ?: null;
        }

        $poKulturi = $berbe->groupBy('kultura_id')->map(function ($g) {
            $k = $g->first()->kultura;
            $kg = (float) $g->sum('kolicina_kg');
            $ha = (float) $k->posadjena_povrsina_ha;

            return ['naziv' => $k->naziv, 'arkod' => $k->parcela?->arkod_broj, 'kg' => $kg, 'broj' => $g->count(),
                't_ha' => $ha > 0 ? round($kg / 1000 / $ha, 2) : null, 'iznos' => round((float) $g->sum(fn ($b) => $b->iznos ?? 0), 2)];
        })->sortByDesc('kg')->values();

        return view('livewire.berba-table', [
            'kulture' => $kulture, 'godine' => $godine, 'berbe' => $berbe, 'poKulturi' => $poKulturi,
            'ukupnoKg' => (float) $berbe->sum('kolicina_kg'), 'ukupnoIznos' => round((float) $berbe->sum(fn ($b) => $b->iznos ?? 0), 2),
            'bezCijene' => $berbe->whereNull('cijena_kg')->count(),
            'masa' => fn ($kg) => Berbe::masa((float) $kg),
        ]);
    }
}
