<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Prskanje;
use App\Models\Kultura;

class PrskanjeTable extends Component
{
    public bool $showForm = false;
    public array $form = [
        'kultura_id'                => '',
        'datum_tretiranja'          => '',
        'tretirana_povrsina_ha'     => '',
        'trgovacki_naziv_sredstva'  => '',
        'kolicina_sredstva_l_ha'    => '',
        'vrijeme_od'                => '',
        'vrijeme_do'                => '',
        'kolicina_vode_l_ha'        => '',
    ];
    public array $editForm = [];
    public ?int $editingId = null;
    public array $visibleColumns = ['datum', 'arkod', 'kultura', 'vel_povrsina', 'tret_povrsina', 'sredstvo', 'kolicina', 'vrijeme', 'voda'];
    public string $search = '';
    public array $filters = ['datum' => '', 'arkod' => '', 'kultura' => '', 'sredstvo' => ''];
    public string $formKulturaSearch = '';

    public function mount(): void
    {
        $this->form['datum_tretiranja'] = date('Y-m-d');
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
        if (!$this->formKulturaSearch) {
            return $this->kulture;
        }
        $search = strtolower($this->formKulturaSearch);
        return $this->kulture->filter(fn($k) =>
            str_contains(strtolower($k->arkod_broj), $search) ||
            str_contains(strtolower($k->naziv), $search)
        );
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

    public function getTrgovackiNaziviProperty()
    {
        return Prskanje::where('user_id', auth()->id())
            ->distinct()
            ->orderBy('trgovacki_naziv_sredstva')
            ->pluck('trgovacki_naziv_sredstva')
            ->filter();
    }

    public function addRow(): void
    {
        $this->showForm = true;
        $this->formKulturaSearch = '';
        $this->form = [
            'kultura_id'               => '',
            'datum_tretiranja'         => date('Y-m-d'),
            'tretirana_povrsina_ha'    => '',
            'trgovacki_naziv_sredstva' => '',
            'kolicina_sredstva_l_ha'   => '',
            'vrijeme_od'               => '',
            'vrijeme_do'               => '',
            'kolicina_vode_l_ha'       => '',
        ];
    }

    public function saveNew(): void
    {
        $this->validate([
            'form.kultura_id'               => 'required|integer',
            'form.datum_tretiranja'         => 'required|date',
            'form.tretirana_povrsina_ha'    => 'nullable|numeric|min:0.01',
            'form.trgovacki_naziv_sredstva' => 'required|string|max:200',
            'form.kolicina_sredstva_l_ha'   => 'required|numeric|min:0.001',
            'form.vrijeme_od'               => 'nullable|date_format:H:i',
            'form.vrijeme_do'               => 'nullable|date_format:H:i',
            'form.kolicina_vode_l_ha'       => 'nullable|numeric|min:0',
        ]);

        $data = $this->form;
        $data['tretirana_povrsina_ha'] = $data['tretirana_povrsina_ha'] !== '' ? $data['tretirana_povrsina_ha'] : null;
        $data['kolicina_vode_l_ha'] = $data['kolicina_vode_l_ha'] !== '' ? $data['kolicina_vode_l_ha'] : null;
        $data['vrijeme_od'] = $data['vrijeme_od'] !== '' ? $data['vrijeme_od'] : null;
        $data['vrijeme_do'] = $data['vrijeme_do'] !== '' ? $data['vrijeme_do'] : null;
        Prskanje::create(array_merge($data, ['user_id' => auth()->id()]));

        $this->showForm = false;
        $this->formKulturaSearch = '';
        $this->form = [
            'kultura_id' => '', 'datum_tretiranja' => date('Y-m-d'),
            'tretirana_povrsina_ha' => '', 'trgovacki_naziv_sredstva' => '',
            'kolicina_sredstva_l_ha' => '', 'vrijeme_od' => '',
            'vrijeme_do' => '', 'kolicina_vode_l_ha' => '',
        ];
    }

    public function saveMultiple(): void
    {
        $this->validate([
            'form.datum_tretiranja'         => 'required|date',
            'form.tretirana_povrsina_ha'    => 'nullable|numeric|min:0.01',
            'form.trgovacki_naziv_sredstva' => 'nullable|string|max:200',
            'form.kolicina_sredstva_l_ha'   => 'nullable|numeric|min:0.001',
            'form.vrijeme_od'               => 'nullable|date_format:H:i',
            'form.vrijeme_do'               => 'nullable|date_format:H:i',
            'form.kolicina_vode_l_ha'       => 'nullable|numeric|min:0',
        ]);

        $kulture = $this->filteredKulture;

        if ($kulture->isEmpty()) {
            $this->addError('formKulturaSearch', 'Nema kultura koje odgovaraju pretrazi.');
            return;
        }

        $data = $this->form;
        $data['tretirana_povrsina_ha']    = $data['tretirana_povrsina_ha'] !== ''    ? $data['tretirana_povrsina_ha']    : null;
        $data['trgovacki_naziv_sredstva'] = $data['trgovacki_naziv_sredstva'] !== '' ? $data['trgovacki_naziv_sredstva'] : null;
        $data['kolicina_sredstva_l_ha']   = $data['kolicina_sredstva_l_ha'] !== ''   ? $data['kolicina_sredstva_l_ha']   : null;
        $data['kolicina_vode_l_ha']       = $data['kolicina_vode_l_ha'] !== ''       ? $data['kolicina_vode_l_ha']       : null;
        $data['vrijeme_od']               = $data['vrijeme_od'] !== ''               ? $data['vrijeme_od']               : null;
        $data['vrijeme_do']               = $data['vrijeme_do'] !== ''               ? $data['vrijeme_do']               : null;
        $data['user_id'] = auth()->id();
        unset($data['kultura_id']);

        foreach ($kulture as $kultura) {
            Prskanje::create(array_merge($data, ['kultura_id' => $kultura->id]));
        }

        $this->showForm = false;
        $this->formKulturaSearch = '';
        $this->form = [
            'kultura_id' => '', 'datum_tretiranja' => date('Y-m-d'),
            'tretirana_povrsina_ha' => '', 'trgovacki_naziv_sredstva' => '',
            'kolicina_sredstva_l_ha' => '', 'vrijeme_od' => '',
            'vrijeme_do' => '', 'kolicina_vode_l_ha' => '',
        ];
    }

    public function startEdit(int $id): void
    {
        $row = Prskanje::findOrFail($id);
        $this->editingId = $id;
        $this->editForm = [
            'kultura_id'               => $row->kultura_id,
            'datum_tretiranja'         => $row->datum_tretiranja->format('Y-m-d'),
            'tretirana_povrsina_ha'    => $row->tretirana_povrsina_ha,
            'trgovacki_naziv_sredstva' => $row->trgovacki_naziv_sredstva,
            'kolicina_sredstva_l_ha'   => $row->kolicina_sredstva_l_ha,
            'vrijeme_od'               => $row->vrijeme_od ? substr($row->vrijeme_od, 0, 5) : '',
            'vrijeme_do'               => $row->vrijeme_do ? substr($row->vrijeme_do, 0, 5) : '',
            'kolicina_vode_l_ha'       => $row->kolicina_vode_l_ha,
        ];
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editForm.kultura_id'               => 'required|integer',
            'editForm.datum_tretiranja'         => 'required|date',
            'editForm.tretirana_povrsina_ha'    => 'nullable|numeric|min:0.01',
            'editForm.trgovacki_naziv_sredstva' => 'required|string|max:200',
            'editForm.kolicina_sredstva_l_ha'   => 'required|numeric|min:0.001',
            'editForm.vrijeme_od'               => 'nullable|date_format:H:i',
            'editForm.vrijeme_do'               => 'nullable|date_format:H:i',
            'editForm.kolicina_vode_l_ha'       => 'nullable|numeric|min:0',
        ]);

        $data = $this->editForm;
        $data['tretirana_povrsina_ha'] = $data['tretirana_povrsina_ha'] !== '' ? $data['tretirana_povrsina_ha'] : null;
        $data['kolicina_vode_l_ha'] = $data['kolicina_vode_l_ha'] !== '' ? $data['kolicina_vode_l_ha'] : null;
        $data['vrijeme_od'] = $data['vrijeme_od'] !== '' ? $data['vrijeme_od'] : null;
        $data['vrijeme_do'] = $data['vrijeme_do'] !== '' ? $data['vrijeme_do'] : null;
        Prskanje::where('id', $this->editingId)
            ->where('user_id', auth()->id())
            ->update($data);

        $this->editingId = null;
        $this->editForm = [];
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editForm = [];
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
