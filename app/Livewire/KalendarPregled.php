<?php

namespace App\Livewire;

use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\PlaniraniRad;
use App\Support\Kalendar;
use App\Support\Prognoza;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

class KalendarPregled extends Component
{
    #[Url(as: 'mjesec', history: true)]
    public string $mjesec = '';

    #[Url(as: 'parcela', except: '')]
    public string $parcela = '';

    /** Uključene skupine (prazno = sve). */
    public array $grupe = [];

    /** Odabrani dan za popis i planiranje. */
    public string $dan = '';

    public bool $showPlan = false;
    public ?int $planEditId = null;
    public array $plan = [];

    protected array $validationAttributes = [
        'plan.datum' => 'datum', 'plan.vrsta' => 'vrsta posla', 'plan.kultura_id' => 'kultura', 'plan.opis' => 'opis', 'plan.napomena' => 'napomena',
    ];

    public function mount(): void
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $this->mjesec)) {
            $this->mjesec = now()->format('Y-m');
        }
        $this->grupe = array_keys(Kalendar::GRUPE);
        $this->dan = str_starts_with(now()->format('Y-m-d'), $this->mjesec) ? now()->format('Y-m-d') : '';
        $this->plan = $this->prazanPlan();
    }

    private function prazanPlan(?string $datum = null): array
    {
        return ['datum' => $datum ?: ($this->dan ?: now()->format('Y-m-d')), 'vrsta' => 'prskanje', 'kultura_id' => '', 'opis' => '', 'napomena' => ''];
    }

    private function prvi(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->mjesec . '-01')->startOfDay();
    }

    public function pomakni(int $za): void
    {
        $this->mjesec = $this->prvi()->addMonthsNoOverflow($za)->format('Y-m');
        $this->dan = '';
    }

    public function danas(): void
    {
        $this->mjesec = now()->format('Y-m');
        $this->dan = now()->format('Y-m-d');
    }

    public function odaberi(string $datum): void
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $datum)) {
            $this->dan = $datum;
            if (! str_starts_with($datum, $this->mjesec)) {
                $this->mjesec = substr($datum, 0, 7);
            }
        }
    }

    public function prebaciGrupu(string $g): void
    {
        if (! isset(Kalendar::GRUPE[$g])) {
            return;
        }
        $this->grupe = in_array($g, $this->grupe, true) ? array_values(array_diff($this->grupe, [$g])) : [...$this->grupe, $g];
    }

    public function sveGrupe(): void
    {
        $this->grupe = array_keys(Kalendar::GRUPE);
    }

    public function noviPlan(?string $datum = null): void
    {
        $this->planEditId = null;
        $this->plan = $this->prazanPlan($datum);
        $this->showPlan = true;
        $this->resetErrorBag();
    }

    public function urediPlan(int $id): void
    {
        $p = PlaniraniRad::where('user_id', auth()->id())->otvoreni()->findOrFail($id);
        $this->planEditId = $p->id;
        $this->plan = ['datum' => $p->datum->format('Y-m-d'), 'vrsta' => $p->vrsta, 'kultura_id' => $p->kultura_id ?? '', 'opis' => $p->opis ?? '', 'napomena' => $p->napomena ?? ''];
        $this->showPlan = true;
        $this->resetErrorBag();
    }

    public function odustani(): void
    {
        $this->showPlan = false;
        $this->planEditId = null;
        $this->resetErrorBag();
    }

    public function spremiPlan(): void
    {
        $uid = auth()->id();
        $this->validate([
            'plan.datum' => 'required|date|after_or_equal:2000-01-01|before_or_equal:2100-12-31',
            'plan.vrsta' => ['required', Rule::in(array_keys(PlaniraniRad::VRSTE))],
            'plan.kultura_id' => [in_array($this->plan['vrsta'] ?? '', ['prskanje', 'gnojidba', 'berba'], true) ? 'required' : 'nullable',
                Rule::exists('kulture', 'id')->where('user_id', $uid)],
            'plan.opis' => [($this->plan['vrsta'] ?? '') === 'ostalo' ? 'required' : 'nullable', 'string', 'max:200'],
            'plan.napomena' => 'nullable|string|max:500',
        ]);

        $podaci = [
            'datum' => $this->plan['datum'], 'vrsta' => $this->plan['vrsta'],
            'kultura_id' => blank($this->plan['kultura_id']) ? null : (int) $this->plan['kultura_id'],
            'opis' => blank($this->plan['opis']) ? null : trim($this->plan['opis']),
            'napomena' => blank($this->plan['napomena']) ? null : trim($this->plan['napomena']),
        ];
        if ($this->planEditId) {
            PlaniraniRad::where('user_id', $uid)->otvoreni()->findOrFail($this->planEditId)->update($podaci);
        } else {
            PlaniraniRad::create($podaci + ['user_id' => $uid]);
        }
        $this->odaberi($podaci['datum']);
        $this->odustani();
    }

    public function obrisiPlan(int $id): void
    {
        PlaniraniRad::where('user_id', auth()->id())->otvoreni()->findOrFail($id)->delete();
        $this->odustani();
    }

    /** "Ostalo" nema svoju evidenciju: samo se označi obavljenim. Ostale vrste se zatvaraju upisom zapisa. */
    public function obavljeno(int $id): void
    {
        $p = PlaniraniRad::where('user_id', auth()->id())->otvoreni()->findOrFail($id);
        if ($p->linkZaUnos()) {
            $this->redirect($p->linkZaUnos());

            return;
        }
        $p->update(['obavljeno_at' => now()]);
    }

    public function render()
    {
        $user = auth()->user();
        $prvi = $this->prvi();
        $pocetak = $prvi->copy()->startOfWeek(Carbon::MONDAY);
        $kraj = $prvi->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $parcelaId = $this->parcela !== '' ? (int) $this->parcela : null;

        $dogadaji = Kalendar::dogadaji($user, $pocetak, $kraj, $parcelaId, $this->grupe)->groupBy('datum');

        // dani pod karencom: traka u mreži (samo kad su karence uključene)
        $podKarencom = [];
        if (in_array('karenca', $this->grupe, true)) {
            foreach (Kalendar::karence($user, $pocetak, $kraj, $parcelaId) as $k) {
                for ($d = $k['od']->copy()->max($pocetak); $d->lt($k['do']) && $d->lte($kraj); $d->addDay()) {
                    $podKarencom[$d->format('Y-m-d')][] = $k['kultura'] . ($k['parcela'] ? ' (' . $k['parcela'] . ')' : '') . ': ' . $k['sredstvo'] . ' do ' . $k['do']->copy()->subDay()->format('d.m.Y.');
                }
            }
        }

        $prognoza = $kraj->gte(now()->startOfDay()) && $pocetak->lte(now()->addDays(Prognoza::DANA)) ? Prognoza::za($user) : [];

        $tjedni = [];
        for ($d = $pocetak->copy(); $d->lte($kraj); $d->addDay()) {
            $tjedni[intdiv((int) round($pocetak->diffInDays($d)), 7)][] = $d->format('Y-m-d');
        }

        $parcele = Parcela::where('user_id', $user->id)->orderBy('arkod_broj')->get(['id', 'arkod_broj', 'rucno_ucrtana']);
        $kulture = $this->showPlan
            ? Kultura::with('parcela:id,arkod_broj,rucno_ucrtana')->where('user_id', $user->id)
                ->where(fn ($q) => $q->whereNull('zavrsena_at')->orWhere('zavrsena_at', '>=', now()->subYear()->toDateString()))
                ->orderBy('naziv')->get(['id', 'parcela_id', 'naziv', 'zavrsena_at'])
            : collect();

        return view('livewire.kalendar-pregled', [
            'prvi' => $prvi, 'tjedni' => $tjedni, 'dogadaji' => $dogadaji, 'podKarencom' => $podKarencom, 'prognoza' => $prognoza,
            'parcele' => $parcele, 'kulture' => $kulture, 'danas' => now()->format('Y-m-d'),
            'odabrani' => $this->dan !== '' ? ($dogadaji[$this->dan] ?? collect()) : collect(),
            'imaKoordinate' => $prognoza !== [] || Prognoza::tocka($user) !== null,
        ]);
    }
}
