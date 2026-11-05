<?php

namespace App\Livewire;

use App\Models\Gnojidba;
use App\Models\Prskanje;
use App\Models\Skladiste as SkladisteModel;
use App\Models\SkladisteUlaz;
use App\Models\SzbSredstvo;
use App\Support\Gnojiva;
use App\Support\Skladiste;
use Illuminate\Support\Str;
use Livewire\Component;

class SkladisteManager extends Component
{
    /** Skladište koje se prikazuje (kartica). */
    public ?int $aktivno = null;

    /** Forma skladišta (novo ili uređivanje). */
    public bool $showSkladisteForm = false;
    public ?int $skladisteEditId = null;
    public array $sf = ['naziv' => '', 'lokacija' => ''];

    /** Forma nabave. */
    public bool $showForm = false;
    public ?int $editingId = null;
    public array $form = [];

    /** Otvoreni artikl (md5 ključa "skladiste|vrsta|NAZIV") s popisom ulaza i potrošnje. */
    public ?string $otvoren = null;
    /** Stvarno stanje upisano pri inventuri, po artiklu (md5 ključa). */
    public array $inventura = [];
    /** Prijenos u drugo skladište, po artiklu: [kolicina, skladiste_id]. */
    public array $prijenos = [];
    public string $filterVrsta = '';
    public string $poruka = '';

    protected array $validationAttributes = [
        'form.naziv' => 'naziv', 'form.datum' => 'datum', 'form.kolicina' => 'količina', 'form.skladiste_id' => 'skladište',
        'form.jedinica' => 'jedinica', 'form.cijena' => 'cijena', 'form.dobavljac' => 'dobavljač', 'sf.naziv' => 'naziv skladišta',
    ];

    public function mount(): void
    {
        $this->aktivno = Skladiste::zadanoId(auth()->user());
        $this->form = $this->praznaForma();
        $this->showSkladisteForm = ! $this->aktivno;   // nema skladišta: odmah forma za prvo
    }

    private function praznaForma(string $vrsta = 'szb', string $naziv = '', ?int $skladisteId = null): array
    {
        return [
            'skladiste_id' => $skladisteId ?? $this->aktivno, 'vrsta' => $vrsta, 'naziv' => $naziv, 'datum' => date('Y-m-d'), 'kolicina' => '',
            'jedinica' => $vrsta === 'gnojivo' ? 'kg' : 'L', 'cijena' => '', 'dobavljac' => '', 'napomena' => '',
        ];
    }

    private function mojeSkladiste($id): SkladisteModel
    {
        return SkladisteModel::where('user_id', auth()->id())->findOrFail((int) $id);
    }

    // ---- skladišta ----

    public function odaberi(int $id): void
    {
        $this->aktivno = $this->mojeSkladiste($id)->id;
        $this->otvoren = null;
        $this->poruka = '';
    }

    public function novoSkladiste(): void
    {
        $this->skladisteEditId = null;
        $this->sf = ['naziv' => '', 'lokacija' => ''];
        $this->showSkladisteForm = true;
        $this->resetErrorBag();
    }

    public function urediSkladiste(int $id): void
    {
        $s = $this->mojeSkladiste($id);
        $this->skladisteEditId = $s->id;
        $this->sf = ['naziv' => $s->naziv, 'lokacija' => $s->lokacija ?? ''];
        $this->showSkladisteForm = true;
        $this->resetErrorBag();
    }

    public function spremiSkladiste(): void
    {
        $this->validate(['sf.naziv' => 'required|string|max:100', 'sf.lokacija' => 'nullable|string|max:150']);
        $podaci = ['naziv' => trim($this->sf['naziv']), 'lokacija' => blank($this->sf['lokacija']) ? null : trim($this->sf['lokacija'])];

        if ($this->skladisteEditId) {
            $this->mojeSkladiste($this->skladisteEditId)->update($podaci);
        } else {
            $prvo = ! SkladisteModel::where('user_id', auth()->id())->exists();
            $s = SkladisteModel::create($podaci + ['user_id' => auth()->id(), 'zadano' => $prvo]);
            $this->aktivno = $s->id;
        }
        $this->showSkladisteForm = false;
        $this->skladisteEditId = null;
    }

    public function odustaniSkladiste(): void
    {
        $this->showSkladisteForm = false;
        $this->skladisteEditId = null;
        $this->resetErrorBag();
    }

    /** Zadano skladište: iz njega troše zapisi prskanja i gnojidbe kojima skladište nije izabrano. */
    public function postaviZadano(int $id): void
    {
        $s = $this->mojeSkladiste($id);
        SkladisteModel::where('user_id', auth()->id())->update(['zadano' => false]);
        $s->update(['zadano' => true]);
    }

