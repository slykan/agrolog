<?php

namespace App\Livewire;

use App\Livewire\Concerns\BiraSkladiste;
use Livewire\Component;
use App\Models\Gnojidba;
use App\Models\Kultura;
use App\Support\Gnojiva;

class GnojidbaTable extends Component
{
    use BiraSkladiste;

    public bool $showForm = false;
    public array $form = [
        'kultura_id'    => '',
        'datum'         => '',
        'tip_gnojiva'   => '',
        'kolicina_kg_ha' => '',
    ];
    public array $editForm = [];
    public ?int $editingId = null;
    public array $visibleColumns = ['datum', 'arkod', 'kultura', 'povrsina', 'gnojivo', 'kolicina', 'hraniva'];
    public string $search = '';
    public array $filters = ['arkod' => '', 'kultura' => '', 'datum' => '', 'gnojivo' => ''];
    public string $formKulturaSearch = '';
    public bool $showKulturaPicker = false;
    protected array $validationAttributes = [
        'form.kultura_id' => 'ARKOD parcela',
        'form.datum' => 'datum',
        'form.tip_gnojiva' => 'tip gnojiva',
        'form.kolicina_kg_ha' => 'kolicina kg/ha',
        'editForm.kultura_id' => 'ARKOD parcela',
        'editForm.datum' => 'datum',
        'editForm.tip_gnojiva' => 'tip gnojiva',
        'editForm.kolicina_kg_ha' => 'kolicina kg/ha',
    ];

    /** Preporuka iz koje se unosi zapis; zatvara se tek spremanjem. */
    public ?int $preporukaId = null;
    public string $preporukaOpis = '';

    public function mount(): void
    {
        $this->form = $this->praznaForma();
        if ($p = \App\Support\Preporuke::zaUnos(request()->integer('preporuka'), auth()->id(), 'gnojidba')) {
            $this->preporukaId = $p->id;
            $this->preporukaOpis = $p->savjetnik_ime . ': ' . $p->sazetak();
            $this->showForm = true;
            $this->form['kultura_id'] = $p->kultura_id;
            $this->form['tip_gnojiva'] = $p->sredstvo;
            $this->updatedFormTipGnojiva($p->sredstvo);
            $this->form['kolicina_kg_ha'] = $p->doza ?? '';
        }
    }

    private function praznaForma(): array
    {
        return [
            'kultura_id' => '', 'datum' => date('Y-m-d'), 'tip_gnojiva' => '', 'kolicina_kg_ha' => '',
            'n_posto' => '', 'p_posto' => '', 'k_posto' => '', 'organsko' => false,
            'skladiste_id' => \App\Support\Skladiste::zadanoId(auth()->user()) ?? '',
        ];
    }

    /** Pravila za sastav gnojiva (%), zajednička za novi i uređeni zapis. */
    private function hranivaPravila(string $prefix): array
    {
        return [
            "$prefix.n_posto"  => 'nullable|numeric|min:0|max:100',
            "$prefix.p_posto"  => 'nullable|numeric|min:0|max:100',
            "$prefix.k_posto"  => 'nullable|numeric|min:0|max:100',
            "$prefix.organsko" => 'boolean',
        ];
    }

    /** Odabrano gnojivo s popisa (ili prepoznata formulacija) upiše svoj sastav. */
    private function popuniSastav(string $forma, ?string $tip): void
    {
        if ($g = Gnojiva::prepoznaj($tip)) {
            $this->{$forma}['n_posto'] = $g['n'];
            $this->{$forma}['p_posto'] = $g['p'];
            $this->{$forma}['k_posto'] = $g['k'];
            $this->{$forma}['organsko'] = $g['organsko'];
        }
    }

    public function updatedFormTipGnojiva($tip): void
    {
        $this->popuniSastav('form', $tip);
    }

    public function updatedEditFormTipGnojiva($tip): void
    {
        $this->popuniSastav('editForm', $tip);
    }

