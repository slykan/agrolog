<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Prskanje;
use App\Models\Kultura;
use App\Models\SzbSredstvo;
use App\Support\SzbPopisi;
use App\Livewire\Concerns\BiraSkladiste;

class PrskanjeTable extends Component
{
    use BiraSkladiste;

    public bool $showForm = false;
    public array $form = [
        'kultura_id'                => '',
        'datum_tretiranja'          => '',
        'tretirana_povrsina_ha'     => '',
        'trgovacki_naziv_sredstva'  => '',
        'registracijski_broj'       => '',
        'kolicina_sredstva_l_ha'    => '',
        'vrijeme_od'                => '',
        'vrijeme_do'                => '',
        'kolicina_vode_l_ha'        => '',
    ];
    public array $editForm = [];
    public ?int $editingId = null;
    public array $visibleColumns = ['datum', 'arkod', 'kultura', 'vel_povrsina', 'tret_povrsina', 'sredstvo', 'kolicina', 'vrijeme', 'voda', 'szb'];
    public string $search = '';
    public array $filters = ['datum' => '', 'arkod' => '', 'kultura' => '', 'sredstvo' => ''];
    public string $formKulturaSearch = '';
    public bool $showKulturaPicker = false;
    /** Napomena uz sredstvo (nije u registru FIS-a / registracija nije aktivna), za novi i uređeni zapis. */
    public string $sredstvoNapomena = '';
    public string $editSredstvoNapomena = '';
    protected array $validationAttributes = [
        'form.kultura_id' => 'ARKOD parcela',
        'form.datum_tretiranja' => 'datum tretiranja',
        'form.tretirana_povrsina_ha' => 'tretirana povrsina',
        'form.trgovacki_naziv_sredstva' => 'trgovacki naziv sredstva',
        'form.registracijski_broj' => 'registracijski broj',
        'form.kolicina_sredstva_l_ha' => 'kolicina sredstva L/ha',
        'form.vrijeme_od' => 'vrijeme od',
        'form.vrijeme_do' => 'vrijeme do',
        'form.kolicina_vode_l_ha' => 'kolicina vode L/ha',
        'editForm.kultura_id' => 'ARKOD parcela',
        'editForm.datum_tretiranja' => 'datum tretiranja',
        'editForm.tretirana_povrsina_ha' => 'tretirana povrsina',
        'editForm.trgovacki_naziv_sredstva' => 'trgovacki naziv sredstva',
        'editForm.registracijski_broj' => 'registracijski broj',
        'editForm.kolicina_sredstva_l_ha' => 'kolicina sredstva L/ha',
        'editForm.vrijeme_od' => 'vrijeme od',
        'editForm.vrijeme_do' => 'vrijeme do',
        'editForm.kolicina_vode_l_ha' => 'kolicina vode L/ha',
    ];

    public function mount(): void
    {
        $this->form = $this->praznaForma();
    }

    /** Nova prazna forma; vrsta uporabe i sustav proizvodnje preuzimaju se iz zadnjeg prskanja. */
    private function praznaForma(): array
    {
        $this->sredstvoNapomena = '';
        $zadnje = Prskanje::where('user_id', auth()->id())->latest('id')->first(['vrsta_uporabe', 'sustav_proizvodnje']);

        return [
            'kultura_id'               => '',
            'datum_tretiranja'         => date('Y-m-d'),
            'tretirana_povrsina_ha'    => '',
            'trgovacki_naziv_sredstva' => '',
            'registracijski_broj'      => '',
            'kolicina_sredstva_l_ha'   => '',
            'vrijeme_od'               => '',
            'vrijeme_do'               => '',
            'kolicina_vode_l_ha'       => '',
            'vrsta_uporabe'            => $zadnje?->vrsta_uporabe ?? 'otvoreno',
            'bbch'                     => '',
            'sustav_proizvodnje'       => $zadnje?->sustav_proizvodnje ?? 'neekoloska',
            'skladiste_id'             => \App\Support\Skladiste::zadanoId(auth()->user()) ?? '',
        ];
    }

    /** Pravila za polja evidencije SZB 2027. (EU 2023/564), zajednička za novi i uređeni zapis. */
    private function szbPravila(string $prefix): array
    {
        return [
            "$prefix.vrsta_uporabe"      => 'required|in:' . implode(',', array_keys(SzbPopisi::VRSTE_UPORABE)),
            "$prefix.bbch"               => 'nullable|string|max:255',
            "$prefix.sustav_proizvodnje" => 'required|in:' . implode(',', array_keys(SzbPopisi::SUSTAVI)),
        ];
    }

