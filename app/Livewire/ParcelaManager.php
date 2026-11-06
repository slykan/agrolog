<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Parcela;
use App\Models\Kultura;
use App\Support\ArkodServis;
use App\Support\Poligon;
use App\Support\SzbPopisi;
use Illuminate\Validation\Rule;

class ParcelaManager extends Component
{
    public bool $showParcelaForm = false;
    public array $parcelaForm = ['arkod_broj' => '', 'povrsina_ha' => ''];
    public ?int $editParcelaId = null;

    public bool $showKulturaForm = false;
    public ?int $kulturaParcelaId = null;
    public array $kulturaForm = ['naziv' => '', 'eppo_naziv' => '', 'posadjena_povrsina_ha' => '', 'godina' => ''];
    public ?int $editKulturaId = null;

    /** Kultura koju nova kultura zamjenjuje (stara se označi završenom i ostaje u evidenciji). */
    public ?int $zamjenaZaId = null;
    public string $zavrsenaDatum = '';
    public string $poruka = '';

    /** Parcela pronađena u javnom ARKOD-u za upisani broj (prikaz na karti i potvrda prije spremanja). */
    public ?array $arkodNadjen = null;
    public string $arkodPoruka = '';

    /** Parcela bez ARKOD-a: naziv ide u parcelaForm.arkod_broj, oblik dolazi s karte. */
    public bool $rucnoUnos = false;
    public ?array $rucniPoligon = null;

    public function mount(): void
    {
        $this->kulturaForm['godina'] = date('Y');
    }

