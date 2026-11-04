<?php

namespace App\Livewire;

use App\Models\Gnojidba;
use App\Models\Prskanje;
use App\Models\SkladisteUlaz;
use App\Models\SzbSredstvo;
use App\Support\Gnojiva;
use App\Support\Skladiste;
use Livewire\Component;

class SkladisteManager extends Component
{
    public bool $showForm = false;
    public ?int $editingId = null;
    public array $form = [];
    /** Otvoreni artikl (md5 ključa "vrsta|NAZIV") s popisom ulaza i potrošnje. */
    public ?string $otvoren = null;
    /** Stvarno stanje upisano pri inventuri, po artiklu (md5 ključa). */
    public array $inventura = [];
    public string $filterVrsta = '';

    protected array $validationAttributes = [
        'form.naziv' => 'naziv', 'form.datum' => 'datum', 'form.kolicina' => 'količina',
        'form.jedinica' => 'jedinica', 'form.cijena' => 'cijena', 'form.dobavljac' => 'dobavljač',
    ];

    public function mount(): void
    {
        $this->form = $this->praznaForma();
    }

    private function praznaForma(string $vrsta = 'szb', string $naziv = ''): array
    {
        return [
            'vrsta' => $vrsta, 'naziv' => $naziv, 'datum' => date('Y-m-d'), 'kolicina' => '',
            'jedinica' => $vrsta === 'gnojivo' ? 'kg' : 'L', 'cijena' => '', 'dobavljac' => '', 'napomena' => '',
        ];
    }

    public function ukljuci(): void
    {
        auth()->user()->forceFill(['koristi_skladiste' => true])->save();
    }

    /** Artikl po md5 ključa (ključ s nazivom ne ide u wire:click ni u ime polja). */
    private function artiklPoHashu(?string $h): ?array
    {
        return $h ? $this->artikli->first(fn ($a) => md5($a['kljuc']) === $h) : null;
    }

    public function novaNabava(string $vrsta = 'szb', ?string $h = null): void
    {
        $artikl = $this->artiklPoHashu($h);
        $this->editingId = null;
        $this->form = $this->praznaForma($artikl['vrsta'] ?? $vrsta, $artikl['naziv'] ?? '');
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
        $postojeci = $this->artikli->first(fn ($a) => Skladiste::kljuc($a['naziv']) === $kljuc);
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
        $kolicina = (float) $f['kolicina'];
        $jedinica = $f['jedinica'];
        if ($jedinica === 't') {   // tone se spremaju kao kg
            $kolicina *= 1000;
            $jedinica = 'kg';
        }
        $podaci = [
            'vrsta' => $f['vrsta'], 'naziv' => trim(preg_replace('/\s+/u', ' ', $f['naziv'])), 'datum' => $f['datum'],
            'kolicina' => $kolicina, 'jedinica' => $jedinica,
            'cijena' => blank($f['cijena']) ? null : $f['cijena'],
            'dobavljac' => blank($f['dobavljac']) ? null : trim($f['dobavljac']),
            'napomena' => blank($f['napomena']) ? null : trim($f['napomena']),
        ];

        if ($this->editingId) {
            SkladisteUlaz::where('user_id', auth()->id())->whereKey($this->editingId)->update($podaci);
        } else {
            SkladisteUlaz::create($podaci + ['user_id' => auth()->id(), 'korekcija' => false]);
        }

        $this->otvoren = md5($f['vrsta'] . '|' . Skladiste::kljuc($podaci['naziv']));
        $this->odustani();
    }

    public function uredi(int $id): void
    {
        $u = SkladisteUlaz::where('user_id', auth()->id())->where('korekcija', false)->findOrFail($id);
        $this->editingId = $u->id;
        $this->form = [
            'vrsta' => $u->vrsta, 'naziv' => $u->naziv, 'datum' => $u->datum->format('Y-m-d'),
            'kolicina' => (float) $u->kolicina, 'jedinica' => $u->jedinica,
            'cijena' => $u->cijena, 'dobavljac' => $u->dobavljac, 'napomena' => $u->napomena,
        ];
        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function obrisi(int $id): void
    {
        SkladisteUlaz::where('user_id', auth()->id())->whereKey($id)->delete();
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
                'user_id' => auth()->id(), 'vrsta' => $artikl['vrsta'], 'naziv' => $artikl['naziv'], 'datum' => date('Y-m-d'),
                'kolicina' => $razlika, 'jedinica' => $artikl['jedinica'], 'korekcija' => true,
                'napomena' => 'Inventura: stvarno stanje ' . Skladiste::broj($stvarno, 3) . ' ' . $artikl['jedinica'],
            ]);
        }
        unset($this->inventura[$h]);
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
