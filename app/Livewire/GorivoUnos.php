<?php

namespace App\Livewire;

use App\Models\GorivoDan;
use App\Support\Gorivo;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Gorivo za dan: popup nakon prvog prskanja/gnojidbe dana, traka "unesi kasnije", popis po danima (na Troškovima).
 * Jedan unos po danu; poslovi tog dana su po defaultu uključeni, kvačica služi samo za izuzetke.
 */
class GorivoUnos extends Component
{
    /** Na Troškovima: popis unosa goriva za godinu i gumb za novi unos. */
    public bool $popis = false;

    public bool $otvoren = false;
    public string $datum = '';
    public ?int $danId = null;
    /** Ključevi uključenih poslova ("prskanje:12"). */
    public array $ukljuceni = [];
    public $litara = '';
    public $cijena = '';
    public $iznos = '';
    public string $napomena = '';

    /** Kratka poruka (toast) nakon spremanja ili kad je posao samo uključen u već upisano gorivo. */
    public string $poruka = '';
    public int $porukaBroj = 0;

    public int $godina;

    public function mount(bool $popis = false): void
    {
        $this->popis = $popis;
        $this->godina = (int) now()->year;
    }

    private function savjetnik(): bool
    {
        return (bool) request()->attributes->get('savjetnik');
    }

    private function javi(string $tekst): void
    {
        $this->poruka = $tekst;
        $this->porukaBroj++;
    }

    #[On('posao-spremljen')]
    public function posaoSpremljen(string $datum): void
    {
        $user = auth()->user();
        if (! $user->prati_gorivo) {
            return;
        }
        $datum = Carbon::parse($datum)->toDateString();
        if ($dan = GorivoDan::where('user_id', $user->id)->whereDate('datum', $datum)->first()) {
            $poslovi = Gorivo::posloviDana($user->id, $datum)->reject(fn ($p) => in_array($p['kljuc'], $dan->izuzeto ?? [], true));
            $this->javi('Uključeno u gorivo za ' . Carbon::parse($datum)->format('d.m.Y.') . ' (sad ' . $poslovi->count() . ' '
                . ($poslovi->count() === 1 ? 'posao' : 'poslova') . ', ' . number_format((float) $poslovi->sum('ha'), 2, ',', '.') . ' ha).');

            return;
        }
        if ($this->odgodjeno($user) === $datum) {
            return;     // već je rekao "kasnije" za taj dan: bez popupa, traka ostaje
        }
        $this->otvori($datum);
    }

    private function odgodjeno($user): ?string
    {
        return $user->gorivo_kasnije ? Carbon::parse($user->gorivo_kasnije)->toDateString() : null;
    }

    #[On('gorivo-otvori')]
    public function otvori(?string $datum = null): void
    {
        $this->resetErrorBag();
        $this->datum = $datum ? Carbon::parse($datum)->toDateString() : now()->toDateString();
        $this->ucitaj();
        $this->otvoren = true;
    }

    private function ucitaj(): void
    {
        $uid = auth()->id();
        $dan = GorivoDan::where('user_id', $uid)->whereDate('datum', $this->datum)->first();
        $kljucevi = Gorivo::posloviDana($uid, $this->datum)->pluck('kljuc')->all();
        $this->danId = $dan?->id;
        $this->ukljuceni = array_values(array_diff($kljucevi, $dan?->izuzeto ?? []));
        $this->litara = $dan?->litara !== null && $dan ? (float) $dan->litara : '';
        $this->cijena = $dan ? ($dan->cijena_l !== null ? (float) $dan->cijena_l : '') : (Gorivo::zadnjaCijena($uid) ?? '');
        $this->iznos = $dan ? (float) $dan->iznos : '';
        $this->napomena = $dan?->napomena ?? '';
    }

    /** Promjena datuma u popupu: učitaj unos i poslove tog dana. */
    public function updatedDatum(): void
    {
        if ($this->datum) {
            $this->ucitaj();
        }
    }

    /** Iznos iz litara × cijene, ili ručno upisan iznos. */
    public function getIzracunProperty(): ?float
    {
        $l = is_numeric($this->litara) ? (float) $this->litara : null;
        $c = is_numeric($this->cijena) ? (float) $this->cijena : null;
        if ($l !== null && $c !== null) {
            return round($l * $c, 2);
        }

        return is_numeric($this->iznos) ? round((float) $this->iznos, 2) : null;
    }