    #[Computed]
    public function parcele()
    {
        // Sortiraj po datumu unosa, najnovija prva
        return Parcela::where('user_id', auth()->id())
            ->with(['kulture' => fn($q) => $q->withCount(['gnojidbe', 'prskanja', 'berbe'])
                ->orderByRaw('zavrsena_at IS NOT NULL')->orderByDesc('godina')->orderByDesc('zavrsena_at')->orderBy('naziv')])
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function postojeceKulture()
    {
        // prvo kulture koje je korisnik već upisao, zatim najčešće kulture; slobodan unos ostaje
        return Kultura::where('user_id', auth()->id())
            ->distinct()
            ->orderBy('naziv')
            ->pluck('naziv')
            ->merge(SzbPopisi::CESTE_KULTURE)
            ->unique(fn ($n) => mb_strtolower(trim($n)))
            ->values();
    }

    /** Područje postojećih parcela, da karta za ucrtavanje krene od njih. */
    #[Computed]
    public function okvirKarte(): ?array
    {
        $tocke = Parcela::where('user_id', auth()->id())->whereNotNull('lat')->get(['lat', 'lng']);

        return $tocke->isEmpty() ? null : [
            [(float) $tocke->min('lat'), (float) $tocke->min('lng')],
            [(float) $tocke->max('lat'), (float) $tocke->max('lng')],
        ];
    }

    public function novaParcela(): void
    {
        $this->cancelParcelaEdit();
        $this->showParcelaForm = true;
    }

    public function ucrtajNaKarti(): void
    {
        $this->resetArkod();
        $this->clearValidation();
        $this->rucnoUnos = true;
        $this->rucniPoligon = null;
        $this->parcelaForm['arkod_broj'] = '';
    }

    /** Povratak na unos broja; kod postojeće ručne parcele tako se upisuje ARKOD kad ga korisnik dobije. */
    public function imamArkodBroj(): void
    {
        $this->clearValidation();
        $this->rucnoUnos = false;
        $this->rucniPoligon = null;
        $this->parcelaForm['arkod_broj'] = '';
    }

    /** Nove točke s karte: površina se izračuna iznova. */
    public function updatedRucniPoligon($tocke): void
    {
        $this->resetErrorBag('rucniPoligon');
        if ($poligon = Poligon::ocisti($tocke)) {
            $this->parcelaForm['povrsina_ha'] = max(0.01, round(Poligon::ha($poligon), 2));
        }
    }

    /** Upisani ARKOD broj: potraži parcelu u javnom ARKOD-u, pokaži je na karti i ponudi službenu površinu. */
    public function updatedParcelaFormArkodBroj($broj): void
    {
        $this->arkodNadjen = null;
        $this->arkodPoruka = '';
        if (blank($broj) || $this->rucnoUnos) {
            return;
        }

        try {
            $this->arkodNadjen = ArkodServis::dohvati((string) $broj);
        } catch (\Throwable $e) {
            report($e);
            $this->arkodPoruka = 'ARKOD servis Agencije trenutno ne odgovara — parcelu možete spremiti i bez provjere.';
            return;
        }

        if (! $this->arkodNadjen) {
            $this->arkodPoruka = 'ARKOD broj nije pronađen u javnom ARKOD-u. Provjerite broj (možete ga spremiti i bez provjere).';
            return;
        }

        if (blank($this->parcelaForm['povrsina_ha'] ?? null)) {
            $this->parcelaForm['povrsina_ha'] = $this->arkodNadjen['povrsina_ha'];
        }
        $this->dispatch('arkod-pregled', poligon: $this->arkodNadjen['poligon']);
    }

    public function preuzmiArkodPovrsinu(): void
    {
        if ($this->arkodNadjen) {
            $this->parcelaForm['povrsina_ha'] = $this->arkodNadjen['povrsina_ha'];
        }
    }

    /** Ikona karte uz parcelu: prikaz parcele u popupu; stare parcele se pri prvom otvaranju dohvate iz ARKOD-a. */
    public function prikaziKartu(int $id): void
    {
        $p = Parcela::where('user_id', auth()->id())->findOrFail($id);

        if (! $p->arkod_poligon && ! $p->rucno_ucrtana) {
            try {
                $arkod = ArkodServis::dohvati($p->arkod_broj);
            } catch (\Throwable $e) {
                report($e);
                $arkod = null;
            }
            if ($arkod) {
                $p->update(Parcela::arkodPolja($arkod));
            }
        }

        $this->dispatch('arkod-karta',
            arkod: $p->arkod_broj,
            poligon: $p->arkod_poligon,
            povrsina: (float) $p->povrsina_ha,
            arkodPovrsina: $p->arkod_povrsina_ha !== null ? (float) $p->arkod_povrsina_ha : null,
            naziv: $p->arkod_naziv,
            vrsta: $p->arkod_vrsta_uporabe,
            rucno: $p->rucno_ucrtana,
        );
    }

    private function resetArkod(): void
    {
        $this->arkodNadjen = null;
        $this->arkodPoruka = '';
    }

    /** Spremanje ručno ucrtane parcele; vraća null ako oblik ili podaci ne valjaju. */
    private function spremiUcrtanu(): ?Parcela
    {
        $this->parcelaForm['arkod_broj'] = trim((string) $this->parcelaForm['arkod_broj']);
        $this->validate([
            'parcelaForm.arkod_broj'  => ['required', 'string', 'max:60', Rule::unique('parcele', 'arkod_broj')->where('user_id', auth()->id())->ignore($this->editParcelaId)],
            'parcelaForm.povrsina_ha' => 'required|numeric|min:0.01',
        ], ['parcelaForm.arkod_broj.unique' => 'Već imate parcelu pod tim nazivom.'], ['parcelaForm.arkod_broj' => 'naziv parcele', 'parcelaForm.povrsina_ha' => 'površina']);

        $poligon = Poligon::ocisti($this->rucniPoligon);
        if (! $poligon) {
            $this->addError('rucniPoligon', 'Ucrtajte parcelu na karti: potrebne su barem tri točke.');
            return null;
        }
        if (Poligon::kriziSe($poligon)) {
            $this->addError('rucniPoligon', 'Rubovi parcele se križaju. Točke treba dodavati redom, uz rub parcele.');
            return null;
        }

        $podaci = array_merge($this->parcelaForm, Parcela::ucrtanaPolja($poligon));
        if ($this->editParcelaId) {
            $parcela = Parcela::where('id', $this->editParcelaId)->where('user_id', auth()->id())->firstOrFail();
            $parcela->update($podaci);

            return $parcela;
        }

        return Parcela::create($podaci + ['user_id' => auth()->id()]);
    }

    public function saveParcela(): void
    {
        $isEdit = (bool) $this->editParcelaId;
        if ($this->rucnoUnos) {
            $parcela = $this->spremiUcrtanu();
            if (! $parcela) {
                return;
            }
            $this->zavrsiSpremanje($parcela, $isEdit);
            return;
        }

        $this->validate([
            'parcelaForm.arkod_broj'  => 'required|string|max:20',
            'parcelaForm.povrsina_ha' => 'required|numeric|min:0.01',
        ]);

        $parcela = null;

        // ARKOD podaci samo ako je pronađena parcela baš za ovaj broj
        $arkod = $this->arkodNadjen && $this->arkodNadjen['id'] === preg_replace('/\D/', '', (string) $this->parcelaForm['arkod_broj'])
            ? Parcela::arkodPolja($this->arkodNadjen) : null;

        if ($this->editParcelaId) {
            $stara = Parcela::where('id', $this->editParcelaId)->where('user_id', auth()->id())->firstOrFail();
            $podaci = ['arkod_broj' => $this->parcelaForm['arkod_broj'], 'povrsina_ha' => $this->parcelaForm['povrsina_ha'], 'rucno_ucrtana' => false];
            if ($arkod) {
                $podaci += $arkod;
            } elseif ($stara->arkod_broj !== $this->parcelaForm['arkod_broj']) {
                $podaci += Parcela::arkodPolja(null);   // promijenjen broj — stari poligon više ne vrijedi
            }
            $stara->update($podaci);
            $parcela = $stara;
        } else {
            $parcela = Parcela::create(array_merge($this->parcelaForm, $arkod ?? [], ['user_id' => auth()->id()]));
        }
        $this->zavrsiSpremanje($parcela, $isEdit);
    }

    private function zavrsiSpremanje(Parcela $parcela, bool $isEdit): void
    {
        $this->resetArkod();
        $this->rucnoUnos = false;
        $this->rucniPoligon = null;

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
                'eppo_naziv' => '',
                'posadjena_povrsina_ha' => $parcela->povrsina_ha,
                'godina' => date('Y'),
            ];
        }
    }

    /** Upisani naziv kulture predloži usjev s Ministarstvova popisa (EPPO), ako još nije odabran. */
    public function updatedKulturaFormNaziv($naziv): void
    {
        if (blank($this->kulturaForm['eppo_naziv'] ?? null)) {
            $this->kulturaForm['eppo_naziv'] = SzbPopisi::usjevZaKulturu($naziv)[0] ?? '';
        }
        // voćke i vinova loza: predloži trajni nasad (korisnik može maknuti kvačicu)
        if (! $this->editKulturaId && Kultura::jeTrajniNaziv($naziv)) {
            $this->kulturaForm['trajni'] = true;
            $this->updatedKulturaFormTrajni(true);
        }
    }

    /** Trajni nasad nema polje Godina: nova kultura vrijedi od tekuće godine i ostaje na parceli svake sljedeće. */
    public function updatedKulturaFormTrajni($trajni): void
    {
        if ($trajni && ! $this->editKulturaId && (int) ($this->kulturaForm['godina'] ?? 0) !== (int) date('Y')) {
            $this->kulturaForm['godina'] = date('Y');
            $this->updatedKulturaFormGodina(date('Y'));
        }
    }

    public function editParcela(int $id): void
    {
        $p = Parcela::where('user_id', auth()->id())->findOrFail($id);
        $this->editParcelaId = $id;
        $this->parcelaForm = ['arkod_broj' => $p->arkod_broj, 'povrsina_ha' => $p->povrsina_ha];
        $this->rucnoUnos = $p->rucno_ucrtana;
        $this->rucniPoligon = $p->rucno_ucrtana ? $p->arkod_poligon : null;
        $this->resetArkod();
        $this->showParcelaForm = false;
        $this->clearValidation();
    }

    public function cancelParcelaEdit(): void
    {
        $this->editParcelaId = null;
        $this->showParcelaForm = false;
        $this->rucnoUnos = false;
        $this->rucniPoligon = null;
        $this->resetArkod();
        $this->parcelaForm = ['arkod_broj' => '', 'povrsina_ha' => ''];
        $this->clearValidation();
    }

    public function deleteParcela(int $id): void
    {
        $parcela = Parcela::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        // Brisanje parcele kaskadno briše kulture i njihovu evidenciju — ne dopuštamo ako zapisa ima
        $kultureIds = Kultura::where('parcela_id', $parcela->id)->pluck('id');
        $imaZapise = \App\Models\Gnojidba::whereIn('kultura_id', $kultureIds)->exists()
            || \App\Models\Prskanje::whereIn('kultura_id', $kultureIds)->exists()
            || \App\Models\Berba::whereIn('kultura_id', $kultureIds)->exists();
        if ($imaZapise) {
            $this->poruka = "Parcela {$parcela->arkod_broj} ima zapise gnojidbe, prskanja ili berbe i ne može se obrisati — evidenciju je obvezno čuvati.";
            return;
        }

        $parcela->delete();
    }

    /** Slobodna površina parcele u godini: površina parcele minus aktivne (nezavršene) kulture te godine. */
    private function slobodnoHa(int $parcelaId, $godina, array $bez = []): float
    {
        $parcela = Parcela::where('user_id', auth()->id())->find($parcelaId);
        if (! $parcela) {
            return 0.0;
        }
        $zauzeto = Kultura::where('parcela_id', $parcelaId)->uGodini($godina)->whereNull('zavrsena_at')
            ->when($bez, fn ($q) => $q->whereNotIn('id', array_filter($bez)))
            ->sum('posadjena_povrsina_ha');

        return max(0.0, round((float) $parcela->povrsina_ha - (float) $zauzeto, 2));
    }

    /** Slobodno na parceli za formu kulture (bez kulture koja se uređuje ili mijenja). */
    public function getSlobodnoProperty(): ?float
    {
        return $this->kulturaParcelaId
            ? $this->slobodnoHa($this->kulturaParcelaId, $this->kulturaForm['godina'] ?: date('Y'), [$this->editKulturaId, $this->zamjenaZaId])
            : null;
    }

    public function openKulturaForm(int $parcelaId): void
    {
        $slobodno = $this->slobodnoHa($parcelaId, date('Y'));
        if ($slobodno < 0.01) {
            $p = Parcela::where('user_id', auth()->id())->find($parcelaId);
            $this->poruka = "Parcela {$p?->arkod_broj} je popunjena (" . number_format((float) $p?->povrsina_ha, 2, ',', '.') . ' ha) za ' . date('Y') . '. Za novu kulturu klikni „Zamijeni kulturu” ili „Završi” uz postojeću, a kod nasada „Iskrči”.';
            $this->showKulturaForm = false;
            return;
        }
        $this->poruka = '';
        $this->kulturaParcelaId = $parcelaId;
        $this->showKulturaForm = true;
        $this->editKulturaId = null;
        $this->zamjenaZaId = null;
        $this->kulturaForm = ['naziv' => '', 'eppo_naziv' => '', 'posadjena_povrsina_ha' => $slobodno, 'godina' => date('Y'),
            'trajni' => $this->parcelaJeNasad($parcelaId)];
        $this->clearValidation();
    }

    /** ARKOD vrsta uporabe vinograd, voćnjak ili maslinik. */
    private function parcelaJeNasad(int $parcelaId): bool
    {
        $vrsta = Parcela::where('user_id', auth()->id())->whereKey($parcelaId)->value('arkod_vrsta_uporabe');

        return in_array($vrsta, ['Vinograd', 'Voćnjak', 'Maslinik'], true);
    }

    /** Postojeću kulturu označi kao trajni nasad. */
    public function oznaciTrajni(int $id): void
    {
        Kultura::where('user_id', auth()->id())->whereKey($id)->update(['trajni' => true]);
    }

    /** Promjena godine u formi nove kulture: ponudi slobodnu površinu za tu godinu. */
    public function updatedKulturaFormGodina($godina): void
    {
        if ($this->showKulturaForm && ! $this->editKulturaId && ! $this->zamjenaZaId && $this->kulturaParcelaId && (int) $godina >= 2000) {
            $this->kulturaForm['posadjena_povrsina_ha'] = $this->slobodnoHa($this->kulturaParcelaId, $godina);
        }
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
        $this->kulturaForm = ['naziv' => '', 'eppo_naziv' => '', 'posadjena_povrsina_ha' => $k->posadjena_povrsina_ha, 'godina' => date('Y')];
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
            'eppo_naziv'            => $k->eppo_naziv ?? '',
            'posadjena_povrsina_ha' => $k->posadjena_povrsina_ha,
            'godina'                => $k->godina,
            'trajni'                => (bool) $k->trajni,
            'sorta'                 => $k->sorta ?? '',
            'godina_sadnje'         => $k->godina_sadnje ?? '',
            'broj_stabala'          => $k->broj_stabala ?? '',
        ];
        $this->showKulturaForm = false;
        $this->clearValidation();
    }

    public function cancelKulturaEdit(): void
    {
        $this->editKulturaId = null;
        $this->kulturaForm = ['naziv' => '', 'eppo_naziv' => '', 'posadjena_povrsina_ha' => '', 'godina' => date('Y')];
        $this->clearValidation();
    }

    public function saveKultura($nova = false): void
    {
        $this->validate([
            'kulturaForm.naziv'                 => 'required|string|max:100',
            'kulturaForm.posadjena_povrsina_ha' => 'required|numeric|min:0.01',
            'kulturaForm.godina'                => 'required|integer|min:2000|max:2100',
            'kulturaForm.eppo_naziv'            => 'nullable|string|max:255',
            'kulturaForm.sorta'                 => 'nullable|string|max:100',
            'kulturaForm.godina_sadnje'         => 'nullable|integer|min:1900|max:2100',
            'kulturaForm.broj_stabala'          => 'nullable|integer|min:0|max:100000000',
        ], [], ['kulturaForm.naziv' => 'kultura', 'kulturaForm.posadjena_povrsina_ha' => 'posađena površina', 'kulturaForm.godina' => 'godina', 'kulturaForm.eppo_naziv' => 'usjev (EPPO)', 'kulturaForm.godina_sadnje' => 'godina sadnje', 'kulturaForm.broj_stabala' => 'broj stabala']);

        // Usjev za evidenciju SZB: s Ministarstvova popisa, ili prepoznat iz naziva kulture
        $eppoNaziv = trim((string) ($this->kulturaForm['eppo_naziv'] ?? ''));
        if ($eppoNaziv !== '' && ! isset(SzbPopisi::eppo()[$eppoNaziv])) {
            $this->addError('kulturaForm.eppo_naziv', 'Odaberite usjev s ponuđenog popisa Ministarstva (ili ostavite prazno).');
            return;
        }
        $usjev = $eppoNaziv !== '' ? [$eppoNaziv, SzbPopisi::eppo()[$eppoNaziv]] : SzbPopisi::usjevZaKulturu($this->kulturaForm['naziv']);

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
            ->uGodini($this->kulturaForm['godina'])
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
            'eppo_naziv' => $usjev[0] ?? null,
            'eppo_kod'   => $usjev[1] ?? null,
        ]);
        $trajni = (bool) ($this->kulturaForm['trajni'] ?? false);
        $data['trajni'] = $trajni;
        foreach (['sorta', 'godina_sadnje', 'broj_stabala'] as $polje) {
            $data[$polje] = $trajni && ! blank($this->kulturaForm[$polje] ?? null) ? $this->kulturaForm[$polje] : null;
        }

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
            // Ostavlja formu otvorenom za sljedeću kulturu s preostalom površinom; ako je parcela puna, zatvori je
            $godina = $this->kulturaForm['godina'];
            $slobodno = $this->slobodnoHa($this->kulturaParcelaId, $godina);
            if ($slobodno < 0.01) {
                $this->showKulturaForm = false;
                $this->poruka = 'Parcela je sada popunjena za ' . $godina . '.';
            } else {
                $this->kulturaForm = ['naziv' => '', 'eppo_naziv' => '', 'posadjena_povrsina_ha' => $slobodno, 'godina' => $godina];
            }
        } else {
            $this->showKulturaForm = false;
        }
    }

    public function deleteKultura(int $id): void
    {
        $k = Kultura::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        // Brisanje bi kaskadno obrisalo i evidenciju gnojidbe/prskanja, koju je obvezno čuvati
        if ($k->imaZapise()) {
            $this->poruka = "Kultura „{$k->naziv}\" ima zapise gnojidbe, prskanja ili berbe i ne može se obrisati. Označi je završenom ili je zamijeni novom kulturom.";
            return;
        }

        $k->delete();
    }

    // ---- prinos (za dobit po hektaru) ----

    public ?int $prinosId = null;
    public array $prinosForm = [];

    public function otvoriPrinos(int $id): void
    {
        $k = Kultura::where('user_id', auth()->id())->findOrFail($id);
        $this->prinosId = $k->id;
        $this->resetErrorBag();
        if ($k->trajni) {
            $this->prinosForm = $this->nasadGodina($k->id, (int) date('Y'));

            return;
        }
        $this->prinosForm = [
            'zetva_datum' => $k->zetva_datum?->format('Y-m-d') ?? date('Y-m-d'),
            'prinos_t_ha' => $k->prinos_t_ha, 'cijena_t' => $k->cijena_t, 'potpora_ha' => $k->potpora_ha,
            'trosak_sjeme' => $k->trosak_sjeme, 'trosak_ostalo' => $k->trosak_ostalo,
        ];
        $this->resetErrorBag();
    }

    /** Potpore i ostali troškovi trajnog nasada za godinu. */
    private function nasadGodina(int $kulturaId, int $godina): array
    {
        $g = \App\Models\NasadGodina::where('kultura_id', $kulturaId)->where('godina', $godina)->first();

        return ['godina' => $godina, 'potpora_ha' => $g?->potpora_ha, 'trosak_ostalo' => $g?->trosak_ostalo];
    }

    public function updatedPrinosFormGodina($godina): void
    {
        if ($this->prinosId && (int) $godina >= 1900 && (int) $godina <= 2100) {
            $this->prinosForm = $this->nasadGodina($this->prinosId, (int) $godina);
        }
    }

    public function spremiPrinos(): void
    {
        $nasad = Kultura::where('user_id', auth()->id())->where('trajni', true)->find($this->prinosId);
        if ($nasad) {
            $this->validate([
                'prinosForm.godina' => 'required|integer|min:1900|max:2100',
                'prinosForm.potpora_ha' => 'nullable|numeric|min:0|max:100000',
                'prinosForm.trosak_ostalo' => 'nullable|numeric|min:0|max:10000000',
            ], [], ['prinosForm.godina' => 'godina', 'prinosForm.potpora_ha' => 'potpore', 'prinosForm.trosak_ostalo' => 'ostali troškovi']);
            $potpora = blank($this->prinosForm['potpora_ha'] ?? null) ? null : $this->prinosForm['potpora_ha'];
            $ostalo = blank($this->prinosForm['trosak_ostalo'] ?? null) ? null : $this->prinosForm['trosak_ostalo'];
            \App\Models\NasadGodina::updateOrCreate(['kultura_id' => $nasad->id, 'godina' => (int) $this->prinosForm['godina']],
                ['potpora_ha' => $potpora, 'trosak_ostalo' => $ostalo]);
            $this->prinosId = null;
            $this->prinosForm = [];

            return;
        }
        $this->validate([
            'prinosForm.zetva_datum' => 'nullable|date',
            'prinosForm.prinos_t_ha' => 'nullable|numeric|min:0|max:500',
            'prinosForm.cijena_t' => 'nullable|numeric|min:0|max:100000',
            'prinosForm.potpora_ha' => 'nullable|numeric|min:0|max:100000',
            'prinosForm.trosak_sjeme' => 'nullable|numeric|min:0|max:10000000',
            'prinosForm.trosak_ostalo' => 'nullable|numeric|min:0|max:10000000',
        ], [], ['prinosForm.prinos_t_ha' => 'prinos', 'prinosForm.cijena_t' => 'cijena', 'prinosForm.potpora_ha' => 'potpore']);
        $podaci = array_map(fn ($v) => blank($v) ? null : $v, $this->prinosForm);
        Kultura::where('user_id', auth()->id())->whereKey($this->prinosId)->update($podaci);
        $this->prinosId = null;
        $this->prinosForm = [];
    }

    public function odustaniPrinos(): void
    {
        $this->prinosId = null;
        $this->prinosForm = [];
        $this->resetErrorBag();
    }

    public function render() { return view('livewire.parcela-manager'); }
}