    /** Fenofaze (BBCH) za kulturu odabranu u formi — prijedlozi u polju, slobodan unos je dopušten. */
    public function bbchPrijedlozi($kulturaId): array
    {
        $k = $kulturaId ? $this->kulture->firstWhere('id', (int) $kulturaId) : null;

        return $k ? SzbPopisi::bbchZaUsjev($k->eppo_naziv ?: $k->naziv) : [];
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
            'id'      => $k->id,
            'arkod'   => $k->arkod_broj,
            'naziv'   => $k->naziv,
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

    public function getPrskanjaProperty()
    {
        $query = Prskanje::where('prskanja.user_id', auth()->id())
            ->join('kulture', 'kulture.id', '=', 'prskanja.kultura_id')
            ->join('parcele', 'parcele.id', '=', 'kulture.parcela_id')
            ->select(
                'prskanja.*',
                'kulture.naziv as kultura_naziv',
                'kulture.posadjena_povrsina_ha',
                'parcele.arkod_broj',
                'parcele.povrsina_ha'
            )
            ->orderByDesc('prskanja.datum_tretiranja')
            ->orderByDesc('prskanja.id');

        if ($this->filters['datum']) {
            $query->where('prskanja.datum_tretiranja', $this->filters['datum']);
        }
        if ($this->filters['arkod']) {
            $query->where('parcele.arkod_broj', 'like', '%' . $this->filters['arkod'] . '%');
        }
        if ($this->filters['kultura']) {
            $query->where('kulture.naziv', 'like', '%' . $this->filters['kultura'] . '%');
        }
        if ($this->filters['sredstvo']) {
            $query->where('prskanja.trgovacki_naziv_sredstva', 'like', '%' . $this->filters['sredstvo'] . '%');
        }
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('parcele.arkod_broj', 'like', '%' . $this->search . '%')
                  ->orWhere('kulture.naziv', 'like', '%' . $this->search . '%')
                  ->orWhere('prskanja.trgovacki_naziv_sredstva', 'like', '%' . $this->search . '%');
            });
        }

        return $query->get();
    }

    /** Registracijski broj iz zadnjeg prskanja istim sredstvom (da se ne upisuje svaki put). */
    private function zadnjiRegistracijskiBroj(?string $naziv): ?string
    {
        if (blank($naziv)) {
            return null;
        }

        return Prskanje::where('user_id', auth()->id())
            ->where('trgovacki_naziv_sredstva', trim($naziv))
            ->whereNotNull('registracijski_broj')
            ->latest('id')
            ->value('registracijski_broj');
    }

    /**
     * Odabrano sredstvo: registracijski broj iz registra FIS-a (ako je sredstvo u registru),
     * inače iz zadnjeg prskanja istim sredstvom. Vraća [registracijski broj ili null, napomena].
     */
    private function sredstvoIzRegistra(?string $naziv, ?string $trenutniBroj): array
    {
        if (blank($naziv)) {
            return [$trenutniBroj, ''];
        }

        $sredstvo = SzbSredstvo::poNazivu($naziv);
        if ($sredstvo) {
            $napomena = $sredstvo->aktivno ? '' : 'Registracija ovog sredstva u FIS-u nije aktivna — provjerite smije li se još primjenjivati.';
            return [$sredstvo->registracijski_broj ?: $trenutniBroj, $napomena];
        }

        $broj = blank($trenutniBroj) ? $this->zadnjiRegistracijskiBroj($naziv) : $trenutniBroj;
        $napomena = SzbSredstvo::exists() ? 'Sredstvo nije pronađeno u registru FIS-a — provjerite naziv s etikete.' : '';

        return [$broj, $napomena];
    }

    public function updatedFormTrgovackiNazivSredstva($naziv): void
    {
        [$broj, $this->sredstvoNapomena] = $this->sredstvoIzRegistra($naziv, $this->form['registracijski_broj'] ?? null);
        $this->form['registracijski_broj'] = $broj ?? '';
    }

    public function updatedEditFormTrgovackiNazivSredstva($naziv): void
    {
        [$broj, $this->editSredstvoNapomena] = $this->sredstvoIzRegistra($naziv, $this->editForm['registracijski_broj'] ?? null);
        $this->editForm['registracijski_broj'] = $broj ?? '';
    }

    public function addRow(): void
    {
        $this->showForm = true;
        $this->showKulturaPicker = false;
        $this->formKulturaSearch = '';
        $this->form = $this->praznaForma();
    }

    public function cancelAdd(): void
    {
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
        $kultura = Kultura::where('user_id', auth()->id())->findOrFail($id);

        $this->form['kultura_id'] = $kultura->id;
        $this->form['tretirana_povrsina_ha'] = $kultura->posadjena_povrsina_ha;
        $this->formKulturaSearch = '';
        $this->showKulturaPicker = false;
    }

    public function updatedFormKulturaId($id): void
    {
        if (!$id) {
            $this->form['tretirana_povrsina_ha'] = '';
            return;
        }

        $kultura = $this->kulture->firstWhere('id', (int) $id);

        if ($kultura) {
            $this->form['tretirana_povrsina_ha'] = $kultura->posadjena_povrsina_ha;
        }
    }

    public function saveNew(): void
    {
        $this->validate([
            'form.kultura_id'               => 'required|integer',
            'form.datum_tretiranja'         => 'required|date',
            'form.tretirana_povrsina_ha'    => 'nullable|numeric|min:0|max:999999.99',
            'form.trgovacki_naziv_sredstva' => 'nullable|string|max:200',
            'form.registracijski_broj'      => 'nullable|string|max:60',
            'form.kolicina_sredstva_l_ha'   => 'required|numeric|min:0|max:99999.999',
            'form.vrijeme_od'               => 'nullable|date_format:H:i',
            'form.vrijeme_do'               => 'nullable|date_format:H:i',
            'form.kolicina_vode_l_ha'       => 'nullable|numeric|min:0|max:999999.99',
        ] + $this->szbPravila('form'));

        $data = $this->form;
        $data['tretirana_povrsina_ha'] = $data['tretirana_povrsina_ha'] !== '' ? $data['tretirana_povrsina_ha'] : null;
        $data['kolicina_vode_l_ha'] = $data['kolicina_vode_l_ha'] !== '' ? $data['kolicina_vode_l_ha'] : null;
        $data['vrijeme_od'] = $data['vrijeme_od'] !== '' ? $data['vrijeme_od'] : null;
        $data['vrijeme_do'] = $data['vrijeme_do'] !== '' ? $data['vrijeme_do'] : null;
        $data['registracijski_broj'] = blank($data['registracijski_broj'] ?? null) ? null : trim($data['registracijski_broj']);
        $data['bbch'] = blank($data['bbch'] ?? null) ? null : trim($data['bbch']);
        $data['skladiste_id'] = $this->skladisteZaSpremanje($data['skladiste_id'] ?? null);
        Prskanje::create(array_merge($data, ['user_id' => auth()->id()]));

        $this->showForm = false;
        $this->showKulturaPicker = false;
        $this->formKulturaSearch = '';
        $this->form = $this->praznaForma();
    }

    public function saveMultiple(): void
    {
        $this->validate([
            'form.datum_tretiranja'         => 'required|date',
            'form.tretirana_povrsina_ha'    => 'nullable|numeric|min:0|max:999999.99',
            'form.trgovacki_naziv_sredstva' => 'nullable|string|max:200',
            'form.registracijski_broj'      => 'nullable|string|max:60',
            'form.kolicina_sredstva_l_ha'   => 'nullable|numeric|min:0|max:99999.999',
            'form.vrijeme_od'               => 'nullable|date_format:H:i',
            'form.vrijeme_do'               => 'nullable|date_format:H:i',
            'form.kolicina_vode_l_ha'       => 'nullable|numeric|min:0|max:999999.99',
        ] + $this->szbPravila('form'));

        $kulture = $this->filteredKulture;

        if ($kulture->isEmpty()) {
            $this->addError('formKulturaSearch', 'Nema kultura koje odgovaraju pretrazi.');
            return;
        }

        $nullableFields = ['tretirana_povrsina_ha', 'trgovacki_naziv_sredstva', 'registracijski_broj', 'bbch', 'kolicina_sredstva_l_ha', 'kolicina_vode_l_ha', 'vrijeme_od', 'vrijeme_do'];
        $data = $this->form;
        foreach ($nullableFields as $field) {
            $data[$field] = blank($data[$field]) ? null : $data[$field];
        }
        $data['user_id'] = auth()->id();
        $data['skladiste_id'] = $this->skladisteZaSpremanje($data['skladiste_id'] ?? null);
        unset($data['kultura_id']);

        foreach ($kulture as $kultura) {
            Prskanje::create(array_merge($data, [
                'kultura_id' => $kultura->id,
                'tretirana_povrsina_ha' => $kultura->posadjena_povrsina_ha,
            ]));
        }

        $this->showForm = false;
        $this->showKulturaPicker = false;
        $this->formKulturaSearch = '';
        $this->form = $this->praznaForma();
    }

    public function startEdit(int $id): void
    {
        $row = Prskanje::findOrFail($id);
        $this->editSredstvoNapomena = '';
        $this->editingId = $id;
        $this->editForm = [
            'kultura_id'               => $row->kultura_id,
            'datum_tretiranja'         => $row->datum_tretiranja->format('Y-m-d'),
            'tretirana_povrsina_ha'    => $row->tretirana_povrsina_ha,
            'trgovacki_naziv_sredstva' => $row->trgovacki_naziv_sredstva,
            'registracijski_broj'      => $row->registracijski_broj,
            'kolicina_sredstva_l_ha'   => $row->kolicina_sredstva_l_ha,
            'vrijeme_od'               => $row->vrijeme_od ? substr($row->vrijeme_od, 0, 5) : '',
            'vrijeme_do'               => $row->vrijeme_do ? substr($row->vrijeme_do, 0, 5) : '',
            'kolicina_vode_l_ha'       => $row->kolicina_vode_l_ha,
            'vrsta_uporabe'            => $row->vrsta_uporabe ?? 'otvoreno',
            'bbch'                     => $row->bbch ?? '',
            'sustav_proizvodnje'       => $row->sustav_proizvodnje ?? 'neekoloska',
            'skladiste_id'             => $row->skladiste_id ?? '',
        ];
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editForm.kultura_id'               => 'required|integer',
            'editForm.datum_tretiranja'         => 'required|date',
            'editForm.tretirana_povrsina_ha'    => 'nullable|numeric|min:0|max:999999.99',
            'editForm.trgovacki_naziv_sredstva' => 'nullable|string|max:200',
            'editForm.registracijski_broj'      => 'nullable|string|max:60',
            'editForm.kolicina_sredstva_l_ha'   => 'required|numeric|min:0|max:99999.999',
            'editForm.vrijeme_od'               => 'nullable|date_format:H:i',
            'editForm.vrijeme_do'               => 'nullable|date_format:H:i',
            'editForm.kolicina_vode_l_ha'       => 'nullable|numeric|min:0|max:999999.99',
        ] + $this->szbPravila('editForm'));

        $data = $this->editForm;
        $data['tretirana_povrsina_ha'] = $data['tretirana_povrsina_ha'] !== '' ? $data['tretirana_povrsina_ha'] : null;
        $data['kolicina_vode_l_ha'] = $data['kolicina_vode_l_ha'] !== '' ? $data['kolicina_vode_l_ha'] : null;
        $data['vrijeme_od'] = $data['vrijeme_od'] !== '' ? $data['vrijeme_od'] : null;
        $data['vrijeme_do'] = $data['vrijeme_do'] !== '' ? $data['vrijeme_do'] : null;
        $data['registracijski_broj'] = blank($data['registracijski_broj'] ?? null) ? null : trim($data['registracijski_broj']);
        $data['bbch'] = blank($data['bbch'] ?? null) ? null : trim($data['bbch']);
        $data['skladiste_id'] = $this->skladisteZaSpremanje($data['skladiste_id'] ?? null);
        Prskanje::where('id', $this->editingId)
            ->where('user_id', auth()->id())
            ->update($data);

        $this->editingId = null;
        $this->editForm = [];
    }

    public function cancelEdit(): void
    {
        $this->editSredstvoNapomena = '';
        $this->editingId = null;
        $this->editForm = [];
    }

    public function copyRow(int $id): void
    {
        $row = Prskanje::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        Prskanje::create([
            'user_id'                   => auth()->id(),
            'kultura_id'                => $row->kultura_id,
            'datum_tretiranja'          => now()->toDateString(),
            'tretirana_povrsina_ha'     => $row->tretirana_povrsina_ha,
            'trgovacki_naziv_sredstva'  => $row->trgovacki_naziv_sredstva,
            'registracijski_broj'       => $row->registracijski_broj,
            'kolicina_sredstva_l_ha'    => $row->kolicina_sredstva_l_ha,
            'vrijeme_od'                => $row->vrijeme_od,
            'vrijeme_do'                => $row->vrijeme_do,
            'kolicina_vode_l_ha'        => $row->kolicina_vode_l_ha,
            'vrsta_uporabe'             => $row->vrsta_uporabe,
            'bbch'                      => $row->bbch,
            'sustav_proizvodnje'        => $row->sustav_proizvodnje,
            'skladiste_id'              => $row->skladiste_id,
        ]);
    }

    public function deleteRow(int $id): void
    {
        Prskanje::where('id', $id)->where('user_id', auth()->id())->delete();
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
        return view('livewire.prskanje-table');
    }
}
