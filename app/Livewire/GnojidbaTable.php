<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Gnojidba;
use App\Models\Kultura;

class GnojidbaTable extends Component
{
    public bool $showForm = false;
    public array $form = [
        'kultura_id'    => '',
        'datum'         => '',
        'tip_gnojiva'   => '',
        'kolicina_kg_ha' => '',
    ];
    public array $editForm = [];
    public ?int $editingId = null;
    public array $visibleColumns = ['arkod', 'povrsina', 'kultura', 'datum', 'gnojivo', 'kolicina'];
    public string $search = '';
    public array $filters = ['arkod' => '', 'kultura' => '', 'datum' => '', 'gnojivo' => ''];
    public string $formKulturaSearch = '';

    public function mount(): void
    {
        $this->form['datum'] = date('Y-m-d');
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
            ->filter();
    }

    public function addRow(): void
    {
        $this->showForm = true;
        $this->formKulturaSearch = '';
        $this->form = [
            'kultura_id'     => '',
            'datum'          => date('Y-m-d'),
            'tip_gnojiva'    => '',
            'kolicina_kg_ha' => '',
        ];
    }

    public function saveNew(): void
    {
        $this->validate([
            'form.kultura_id'     => 'required|integer',
            'form.datum'          => 'required|date',
            'form.tip_gnojiva'    => 'required|string|max:100',
            'form.kolicina_kg_ha' => 'required|numeric|min:0.01',
        ]);

        Gnojidba::create(array_merge($this->form, ['user_id' => auth()->id()]));

        $this->showForm = false;
        $this->formKulturaSearch = '';
        $this->form = ['kultura_id' => '', 'datum' => date('Y-m-d'), 'tip_gnojiva' => '', 'kolicina_kg_ha' => ''];
    }

    public function saveMultiple(): void
    {
        $this->validate([
            'form.datum'          => 'required|date',
            'form.tip_gnojiva'    => 'required|string|max:100',
            'form.kolicina_kg_ha' => 'required|numeric|min:0.01',
        ]);

        $kulture = $this->filteredKulture;

        if ($kulture->isEmpty()) {
            $this->addError('formKulturaSearch', 'Nema kultura koje odgovaraju pretrazi.');
            return;
        }

        $data = [
            'datum'          => $this->form['datum'],
            'tip_gnojiva'    => $this->form['tip_gnojiva'],
            'kolicina_kg_ha' => $this->form['kolicina_kg_ha'],
            'user_id'        => auth()->id(),
        ];

        foreach ($kulture as $kultura) {
            Gnojidba::create(array_merge($data, ['kultura_id' => $kultura->id]));
        }

        $this->showForm = false;
        $this->formKulturaSearch = '';
        $this->form = ['kultura_id' => '', 'datum' => date('Y-m-d'), 'tip_gnojiva' => '', 'kolicina_kg_ha' => ''];
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
        ];
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editForm.kultura_id'     => 'required|integer',
            'editForm.datum'          => 'required|date',
            'editForm.tip_gnojiva'    => 'required|string|max:100',
            'editForm.kolicina_kg_ha' => 'required|numeric|min:0.01',
        ]);

        Gnojidba::where('id', $this->editingId)
            ->where('user_id', auth()->id())
            ->update($this->editForm);

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