    /** Briše se samo prazno skladište (bez ulaza); zapisi koji su iz njega trošili prelaze na zadano. */
    public function obrisiSkladiste(int $id): void
    {
        $s = $this->mojeSkladiste($id);
        if ($s->ulazi()->exists()) {
            $this->poruka = 'Skladište "' . $s->naziv . '" ima ulaze. Prvo ih obrišite ili prenesite robu u drugo skladište.';
            return;
        }
        $bioZadano = $s->zadano;
        $s->delete();
        if ($bioZadano) {
            SkladisteModel::where('user_id', auth()->id())->orderBy('id')->first()?->update(['zadano' => true]);
        }
        $this->aktivno = Skladiste::zadanoId(auth()->user());
        $this->showSkladisteForm = ! $this->aktivno;
        $this->poruka = '';
    }

    // ---- nabava ----

    /** Artikl po md5 ključa (ključ s nazivom ne ide u wire:click ni u ime polja). */
    private function artiklPoHashu(?string $h): ?array
    {
        return $h ? $this->artikli->first(fn ($a) => md5($a['kljuc']) === $h) : null;
    }

    public function novaNabava(string $vrsta = 'szb', ?string $h = null): void
    {
        $artikl = $this->artiklPoHashu($h);
        $this->editingId = null;
        $this->form = $this->praznaForma($artikl['vrsta'] ?? $vrsta, $artikl['naziv'] ?? '', $artikl['skladiste_id'] ?? null);
        if ($artikl) {
            $this->form['jedinica'] = $artikl['jedinica'];
        }
        $this->showForm = true;
        $this->resetErrorBag();
    }

    /** Naziv s popisa: prepozna se vrsta (sredstvo iz registra ili gnojivo) i preuzme jedinica postojećeg artikla. */
    public function updatedFormNaziv($naziv): void
    {
        if (blank($naziv)) {
            return;
        }
        $kljuc = Skladiste::kljuc($naziv);
        $postojeci = $this->artikli->sortBy(fn ($a) => $a['skladiste_id'] == ($this->form['skladiste_id'] ?? null) ? 0 : 1)
            ->first(fn ($a) => Skladiste::kljuc($a['naziv']) === $kljuc);
        if ($postojeci) {
            $this->form['vrsta'] = $postojeci['vrsta'];
            $this->form['jedinica'] = $postojeci['jedinica'];
            return;
        }
        if (collect(array_keys(Gnojiva::POPIS))->contains(fn ($n) => Skladiste::kljuc($n) === $kljuc)) {
            $this->form['vrsta'] = 'gnojivo';
            $this->form['jedinica'] = 'kg';
        } elseif (SzbSredstvo::poNazivu($naziv)) {
            $this->form['vrsta'] = 'szb';
            if ($this->form['jedinica'] === 't') {
                $this->form['jedinica'] = 'L';
            }
        }
    }

    public function spremi(): void
    {
        $this->validate([
            'form.skladiste_id' => 'required|integer',
            'form.vrsta'     => 'required|in:szb,gnojivo',
            'form.naziv'     => 'required|string|max:200',
            'form.datum'     => 'required|date',
            'form.kolicina'  => 'required|numeric|min:0.001|max:9999999',
            'form.jedinica'  => 'required|in:L,kg,t',
            'form.cijena'    => 'nullable|numeric|min:0|max:9999999',
            'form.dobavljac' => 'nullable|string|max:150',
            'form.napomena'  => 'nullable|string|max:255',
        ]);

        $f = $this->form;
        $skladiste = $this->mojeSkladiste($f['skladiste_id']);
        $kolicina = (float) $f['kolicina'];
        $jedinica = $f['jedinica'];
        if ($jedinica === 't') {   // tone se spremaju kao kg
            $kolicina *= 1000;
            $jedinica = 'kg';
        }
        $podaci = [
            'skladiste_id' => $skladiste->id,
            'vrsta' => $f['vrsta'], 'naziv' => trim(preg_replace('/\s+/u', ' ', $f['naziv'])), 'datum' => $f['datum'],
            'kolicina' => $kolicina, 'jedinica' => $jedinica,
            'cijena' => blank($f['cijena']) ? null : $f['cijena'],
            'dobavljac' => blank($f['dobavljac']) ? null : trim($f['dobavljac']),
            'napomena' => blank($f['napomena']) ? null : trim($f['napomena']),
        ];

        if ($this->editingId) {
            SkladisteUlaz::where('user_id', auth()->id())->whereNull('prijenos_par')->whereKey($this->editingId)->update($podaci);
        } else {
            SkladisteUlaz::create($podaci + ['user_id' => auth()->id(), 'korekcija' => false]);
        }

        $this->aktivno = $skladiste->id;
        $this->otvoren = md5($skladiste->id . '|' . $f['vrsta'] . '|' . Skladiste::kljuc($podaci['naziv']));
        $this->odustani();
    }

    public function uredi(int $id): void
    {
        $u = SkladisteUlaz::where('user_id', auth()->id())->where('korekcija', false)->whereNull('prijenos_par')->findOrFail($id);
        $this->editingId = $u->id;
        $this->form = [
            'skladiste_id' => $u->skladiste_id, 'vrsta' => $u->vrsta, 'naziv' => $u->naziv, 'datum' => $u->datum->format('Y-m-d'),
            'kolicina' => (float) $u->kolicina, 'jedinica' => $u->jedinica,
            'cijena' => $u->cijena, 'dobavljac' => $u->dobavljac, 'napomena' => $u->napomena,
        ];
        $this->showForm = true;
        $this->resetErrorBag();
    }

