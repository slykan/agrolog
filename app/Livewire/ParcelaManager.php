<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Parcela;
use App\Models\Kultura;

class ParcelaManager extends Component
{
    public bool $showParcelaForm = false;
    public array $parcelaForm = ['arkod_broj' => '', 'povrsina_ha' => ''];
    public ?int $editParcelaId = null;

    public bool $showKulturaForm = false;
    public ?int $kulturaParcelaId = null;
    public array $kulturaForm = ['naziv' => '', 'posadjena_povrsina_ha' => '', 'godina' => ''];
    public ?int $editKulturaId = null;

    public function mount(): void
    {
        $this->kulturaForm['godina'] = date('Y');
    }

    #[Computed]
    public function parcele()
    {
        // Sortiraj po datumu unosa, najnovija prva
        return Parcela::where('user_id', auth()->id())
            ->with(['kulture' => fn($q) => $q->orderByDesc('godina')->orderBy('naziv')])
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function postojeceKulture()
    {
        return Kultura::where('user_id', auth()->id())
            ->distinct()
            ->orderBy('naziv')
            ->pluck('naziv');
    }

    public function saveParcela(): void
    {
        $this->validate([
            'parcelaForm.arkod_broj'  => 'required|string|max:20',
            'parcelaForm.povrsina_ha' => 'required|numeric|min:0.01',
        ]);

        $isEdit = (bool) $this->editParcelaId;
        $parcela = null;

        if ($this->editParcelaId) {
            Parcela::where('id', $this->editParcelaId)->where('user_id', auth()->id())
                ->update(['arkod_broj' => $this->parcelaForm['arkod_broj'], 'povrsina_ha' => $this->parcelaForm['povrsina_ha']]);
            $parcela = Parcela::where('id', $this->editParcelaId)->first();
        } else {
            $parcela = Parcela::create(array_merge($this->parcelaForm, ['user_id' => auth()->id()]));
        }

        $this->showParcelaForm = false;
        $this->editParcelaId = null;
        $this->parcelaForm = ['arkod_broj' => '', 'povrsina_ha' => ''];

        // Automatski otvori formu za unos kulture samo za novu parcelu
        if ($parcela && !$isEdit) {
            $this->kulturaParcelaId = $parcela->id;
            $this->showKulturaForm = true;
            $this->editKulturaId = null;
            $this->kulturaForm = [
                'naziv' => '',
                'posadjena_povrsina_ha' => $parcela->povrsina_ha,
                'godina' => date('Y'),
            ];
        }
    }

    public function editParcela(int $id): void
    {
        $p = Parcela::findOrFail($id);
        $this->editParcelaId = $id;
        $this->parcelaForm = ['arkod_broj' => $p->arkod_broj, 'povrsina_ha' => $p->povrsina_ha];
        $this->showParcelaForm = false;
        $this->clearValidation();
    }

    public function cancelParcelaEdit(): void
    {
        $this->editParcelaId = null;
        $this->parcelaForm = ['arkod_broj' => '', 'povrsina_ha' => ''];
        $this->clearValidation();
    }

    public function deleteParcela(int $id): void
    {
        Parcela::where('id', $id)->where('user_id', auth()->id())->delete();
    }

    public function openKulturaForm(int $parcelaId): void
    {
        $this->kulturaParcelaId = $parcelaId;
        $this->showKulturaForm = true;
        $this->editKulturaId = null;
        $this->kulturaForm = ['naziv' => '', 'posadjena_povrsina_ha' => '', 'godina' => date('Y')];
    }

    public function editKultura(int $id): void
    {
        $k = Kultura::findOrFail($id);
        $this->editKulturaId = $id;
        $this->kulturaParcelaId = $k->parcela_id;
        $this->kulturaForm = [
            'naziv'                 => $k->naziv,
            'posadjena_povrsina_ha' => $k->posadjena_povrsina_ha,
            'godina'                => $k->godina,
        ];
        $this->showKulturaForm = true;
    }

    public function saveKultura($nova = false): void
    {
        $this->validate([
            'kulturaForm.naziv'                 => 'required|string|max:100',
            'kulturaForm.posadjena_povrsina_ha' => 'required|numeric|min:0.01',
            'kulturaForm.godina'                => 'required|integer|min:2000|max:2100',
        ]);

        $parcela = Parcela::findOrFail($this->kulturaParcelaId);

        // Zbroj površina ostalih kultura na ovoj parceli (iste godine)
        $zauzetoHa = Kultura::where('parcela_id', $this->kulturaParcelaId)
            ->where('godina', $this->kulturaForm['godina'])
            ->when($this->editKulturaId, fn($q) => $q->where('id', '!=', $this->editKulturaId))
            ->sum('posadjena_povrsina_ha');

        $novaHa = (float) $this->kulturaForm['posadjena_povrsina_ha'];

        if (($zauzetoHa + $novaHa) > $parcela->povrsina_ha) {
            $preostalo = round($parcela->povrsina_ha - $zauzetoHa, 2);
            $this->addError('kulturaForm.posadjena_povrsina_ha',
                "Prekoračenje! Parcela ima {$parcela->povrsina_ha} ha, već zauzeto {$zauzetoHa} ha. Možeš unijeti max {$preostalo} ha.");
            return;
        }

        $data = array_merge($this->kulturaForm, [
            'user_id'    => auth()->id(),
            'parcela_id' => $this->kulturaParcelaId,
        ]);

        if ($this->editKulturaId) {
            Kultura::where('id', $this->editKulturaId)->where('user_id', auth()->id())->update($data);
        } else {
            Kultura::create($data);
        }

        $this->editKulturaId = null;
        if ($nova) {
            // Ostavlja formu otvorenom za unos sledeće kulture
            $this->kulturaForm = [
                'naziv' => '',
                'posadjena_povrsina_ha' => '',
                'godina' => date('Y'),
            ];
        } else {
            $this->showKulturaForm = false;
        }
    }

    public function deleteKultura(int $id): void
    {
        Kultura::where('id', $id)->where('user_id', auth()->id())->delete();
    }

    public function render() { return view('livewire.parcela-manager'); }
}
