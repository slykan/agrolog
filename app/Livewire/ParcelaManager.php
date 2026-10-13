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

    /** Kultura koju nova kultura zamjenjuje (stara se označi završenom i ostaje u evidenciji). */
    public ?int $zamjenaZaId = null;
    public string $zavrsenaDatum = '';
    public string $poruka = '';

    public function mount(): void
    {
        $this->kulturaForm['godina'] = date('Y');
    }

    #[Computed]
    public function parcele()
    {
        // Sortiraj po datumu unosa, najnovija prva
        return Parcela::where('user_id', auth()->id())
            ->with(['kulture' => fn($q) => $q->withCount(['gnojidbe', 'prskanja'])
                ->orderByRaw('zavrsena_at IS NOT NULL')->orderByDesc('godina')->orderByDesc('zavrsena_at')->orderBy('naziv')])
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
        $parcela = Parcela::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        // Brisanje parcele kaskadno briše kulture i njihovu evidenciju — ne dopuštamo ako zapisa ima
        $kultureIds = Kultura::where('parcela_id', $parcela->id)->pluck('id');
        $imaZapise = \App\Models\Gnojidba::whereIn('kultura_id', $kultureIds)->exists()
            || \App\Models\Prskanje::whereIn('kultura_id', $kultureIds)->exists();
        if ($imaZapise) {
            $this->poruka = "Parcela {$parcela->arkod_broj} ima zapise gnojidbe/prskanja i ne može se obrisati — evidenciju je obvezno čuvati.";
            return;
        }

        $parcela->delete();
    }

    public function openKulturaForm(int $parcelaId): void
    {
        $this->kulturaParcelaId = $parcelaId;
        $this->showKulturaForm = true;
        $this->editKulturaId = null;
        $this->zamjenaZaId = null;
        $this->kulturaForm = ['naziv' => '', 'posadjena_povrsina_ha' => '', 'godina' => date('Y')];
    }

    /** Nova kultura na istoj parceli umjesto postojeće (npr. ječam → kukuruz). Stara ostaje sa svim zapisima. */
    public function zamijeniKulturu(int $id): void
    {
        $k = Kultura::where('user_id', auth()->id())->findOrFail($id);
        $this->kulturaParcelaId = $k->parcela_id;
        $this->zamjenaZaId = $k->id;
        $this->zavrsenaDatum = date('Y-m-d');
        $this->editKulturaId = null;
        $this->showKulturaForm = true;
        $this->kulturaForm = ['naziv' => '', 'posadjena_povrsina_ha' => $k->posadjena_povrsina_ha, 'godina' => date('Y')];
        $this->poruka = '';
        $this->clearValidation();
    }

    public function zavrsiKulturu(int $id): void
    {
        Kultura::where('id', $id)->where('user_id', auth()->id())->update(['zavrsena_at' => date('Y-m-d')]);
    }

    public function vratiKulturu(int $id): void
    {
        Kultura::where('id', $id)->where('user_id', auth()->id())->update(['zavrsena_at' => null]);
    }

    public function closeKulturaForm(): void
    {
        $this->showKulturaForm = false;
        $this->zamjenaZaId = null;
        $this->clearValidation();
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
        $this->showKulturaForm = false;
        $this->clearValidation();
    }

    public function cancelKulturaEdit(): void
    {
        $this->editKulturaId = null;
        $this->kulturaForm = ['naziv' => '', 'posadjena_povrsina_ha' => '', 'godina' => date('Y')];
        $this->clearValidation();
    }

    public function saveKultura($nova = false): void
    {
        $this->validate([
            'kulturaForm.naziv'                 => 'required|string|max:100',
            'kulturaForm.posadjena_povrsina_ha' => 'required|numeric|min:0.01',
            'kulturaForm.godina'                => 'required|integer|min:2000|max:2100',
        ]);

        $parcela = Parcela::where('user_id', auth()->id())->findOrFail($this->kulturaParcelaId);

        // Naziv kulture koja već ima zapise ne mijenja se — to bi prepisalo povijest gnojidbe i prskanja
        if ($this->editKulturaId) {
            $stara = Kultura::where('user_id', auth()->id())->findOrFail($this->editKulturaId);
            if (mb_strtolower(trim($stara->naziv)) !== mb_strtolower(trim($this->kulturaForm['naziv'])) && $stara->imaZapise()) {
                $this->addError('kulturaForm.naziv',
                    "Kultura „{$stara->naziv}\" već ima zapise gnojidbe/prskanja. Za novu kulturu na parceli koristi „Zamijeni kulturu\" — stari zapisi ostaju netaknuti.");
                return;
            }
        }
        if ($this->zamjenaZaId) {
            $this->validate(['zavrsenaDatum' => 'required|date'], [], ['zavrsenaDatum' => 'datum završetka']);
        }

        // Zbroj površina ostalih aktivnih kultura na ovoj parceli (iste godine) — završene se ne broje
        $zauzetoHa = Kultura::where('parcela_id', $this->kulturaParcelaId)
            ->where('godina', $this->kulturaForm['godina'])
            ->whereNull('zavrsena_at')
            ->when($this->editKulturaId, fn($q) => $q->where('id', '!=', $this->editKulturaId))
            ->when($this->zamjenaZaId, fn($q) => $q->where('id', '!=', $this->zamjenaZaId))
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
            if ($this->zamjenaZaId) {
                Kultura::where('id', $this->zamjenaZaId)->where('user_id', auth()->id())
                    ->update(['zavrsena_at' => $this->zavrsenaDatum]);
                $nova = false; // zamjena je jedna kultura — forma se zatvara
            }
            Kultura::create($data);
        }

        $this->editKulturaId = null;
        $this->zamjenaZaId = null;
        $this->poruka = '';
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
        $k = Kultura::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        // Brisanje bi kaskadno obrisalo i evidenciju gnojidbe/prskanja, koju je obvezno čuvati
        if ($k->imaZapise()) {
            $this->poruka = "Kultura „{$k->naziv}\" ima zapise gnojidbe/prskanja i ne može se obrisati. Označi je završenom ili je zamijeni novom kulturom.";
            return;
        }

        $k->delete();
    }

    public function render() { return view('livewire.parcela-manager'); }
}