    /** Briše ulaz; prijenos se briše u oba skladišta. */
    public function obrisi(int $id): void
    {
        $u = SkladisteUlaz::where('user_id', auth()->id())->find($id);
        if (! $u) {
            return;
        }
        if ($u->prijenos_par) {
            SkladisteUlaz::where('user_id', auth()->id())->where('prijenos_par', $u->prijenos_par)->delete();
        } else {
            $u->delete();
        }
    }

    public function odustani(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->form = $this->praznaForma($this->form['vrsta'] ?? 'szb');
        $this->resetErrorBag();
    }

    public function otvori(string $h): void
    {
        $this->otvoren = $this->otvoren === $h ? null : $h;
    }

    /** Inventura: upisano stvarno stanje → korekcija za razliku prema izračunatom stanju. */
    public function spremiInventuru(string $h): void
    {
        $artikl = $this->artiklPoHashu($h);
        $stvarno = str_replace(',', '.', (string) ($this->inventura[$h] ?? ''));
        if (! $artikl || ! is_numeric($stvarno) || (float) $stvarno < 0) {
            $this->addError('inventura.' . $h, 'Upišite stvarnu količinu na skladištu (0 ili više).');
            return;
        }
        $razlika = round((float) $stvarno - $artikl['stanje'], 3);
        if (abs($razlika) >= 0.001) {
            SkladisteUlaz::create([
                'user_id' => auth()->id(), 'skladiste_id' => $artikl['skladiste_id'], 'vrsta' => $artikl['vrsta'], 'naziv' => $artikl['naziv'],
                'datum' => date('Y-m-d'), 'kolicina' => $razlika, 'jedinica' => $artikl['jedinica'], 'korekcija' => true,
                'napomena' => 'Inventura: stvarno stanje ' . Skladiste::broj($stvarno, 3) . ' ' . $artikl['jedinica'],
            ]);
        }
        unset($this->inventura[$h]);
    }

    /** Prijenos robe u drugo skladište: izlaz iz ovog i ulaz u drugo (s prosječnom cijenom), povezani kao par. */
    public function prenesi(string $h): void
    {
        $artikl = $this->artiklPoHashu($h);
        $kolicina = (float) str_replace(',', '.', (string) ($this->prijenos[$h]['kolicina'] ?? ''));
        $ciljId = (int) ($this->prijenos[$h]['skladiste_id'] ?? 0);
        $cilj = SkladisteModel::where('user_id', auth()->id())->find($ciljId);
        if (! $artikl || $kolicina <= 0 || ! $cilj || $cilj->id === $artikl['skladiste_id']) {
            $this->addError('prijenos.' . $h, 'Upišite količinu veću od 0 i odaberite drugo skladište.');
            return;
        }
        $par = (string) Str::uuid();
        $zajednicko = ['user_id' => auth()->id(), 'vrsta' => $artikl['vrsta'], 'naziv' => $artikl['naziv'], 'datum' => date('Y-m-d'),
                       'jedinica' => $artikl['jedinica'], 'prijenos_par' => $par];
        SkladisteUlaz::create($zajednicko + ['skladiste_id' => $artikl['skladiste_id'], 'kolicina' => -$kolicina, 'korekcija' => true,
            'napomena' => 'Prijenos u: ' . $cilj->naziv]);
        SkladisteUlaz::create($zajednicko + ['skladiste_id' => $cilj->id, 'kolicina' => $kolicina, 'korekcija' => false,
            'cijena' => $artikl['cijena_po_jedinici'] !== null ? round($kolicina * $artikl['cijena_po_jedinici'], 2) : null,
            'napomena' => 'Prijenos iz: ' . $artikl['skladiste']]);
        unset($this->prijenos[$h]);
    }

    public function getSkladistaProperty()
    {
        return Skladiste::skladista(auth()->user());
    }

    public function getArtikliProperty()
    {
        return Skladiste::stanje(auth()->user(), true);
    }

    /** Prijedlozi naziva: sredstva iz registra i vlastite evidencije, odnosno gnojiva s popisa i iz evidencije. */
    public function getPrijedloziProperty(): array
    {
        $uid = auth()->id();
        $jedinstveno = fn ($c) => $c->filter()->map(fn ($n) => trim($n))->unique(fn ($n) => mb_strtoupper($n))->values();

        return [
            'szb' => $jedinstveno(Prskanje::where('user_id', $uid)->distinct()->pluck('trgovacki_naziv_sredstva')
                ->merge(SzbSredstvo::where('aktivno', true)->orderBy('naziv')->pluck('naziv'))),
            'gnojivo' => $jedinstveno(Gnojidba::where('user_id', $uid)->distinct()->pluck('tip_gnojiva')
                ->merge(array_keys(Gnojiva::POPIS))),
        ];
    }

    public function render()
    {
        return view('livewire.skladiste-manager');
    }
}
