<?php

namespace App\Livewire;

use App\Models\Kultura;
use App\Models\Rad;
use App\Models\Stroj;
use App\Support\Radovi;
use Illuminate\Validation\Rule;
use Livewire\Component;

/** Ostali radovi na parceli (oranje, rezidba, košnja...) s troškom: vlastiti rad (gorivo + radni sati) ili usluga. */
class RadoviTable extends Component
{
    public bool $showForm = false;
    public ?int $editId = null;
    public array $form = [];
    public int $godina;
    public string $kulturaFilter = '';

    protected array $validationAttributes = [
        'form.kultura_id' => 'kultura', 'form.datum' => 'datum', 'form.vrsta' => 'vrsta rada', 'form.izvedba' => 'izvedba',
        'form.stroj_id' => 'stroj', 'form.povrsina_ha' => 'površina', 'form.sati' => 'sati rada', 'form.gorivo_l' => 'gorivo',
        'form.cijena_goriva' => 'cijena goriva', 'form.radni_sati' => 'radni sati', 'form.cijena_sata' => 'cijena sata',
        'form.usluga_cijena' => 'cijena usluge', 'form.usluga_po' => 'način obračuna', 'form.izvodac' => 'izvođač', 'form.napomena' => 'napomena',
    ];

    public function mount(): void
    {
        $this->godina = (int) date('Y');
        $this->form = $this->praznaForma();
    }

    private function praznaForma(): array
    {
        // cijena goriva i sata predlažu se iz zadnjeg zapisa
        $zadnji = Rad::where('user_id', auth()->id())->where('izvedba', 'vlastito')->latest('id')->first();

        return ['kultura_id' => '', 'datum' => date('Y-m-d'), 'vrsta' => '', 'izvedba' => 'vlastito', 'stroj_id' => '', 'povrsina_ha' => '',
            'sati' => '', 'gorivo_l' => '', 'cijena_goriva' => $zadnji?->cijena_goriva ?? '', 'radni_sati' => '', 'cijena_sata' => $zadnji?->cijena_sata ?? '',
            'usluga_cijena' => '', 'usluga_po' => 'ha', 'izvodac' => '', 'napomena' => ''];
    }