    /**
     * Dušik u godini (prema filtru datuma ili tekućoj): po kulturi kg N/ha ukupno i iz stajskog gnoja,
     * te prosjek gospodarstva za stajski gnoj (granica 170 kg N/ha, Akcijski program NN 95/25).
     */
    public function getBilancaDusikaProperty(): array
    {
        $godina = $this->filters['datum'] ? (int) substr($this->filters['datum'], 0, 4) : (int) date('Y');

        $redovi = Gnojidba::where('gnojidbe.user_id', auth()->id())
            ->whereYear('gnojidbe.datum', $godina)
            ->join('kulture', 'kulture.id', '=', 'gnojidbe.kultura_id')
            ->join('parcele', 'parcele.id', '=', 'kulture.parcela_id')
            ->selectRaw('kulture.id, kulture.naziv, kulture.posadjena_povrsina_ha, parcele.arkod_broj,
                SUM(gnojidbe.kg_n_ha) as n_ukupno,
                SUM(CASE WHEN gnojidbe.organsko THEN gnojidbe.kg_n_ha ELSE 0 END) as n_stajski')
            ->groupBy('kulture.id', 'kulture.naziv', 'kulture.posadjena_povrsina_ha', 'parcele.arkod_broj')
            ->orderBy('parcele.arkod_broj')
            ->get();

        $ha = (float) Kultura::where('user_id', auth()->id())->uGodini($godina)->sum('posadjena_povrsina_ha');
        $kgStajski = $redovi->sum(fn ($r) => (float) $r->n_stajski * (float) $r->posadjena_povrsina_ha);

        return [
            'godina'   => $godina,
            'redovi'   => $redovi,
            'prosjek'  => $ha > 0 ? round($kgStajski / $ha, 1) : null,
            'granica'  => Gnojiva::GRANICA_STAJSKI_N,
        ];
    }

    public function getKultureProperty()
    {
        return Kultura::join('parcele', 'parcele.id', '=', 'kulture.parcela_id')
            ->where('kulture.user_id', auth()->id())
            ->orderBy('parcele.arkod_broj')
            ->orderBy('kulture.naziv')
            ->select('kulture.*', 'parcele.arkod_broj', 'parcele.povrsina_ha')
            ->get();
    }

    public function getFilteredKultureProperty()
    {
        // Za nove zapise nude se samo aktivne kulture (završene ostaju samo u staroj evidenciji)
        $aktivne = $this->kulture->whereNull('zavrsena_at');
        if (!$this->formKulturaSearch) {
            return $aktivne;
        }
        $search = mb_strtolower($this->formKulturaSearch);
        return $aktivne->filter(fn($k) =>
            str_contains(mb_strtolower($k->arkod_broj), $search) ||
            str_contains(mb_strtolower($k->naziv), $search)
        );
    }

    public function getKulturaOptionsProperty(): array
    {
        return $this->kulture->map(fn($k) => [
            'id'       => $k->id,
            'arkod'    => $k->arkod_broj,
            'naziv'    => $k->naziv,
            'povrsina' => $k->posadjena_povrsina_ha,
        ])->values()->all();
    }

    public function getSelectedKulturaProperty()
    {
        if (!$this->form['kultura_id']) {
            return null;
        }

        return $this->kulture->firstWhere('id', (int) $this->form['kultura_id']);
    }

    public function getGnojidbeProperty()
    {
        $query = Gnojidba::where('gnojidbe.user_id', auth()->id())
            ->join('kulture', 'kulture.id', '=', 'gnojidbe.kultura_id')
            ->join('parcele', 'parcele.id', '=', 'kulture.parcela_id')
            ->select('gnojidbe.*', 'kulture.naziv as kultura_naziv', 'kulture.posadjena_povrsina_ha', 'parcele.arkod_broj', 'parcele.povrsina_ha')
            ->orderByDesc('gnojidbe.datum')
            ->orderByDesc('gnojidbe.id');

        if ($this->filters['arkod']) {
            $query->where('parcele.arkod_broj', 'like', '%' . $this->filters['arkod'] . '%');
        }
        if ($this->filters['kultura']) {
            $query->where('kulture.naziv', 'like', '%' . $this->filters['kultura'] . '%');
        }
        if ($this->filters['datum']) {
            $query->where('gnojidbe.datum', $this->filters['datum']);
        }
        if ($this->filters['gnojivo']) {
            $query->where('gnojidbe.tip_gnojiva', 'like', '%' . $this->filters['gnojivo'] . '%');
        }
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('parcele.arkod_broj', 'like', '%' . $this->search . '%')
                  ->orWhere('kulture.naziv', 'like', '%' . $this->search . '%')
                  ->orWhere('gnojidbe.tip_gnojiva', 'like', '%' . $this->search . '%');
            });
        }

        return $query->get();
    }

    public function getTipoviGnojivaProperty()
    {
        return Gnojidba::where('gnojidbe.user_id', auth()->id())
            ->distinct()
            ->orderBy('tip_gnojiva')
            ->pluck('tip_gnojiva')
            ->filter()
            ->merge(array_keys(Gnojiva::POPIS))
            ->unique(fn ($t) => mb_strtolower(trim($t)))
            ->values();
    }

    public function addRow(): void
    {
        $this->preporukaId = null;
        $this->preporukaOpis = '';
        $this->showForm = true;
        $this->showKulturaPicker = false;
        $this->formKulturaSearch = '';
        $this->form = $this->praznaForma();
    }

    public function cancelAdd(): void
    {
        $this->preporukaId = null;
        $this->preporukaOpis = '';
        $this->showForm = false;
        $this->showKulturaPicker = false;
        $this->formKulturaSearch = '';
        $this->form = $this->praznaForma();
        $this->resetErrorBag();
    }

    public function openKulturaPicker(): void
    {
        $this->showKulturaPicker = true;
    }

    public function selectKultura(int $id): void
    {
        Kultura::where('user_id', auth()->id())->findOrFail($id);

        $this->form['kultura_id'] = $id;
        $this->formKulturaSearch = '';
        $this->showKulturaPicker = false;
    }

    public function saveNew(): void
    {
        $this->validate([
            'form.kultura_id'     => 'required|integer',
            'form.datum'          => 'required|date',
            'form.tip_gnojiva'    => 'required|string|max:100',
            'form.kolicina_kg_ha' => 'required|numeric|min:0.01|max:999999.99',
        ] + $this->hranivaPravila('form'));

        $zapis = Gnojidba::create(Gnojidba::sHranivima(array_merge($this->form, ['user_id' => auth()->id(), 'skladiste_id' => $this->skladisteZaSpremanje($this->form['skladiste_id'] ?? null)])));
        \App\Support\Preporuke::zavrsiUnosom($this->preporukaId, auth()->id(), 'gnojidba', $zapis->id);
        $this->preporukaId = null;
        $this->preporukaOpis = '';

        $this->showForm = false;
        $this->showKulturaPicker = false;
        $this->formKulturaSearch = '';
        $this->form = $this->praznaForma();
    }

    public function saveMultiple(): void
    {
        $this->validate([
            'form.datum'          => 'required|date',
            'form.tip_gnojiva'    => 'nullable|string|max:100',
            'form.kolicina_kg_ha' => 'nullable|numeric|min:0.01|max:999999.99',
        ] + $this->hranivaPravila('form'));

        $kulture = $this->filteredKulture;

        if ($kulture->isEmpty()) {
            $this->addError('formKulturaSearch', 'Nema kultura koje odgovaraju pretrazi.');
            return;
        }

        $data = [
            'datum'          => $this->form['datum'],
            'tip_gnojiva'    => $this->form['tip_gnojiva'] !== '' ? $this->form['tip_gnojiva'] : null,
            'kolicina_kg_ha' => $this->form['kolicina_kg_ha'] !== '' ? $this->form['kolicina_kg_ha'] : null,
            'n_posto'        => $this->form['n_posto'],
            'p_posto'        => $this->form['p_posto'],
            'k_posto'        => $this->form['k_posto'],
            'organsko'       => $this->form['organsko'],
            'skladiste_id'   => $this->skladisteZaSpremanje($this->form['skladiste_id'] ?? null),
            'user_id'        => auth()->id(),
        ];
        $data = Gnojidba::sHranivima($data);

        foreach ($kulture as $kultura) {
            Gnojidba::create(array_merge($data, ['kultura_id' => $kultura->id]));
        }

        $this->showForm = false;
        $this->showKulturaPicker = false;
        $this->formKulturaSearch = '';
        $this->form = $this->praznaForma();
    }

    public function startEdit(int $id): void
    {
        $row = Gnojidba::findOrFail($id);
        $this->editingId = $id;
        $this->editForm = [
            'kultura_id'     => $row->kultura_id,
            'datum'          => $row->datum->format('Y-m-d'),
            'tip_gnojiva'    => $row->tip_gnojiva,
            'kolicina_kg_ha' => $row->kolicina_kg_ha,
            'n_posto'        => $row->n_posto ?? '',
            'p_posto'        => $row->p_posto ?? '',
            'k_posto'        => $row->k_posto ?? '',
            'organsko'       => (bool) $row->organsko,
            'skladiste_id'   => $row->skladiste_id ?? '',
        ];
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editForm.kultura_id'     => 'required|integer',
            'editForm.datum'          => 'required|date',
            'editForm.tip_gnojiva'    => 'required|string|max:100',
            'editForm.kolicina_kg_ha' => 'required|numeric|min:0.01|max:999999.99',
        ] + $this->hranivaPravila('editForm'));

        Gnojidba::where('id', $this->editingId)
            ->where('user_id', auth()->id())
            ->update(Gnojidba::sHranivima(array_merge($this->editForm, ['skladiste_id' => $this->skladisteZaSpremanje($this->editForm['skladiste_id'] ?? null)])));

        $this->editingId = null;
        $this->editForm = [];
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editForm = [];
    }

    public function copyRow(int $id): void
    {
        $row = Gnojidba::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        Gnojidba::create([
            'user_id'        => auth()->id(),
            'kultura_id'     => $row->kultura_id,
            'datum'          => now()->toDateString(),
            'tip_gnojiva'    => $row->tip_gnojiva,
            'kolicina_kg_ha' => $row->kolicina_kg_ha,
            'n_posto'        => $row->n_posto,
            'p_posto'        => $row->p_posto,
            'k_posto'        => $row->k_posto,
            'organsko'       => $row->organsko,
            'kg_n_ha'        => $row->kg_n_ha,
            'skladiste_id'   => $row->skladiste_id,
        ]);
    }

    public function deleteRow(int $id): void
    {
        Gnojidba::where('id', $id)->where('user_id', auth()->id())->delete();
    }

    public function toggleColumn(string $key): void
    {
        if (in_array($key, $this->visibleColumns)) {
            $this->visibleColumns = array_values(array_filter($this->visibleColumns, fn($c) => $c !== $key));
        } else {
            $this->visibleColumns[] = $key;
        }
    }

    public function render()
    {
        return view('livewire.gnojidba-table');
    }
}
