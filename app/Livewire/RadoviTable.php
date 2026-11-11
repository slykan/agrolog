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
    use Concerns\IzKalendara;

    public bool $showForm = false;
    public ?int $editId = null;
    public array $form = [];
    public int $godina;
    public string $kulturaFilter = '';
    /** Jedan rad na više kultura: sprema se po zapis za svaku, trošak raspodijeljen po hektarima. */
    public bool $viseKultura = false;
    public array $odabrane = [];

    protected array $validationAttributes = [
        'odabrane' => 'kulture', 'odabrane.*' => 'kultura',
        'form.kultura_id' => 'kultura', 'form.datum' => 'datum', 'form.vrsta' => 'vrsta rada', 'form.izvedba' => 'izvedba',
        'form.stroj_id' => 'stroj', 'form.povrsina_ha' => 'površina', 'form.sati' => 'sati rada', 'form.gorivo_l' => 'gorivo',
        'form.cijena_goriva' => 'cijena goriva', 'form.radni_sati' => 'radni sati', 'form.cijena_sata' => 'cijena sata',
        'form.usluga_cijena' => 'cijena usluge', 'form.usluga_po' => 'način obračuna', 'form.izvodac' => 'izvođač', 'form.napomena' => 'napomena',
    ];

    public function mount(): void
    {
        $this->godina = (int) date('Y');
        $this->form = $this->praznaForma();
        $plan = $this->izKalendara('rad', 'datum', $otvori);
        if ($plan?->opis) {
            $this->form['vrsta'] = mb_substr($plan->opis, 0, 100);
        }
        $this->showForm = $otvori;
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
        $this->viseKultura = false;
        $this->odabrane = [];
        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function naViseKultura(): void
    {
        if ($this->editId) {
            return;
        }
        $this->viseKultura = true;
        $this->odabrane = blank($this->form['kultura_id'] ?? null) ? [] : [(string) $this->form['kultura_id']];
        $this->resetErrorBag();
    }

    public function jednaKultura(): void
    {
        $this->viseKultura = false;
        $this->form['kultura_id'] = $this->odabrane[0] ?? '';
        $this->resetErrorBag();
    }

    public function oznaciSve(): void
    {
        $this->odabrane = $this->ponudjeneKulture()->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    /** Kulture koje se nude u formi: aktivne i završene u zadnjih godinu dana. */
    private function ponudjeneKulture()
    {
        return Kultura::join('parcele', 'parcele.id', '=', 'kulture.parcela_id')
            ->where('kulture.user_id', auth()->id())
            ->where(fn ($q) => $q->whereNull('kulture.zavrsena_at')->orWhere('kulture.zavrsena_at', '>=', now()->subYear()->toDateString())
                ->orWhere('kulture.id', (int) ($this->form['kultura_id'] ?? 0)))
            ->orderByRaw('kulture.zavrsena_at IS NOT NULL')->orderBy('parcele.arkod_broj')->orderBy('kulture.naziv')
            ->select('kulture.*', 'parcele.arkod_broj', 'parcele.rucno_ucrtana')->get();
    }

    /**
     * Raspodjela jednog rada na odabrane kulture po hektarima: usluga po ha ostaje ista cijena,
     * a ukupni iznos, gorivo, radni sati i sati stroja dijele se prema udjelu površine.
     *
     * @return array<int, array> podaci za svaki zapis (bez user_id)
     */
    private function raspodjela(array $podaci): array
    {
        $kulture = Kultura::where('user_id', auth()->id())->whereIn('id', array_map('intval', $this->odabrane))->get();
        $ukupnoHa = (float) $kulture->sum('posadjena_povrsina_ha');
        $zapisi = [];
        foreach ($kulture as $k) {
            $udio = $ukupnoHa > 0 ? (float) $k->posadjena_povrsina_ha / $ukupnoHa : 1 / max(1, $kulture->count());
            $p = $podaci;
            $p['kultura_id'] = $k->id;
            $p['povrsina_ha'] = null;
            foreach (['sati', 'gorivo_l', 'radni_sati'] as $polje) {
                $p[$polje] = $p[$polje] === null ? null : round((float) $p[$polje] * $udio, 2);
            }
            if ($p['izvedba'] === 'usluga' && $p['usluga_po'] === 'ukupno' && $p['usluga_cijena'] !== null) {
                $p['usluga_cijena'] = round((float) $p['usluga_cijena'] * $udio, 2);
            }
            if ($kulture->count() > 1) {
                $n = $kulture->count();
                $oznaka = ' (zajednički rad na ' . $n . ' ' . ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 12 || $n % 100 > 14) ? 'kulture' : 'kultura') . ', ' . number_format($ukupnoHa, 2, ',', '.') . ' ha)';
                $p['napomena'] = trim(mb_substr((string) ($p['napomena'] ?? ''), 0, 500 - mb_strlen($oznaka)) . $oznaka);
            }
            $p['trosak'] = Rad::izracunajTrosak($p, (float) $k->posadjena_povrsina_ha);
            $p['_udio'] = $udio;
            $zapisi[] = $p;
        }

        // ukupni iznos (osim usluge po ha) dijeli se tako da zbroj bude točan do centa: ostatak ide zadnjoj kulturi
        $ukupno = Rad::izracunajTrosak(['povrsina_ha' => null] + $podaci, $ukupnoHa);
        $poHa = $podaci['izvedba'] === 'usluga' && $podaci['usluga_po'] === 'ha';
        if ($ukupno !== null && ! $poHa && $zapisi) {
            $dodijeljeno = 0.0;
            foreach ($zapisi as $i => &$z) {
                $z['trosak'] = $i === array_key_last($zapisi) ? round($ukupno - $dodijeljeno, 2) : round($ukupno * $z['_udio'], 2);
                $dodijeljeno += $z['trosak'];
            }
            unset($z);
        }

        return array_map(function ($z) { unset($z['_udio']); return $z; }, $zapisi);
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
        $this->viseKultura = false;
        $this->odabrane = [];
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
        $this->planId = null;
        $this->planOpis = '';
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
        if ($this->viseKultura) {
            if (! $this->odabrane) {
                return null;
            }
            $iznosi = array_column($this->raspodjela($this->podaci()), 'trosak');

            return in_array(null, $iznosi, true) ? null : round(array_sum($iznosi), 2);
        }

        return Rad::izracunajTrosak($this->form, $this->kultura()?->posadjena_povrsina_ha);
    }

    /** Upisana vrsta izgleda kao vožnja autom, koja pripada općim troškovima. */
    public function getJeVoznjaProperty(): bool
    {
        $v = mb_strtolower(trim($this->form['vrsta'] ?? ''));

        return $v !== '' && (bool) preg_match('/(^|\s)(put|puta|putovanje|vožnja|voznja|auto|autom|automobil|kilometri|km)(\s|$)/u', $v);
    }

    /** Podaci iz forme za spremanje (bez troška); polja druge izvedbe se ne čuvaju, da trošak uvijek odgovara onome što piše. */
    private function podaci(): array
    {
        $usluga = ($this->form['izvedba'] ?? '') === 'usluga';
        $broj = fn ($k) => blank($this->form[$k] ?? null) || ! is_numeric($this->form[$k]) ? null : $this->form[$k];
        $tekst = fn ($k) => blank($this->form[$k] ?? null) ? null : trim($this->form[$k]);

        return [
            'kultura_id' => blank($this->form['kultura_id'] ?? null) ? null : (int) $this->form['kultura_id'], 'datum' => $this->form['datum'],
            'vrsta' => mb_substr(trim((string) $this->form['vrsta']), 0, 100),
            'izvedba' => $this->form['izvedba'], 'stroj_id' => $broj('stroj_id'), 'povrsina_ha' => $broj('povrsina_ha'), 'sati' => $broj('sati'),
            'izvodac' => $usluga ? $tekst('izvodac') : null, 'napomena' => $tekst('napomena'),
            'gorivo_l' => $usluga ? null : $broj('gorivo_l'), 'cijena_goriva' => $usluga ? null : $broj('cijena_goriva'),
            'radni_sati' => $usluga ? null : $broj('radni_sati'), 'cijena_sata' => $usluga ? null : $broj('cijena_sata'),
            'usluga_cijena' => $usluga ? $broj('usluga_cijena') : null, 'usluga_po' => $usluga ? ($this->form['usluga_po'] ?: 'ukupno') : null,
        ];
    }

    public function spremi(): void
    {
        $uid = auth()->id();
        $usluga = ($this->form['izvedba'] ?? '') === 'usluga';
        $vise = $this->viseKultura && ! $this->editId;
        $this->validate([
            'form.kultura_id' => $vise ? ['nullable'] : ['required', Rule::exists('kulture', 'id')->where('user_id', $uid)],
            'odabrane' => $vise ? 'required|array|min:1|max:500' : 'nullable|array',
            'odabrane.*' => [Rule::exists('kulture', 'id')->where('user_id', $uid)],
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

        $podaci = $this->podaci();

        if ($vise) {
            $prvi = \Illuminate\Support\Facades\DB::transaction(function () use ($podaci, $uid) {
                $prvi = null;
                foreach ($this->raspodjela($podaci) as $p) {
                    $zapis = Rad::create($p + ['user_id' => $uid]);
                    $prvi ??= $zapis->id;
                }

                return $prvi;
            });
            if ($prvi) {
                $this->zatvoriPlan('rad', $prvi);
            }
        } else {
            $podaci['trosak'] = Rad::izracunajTrosak($podaci, $this->kultura()?->posadjena_povrsina_ha);
            if ($this->editId) {
                Rad::where('user_id', $uid)->findOrFail($this->editId)->update($podaci);
            } else {
                $zapis = Rad::create($podaci + ['user_id' => $uid]);
                $this->zatvoriPlan('rad', $zapis->id);
            }
        }
        $this->viseKultura = false;
        $this->odabrane = [];
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
        $kulture = $this->ponudjeneKulture();

        $godine = Rad::where('user_id', $uid)->pluck('datum')->map(fn ($d) => (int) $d->format('Y'))
            ->push((int) date('Y'), $this->godina)->unique()->sortDesc()->values();

        $radovi = Rad::with(['kultura.parcela', 'stroj'])->where('user_id', $uid)
            ->where('datum', '>=', $this->godina . '-01-01')->where('datum', '<', ($this->godina + 1) . '-01-01')
            ->when($this->kulturaFilter !== '', fn ($q) => $q->where('kultura_id', (int) $this->kulturaFilter))
            ->orderByDesc('datum')->orderByDesc('id')->get();

        // ponuda vrsta rada: prema odabranoj kulturi, kod više kultura prema prvoj odabranoj
        $kultura = ! $this->showForm ? null
            : ($this->viseKultura ? ($this->odabrane ? $kulture->firstWhere('id', (int) $this->odabrane[0]) : null) : $this->kultura());
        $odabraniHa = $this->viseKultura ? (float) $kulture->whereIn('id', array_map('intval', $this->odabrane))->sum('posadjena_povrsina_ha') : 0.0;

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
            'odabraniHa' => $odabraniHa, 'jeVoznja' => $this->showForm && $this->jeVoznja,
        ]);
    }
}