    public function dodaj(): void
    {
        $this->editId = null;
        $this->form = $this->praznaForma();
        if ($this->kulturaFilter !== '') {
            $this->form['kultura_id'] = $this->kulturaFilter;
        }
        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function uredi(int $id): void
    {
        $r = Rad::where('user_id', auth()->id())->findOrFail($id);
        $this->editId = $r->id;
        $this->form = ['kultura_id' => $r->kultura_id, 'datum' => $r->datum->format('Y-m-d')];
        foreach (['vrsta', 'izvedba', 'stroj_id', 'povrsina_ha', 'sati', 'gorivo_l', 'cijena_goriva', 'radni_sati', 'cijena_sata', 'usluga_cijena', 'usluga_po', 'izvodac', 'napomena'] as $k) {
            $this->form[$k] = $r->{$k} ?? '';
        }
        $this->form['usluga_po'] = $r->usluga_po ?: 'ha';
        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function kopiraj(int $id): void
    {
        $this->uredi($id);
        $this->editId = null;
        $this->form['datum'] = date('Y-m-d');
    }

    public function odustani(): void
    {
        $this->showForm = false;
        $this->editId = null;
        $this->resetErrorBag();
    }

    private function kultura(): ?Kultura
    {
        return blank($this->form['kultura_id'] ?? null) ? null : Kultura::where('user_id', auth()->id())->find((int) $this->form['kultura_id']);
    }

    /** Trošak koji će se spremiti, za prikaz u formi. */
    public function getIzracunProperty(): ?float
    {
        return Rad::izracunajTrosak($this->form, $this->kultura()?->posadjena_povrsina_ha);
    }

    public function spremi(): void
    {
        $uid = auth()->id();
        $usluga = ($this->form['izvedba'] ?? '') === 'usluga';
        $this->validate([
            'form.kultura_id' => ['required', Rule::exists('kulture', 'id')->where('user_id', $uid)],
            'form.datum' => 'required|date',
            'form.vrsta' => 'required|string|max:100',
            'form.izvedba' => 'required|in:vlastito,usluga',
            'form.stroj_id' => ['nullable', Rule::exists('strojevi', 'id')->where('user_id', $uid)],
            'form.povrsina_ha' => 'nullable|numeric|gt:0|max:100000',
            'form.sati' => 'nullable|numeric|min:0|max:10000',
            'form.gorivo_l' => 'nullable|numeric|min:0|max:100000',
            'form.cijena_goriva' => 'nullable|numeric|min:0|max:100',
            'form.radni_sati' => 'nullable|numeric|min:0|max:100000',
            'form.cijena_sata' => 'nullable|numeric|min:0|max:1000',
            'form.usluga_cijena' => ($usluga ? 'required' : 'nullable') . '|numeric|min:0|max:10000000',
            'form.usluga_po' => 'nullable|in:ukupno,ha',
            'form.izvodac' => 'nullable|string|max:150',
            'form.napomena' => 'nullable|string|max:500',
        ]);

        $broj = fn ($k) => blank($this->form[$k] ?? null) ? null : $this->form[$k];
        $tekst = fn ($k) => blank($this->form[$k] ?? null) ? null : trim($this->form[$k]);
        $podaci = [
            'kultura_id' => (int) $this->form['kultura_id'], 'datum' => $this->form['datum'], 'vrsta' => mb_substr(trim($this->form['vrsta']), 0, 100),
            'izvedba' => $this->form['izvedba'], 'stroj_id' => $broj('stroj_id'), 'povrsina_ha' => $broj('povrsina_ha'), 'sati' => $broj('sati'),
            'izvodac' => $usluga ? $tekst('izvodac') : null, 'napomena' => $tekst('napomena'),
            // polja druge izvedbe se ne čuvaju, da trošak uvijek odgovara onome što piše
            'gorivo_l' => $usluga ? null : $broj('gorivo_l'), 'cijena_goriva' => $usluga ? null : $broj('cijena_goriva'),
            'radni_sati' => $usluga ? null : $broj('radni_sati'), 'cijena_sata' => $usluga ? null : $broj('cijena_sata'),
            'usluga_cijena' => $usluga ? $broj('usluga_cijena') : null, 'usluga_po' => $usluga ? ($this->form['usluga_po'] ?: 'ukupno') : null,
        ];
        $podaci['trosak'] = Rad::izracunajTrosak($podaci, $this->kultura()?->posadjena_povrsina_ha);

        if ($this->editId) {
            Rad::where('user_id', $uid)->findOrFail($this->editId)->update($podaci);
        } else {
            Rad::create($podaci + ['user_id' => $uid]);
        }
        $this->godina = (int) substr($this->form['datum'], 0, 4);
        $this->odustani();
    }

    public function obrisi(int $id): void
    {
        Rad::where('user_id', auth()->id())->findOrFail($id)->delete();
    }

    public function render()
    {
        $uid = auth()->id();
        $kulture = Kultura::join('parcele', 'parcele.id', '=', 'kulture.parcela_id')
            ->where('kulture.user_id', $uid)
            ->where(fn ($q) => $q->whereNull('kulture.zavrsena_at')->orWhere('kulture.zavrsena_at', '>=', now()->subYear()->toDateString())
                ->orWhere('kulture.id', (int) ($this->form['kultura_id'] ?? 0)))
            ->orderByRaw('kulture.zavrsena_at IS NOT NULL')->orderBy('parcele.arkod_broj')->orderBy('kulture.naziv')
            ->select('kulture.*', 'parcele.arkod_broj', 'parcele.rucno_ucrtana')->get();

        $godine = Rad::where('user_id', $uid)->pluck('datum')->map(fn ($d) => (int) $d->format('Y'))
            ->push((int) date('Y'), $this->godina)->unique()->sortDesc()->values();

        $radovi = Rad::with(['kultura.parcela', 'stroj'])->where('user_id', $uid)
            ->where('datum', '>=', $this->godina . '-01-01')->where('datum', '<', ($this->godina + 1) . '-01-01')
            ->when($this->kulturaFilter !== '', fn ($q) => $q->where('kultura_id', (int) $this->kulturaFilter))
            ->orderByDesc('datum')->orderByDesc('id')->get();

        $kultura = $this->showForm ? $this->kultura() : null;

        return view('livewire.radovi-table', [
            'kulture' => $kulture, 'godine' => $godine, 'radovi' => $radovi,
            'strojevi' => Stroj::where('user_id', $uid)->orderBy('naziv')->get(['id', 'naziv']),
            'vrste' => $this->showForm ? Radovi::vrsteZa($uid, $kultura) : [],
            'skupina' => Radovi::SKUPINE[Radovi::skupina($kultura)],
            'haKulture' => $kultura?->posadjena_povrsina_ha,
            'ukupno' => round((float) $radovi->sum('trosak'), 2),
            'poVrsti' => $radovi->groupBy(fn ($r) => mb_strtolower($r->vrsta))->map(fn ($g) => [
                'vrsta' => $g->first()->vrsta, 'broj' => $g->count(), 'trosak' => round((float) $g->sum('trosak'), 2),
            ])->sortByDesc('trosak')->values(),
            'bezTroska' => $radovi->whereNull('trosak')->count(),
        ]);
    }
}