    public function spremi(): void
    {
        $this->validate([
            'datum' => 'required|date|before_or_equal:' . now()->addDay()->toDateString(),
            'litara' => 'nullable|numeric|gt:0|max:100000',
            'cijena' => 'nullable|numeric|gt:0|max:100',
            'iznos' => 'nullable|numeric|gt:0|max:10000000',
            'napomena' => 'nullable|string|max:300',
        ], [], ['litara' => 'litre', 'cijena' => 'cijena po litri', 'iznos' => 'iznos']);

        $iznos = $this->izracun;
        if ($iznos === null) {
            $this->addError('litara', 'Upišite litre i cijenu po litri, ili samo iznos u eurima.');

            return;
        }

        $uid = auth()->id();
        $kljucevi = Gorivo::posloviDana($uid, $this->datum)->pluck('kljuc')->all();
        $dan = GorivoDan::where('user_id', $uid)->whereDate('datum', $this->datum)->first()
            ?? new GorivoDan(['user_id' => $uid, 'datum' => $this->datum]);
        $dan->fill([
            'litara' => is_numeric($this->litara) ? $this->litara : null,
            'cijena_l' => is_numeric($this->cijena) ? $this->cijena : null,
            'iznos' => $iznos,
            'izuzeto' => array_values(array_diff($kljucevi, $this->ukljuceni)) ?: null,
            'napomena' => $this->napomena !== '' ? $this->napomena : null,
        ])->save();

        $user = auth()->user();
        if ($this->odgodjeno($user) === $this->datum) {
            $user->forceFill(['gorivo_kasnije' => null])->save();
        }
        $this->otvoren = false;
        $this->javi('Gorivo za ' . Carbon::parse($this->datum)->format('d.m.Y.') . ' je spremljeno.');
        $this->dispatch('troskovi-promijenjeni');
    }

    /** "Unijet ću na kraju dana": taj dan bez popupa, traka podsjeća. */
    public function kasnije(): void
    {
        if (! $this->danId && $this->datum) {
            auth()->user()->forceFill(['gorivo_kasnije' => $this->datum])->save();
        }
        $this->otvoren = false;
    }

    public function obrisi(): void
    {
        GorivoDan::where('user_id', auth()->id())->whereKey($this->danId)->delete();
        $this->otvoren = false;
        $this->javi('Gorivo za ' . Carbon::parse($this->datum)->format('d.m.Y.') . ' je obrisano.');
        $this->dispatch('troskovi-promijenjeni');
    }

    /** Korisnik ne želi pratiti gorivo: bez popupa i trake; uključuje se opet u Profilu. */
    public function nePratim(): void
    {
        if ($this->savjetnik()) {
            return;
        }
        auth()->user()->forceFill(['prati_gorivo' => false, 'gorivo_kasnije' => null])->save();
        $this->otvoren = false;
        $this->javi('Gorivo se više ne prati. Uključiti ga možete u Profilu.');
    }

    public function sakrijTraku(): void
    {
        auth()->user()->forceFill(['gorivo_kasnije' => null])->save();
    }

    #[On('troskovi-promijenjeni')]
    public function osvjezi(): void
    {
    }

    public function render()
    {
        $user = auth()->user();
        $uid = $user->id;

        $poslovi = $this->otvoren && $this->datum ? Gorivo::posloviDana($uid, $this->datum) : collect();
        $raspodjela = $this->otvoren && $this->izracun !== null
            ? Gorivo::rasporedi($this->izracun, $poslovi, array_values(array_diff($poslovi->pluck('kljuc')->all(), $this->ukljuceni)))
            : [];

        // traka: dan odgođen s "kasnije", a gorivo još nije upisano
        $traka = null;
        if ($user->prati_gorivo && ($odg = $this->odgodjeno($user)) && ! GorivoDan::where('user_id', $uid)->whereDate('datum', $odg)->exists()) {
            $traka = $odg;
        }

        $dani = collect();
        if ($this->popis) {
            $dani = GorivoDan::where('user_id', $uid)->whereYear('datum', $this->godina)->orderByDesc('datum')->get();
            $sviPoslovi = $dani->isEmpty() ? collect() : Gorivo::poslovi($uid, $dani->min('datum')->format('Y-m-d'), $dani->max('datum')->format('Y-m-d'));
            $dani->each(function ($d) use ($sviPoslovi) {
                $p = $sviPoslovi->get($d->datum->format('Y-m-d'), collect())->reject(fn ($p) => in_array($p['kljuc'], $d->izuzeto ?? [], true));
                $d->broj_poslova = $p->count();
                $d->ha = (float) $p->sum('ha');
            });
        }
        $godine = collect(range((int) now()->year, (int) now()->year - 4));

        return view('livewire.gorivo-unos', [
            'prati' => (bool) $user->prati_gorivo, 'poslovi' => $poslovi, 'raspodjela' => $raspodjela, 'traka' => $traka,
            'dani' => $dani, 'godine' => $godine, 'savjetnik' => $this->savjetnik(),
        ]);
    }
}
