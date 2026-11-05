<?php

namespace Tests\Feature;

use App\Livewire\PrskanjeTable;
use App\Livewire\SkladisteManager;
use App\Models\Gnojidba;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\Skladiste as SkladisteModel;
use App\Models\SkladisteUlaz;
use App\Models\User;
use App\Support\Skladiste;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SkladisteTest extends TestCase
{
    use RefreshDatabase;

    private function kultura(User $u, float $ha = 2): Kultura
    {
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 10]);

        return Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Kukuruz', 'posadjena_povrsina_ha' => $ha, 'godina' => (int) date('Y')]);
    }

    private function prskanje(User $u, Kultura $k, string $datum, string $sredstvo, float $doza, ?float $ha = null, ?int $skladiste = null): Prskanje
    {
        return Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'skladiste_id' => $skladiste, 'datum_tretiranja' => $datum, 'trgovacki_naziv_sredstva' => $sredstvo,
            'kolicina_sredstva_l_ha' => $doza, 'tretirana_povrsina_ha' => $ha, 'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);
    }

    private function skladiste(User $u, string $naziv = 'Glavno', bool $zadano = true): SkladisteModel
    {
        return SkladisteModel::create(['user_id' => $u->id, 'naziv' => $naziv, 'zadano' => $zadano]);
    }

    private function ulaz(SkladisteModel $s, string $vrsta, string $naziv, float $kolicina, string $jedinica, ?float $cijena = null, string $datum = '2026-03-01'): SkladisteUlaz
    {
        return SkladisteUlaz::create(['user_id' => $s->user_id, 'skladiste_id' => $s->id, 'vrsta' => $vrsta, 'naziv' => $naziv, 'datum' => $datum,
            'kolicina' => $kolicina, 'jedinica' => $jedinica, 'cijena' => $cijena]);
    }

    public function test_bez_skladista_nema_pracenja_a_izbornik_postoji(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->get('/dashboard')->assertOk()->assertSee(route('skladiste'), false)->assertDontSee('pri kraju ili u minusu');
        $this->actingAs($u)->get('/skladiste')->assertOk()->assertSee('Dodajte prvo skladište');
        $this->assertNull(Skladiste::napomena($u, 'szb', 'Roundup'));
    }

    public function test_dodavanje_skladista_prvo_je_glavno(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u);
        Livewire::test(SkladisteManager::class)->set('sf.naziv', 'Glavno skladište')->call('spremiSkladiste')->assertHasNoErrors()
            ->call('novoSkladiste')->set('sf.naziv', 'Ekonomija')->call('spremiSkladiste');

        $this->assertTrue(SkladisteModel::where('naziv', 'Glavno skladište')->first()->zadano);
        $this->assertFalse(SkladisteModel::where('naziv', 'Ekonomija')->first()->zadano);
    }

    public function test_potrosnja_iz_prskanja_i_gnojidbe(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u, 2);
        $s = $this->skladiste($u);
        $this->ulaz($s, 'szb', 'Roundup', 20, 'L', 100);
        $this->ulaz($s, 'gnojivo', 'KAN 27% N', 1000, 'kg');

        $this->prskanje($u, $k, '2026-02-01', 'Roundup', 3);          // prije prve nabave: ne broji se
        $this->prskanje($u, $k, '2026-04-01', 'roundup ', 3, 1.5);   // 4,5 L (tretirana površina)
        $this->prskanje($u, $k, '2026-05-01', 'ROUNDUP', 2);          // 4 L (površina kulture)
        $this->prskanje($u, $k, '2026-05-01', 'Drugo sredstvo', 9);   // ne prati se
        Gnojidba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => '2026-04-10', 'tip_gnojiva' => 'KAN 27% N', 'kolicina_kg_ha' => 250]);

        $stanje = Skladiste::stanje($u);
        $this->assertCount(2, $stanje);
        $r = $stanje[$s->id . '|szb|ROUNDUP'];
        $this->assertEqualsWithDelta(8.5, $r['potroseno'], 0.001);
        $this->assertEqualsWithDelta(11.5, $r['stanje'], 0.001);
        $this->assertSame(5.0, $r['cijena_po_jedinici']);
        $this->assertEqualsWithDelta(57.5, $r['vrijednost'], 0.001);
        $this->assertSame('ok', $r['status']);
        $this->assertEqualsWithDelta(500, $stanje[$s->id . '|gnojivo|KAN 27% N']['stanje'], 0.001);

        // izmjena zapisa (update bez modela) ispravi stanje
        Prskanje::where('kolicina_sredstva_l_ha', 2)->update(['kolicina_sredstva_l_ha' => 8]);
        $r = Skladiste::stanje($u)[$s->id . '|szb|ROUNDUP'];
        $this->assertEqualsWithDelta(-0.5, $r['stanje'], 0.001);
        $this->assertSame('minus', $r['status']);
    }

    public function test_vise_skladista_trosi_iz_izabranog_a_bez_izbora_iz_glavnog(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u, 2);
        $glavno = $this->skladiste($u, 'Glavno', true);
        $eko = $this->skladiste($u, 'Ekonomija', false);
        $this->ulaz($glavno, 'szb', 'Roundup', 20, 'L');
        $this->ulaz($eko, 'szb', 'Roundup', 10, 'L');

        $this->prskanje($u, $k, '2026-04-01', 'Roundup', 1);               // bez skladišta → glavno, 2 L
        $this->prskanje($u, $k, '2026-04-02', 'Roundup', 2, null, $eko->id); // ekonomija, 4 L

        $s = Skladiste::stanje($u);
        $this->assertEqualsWithDelta(18, $s[$glavno->id . '|szb|ROUNDUP']['stanje'], 0.001);
        $this->assertEqualsWithDelta(6, $s[$eko->id . '|szb|ROUNDUP']['stanje'], 0.001);

        // forma prskanja sprema izabrano skladište, a napomena prikazuje njegovo stanje
        $this->actingAs($u);
        $this->assertStringContainsString('Na skladištu: 6 L', Skladiste::napomena($u, 'szb', 'Roundup', null, null, $eko->id)['tekst']);
        Livewire::test(PrskanjeTable::class)
            ->set('form.kultura_id', $k->id)->set('form.trgovacki_naziv_sredstva', 'Roundup')
            ->set('form.kolicina_sredstva_l_ha', '1')->set('form.skladiste_id', $eko->id)
            ->call('saveNew')->assertHasNoErrors();
        $this->assertSame($eko->id, Prskanje::latest('id')->first()->skladiste_id);

        // prijenos 5 L iz glavnog u ekonomiju, pa brisanje prijenosa
        $h = md5($glavno->id . '|szb|ROUNDUP');
        Livewire::test(SkladisteManager::class)
            ->set("prijenos.$h.kolicina", '5')->set("prijenos.$h.skladiste_id", $eko->id)
            ->call('prenesi', $h)->assertHasNoErrors();
        $s = Skladiste::stanje($u);
        $this->assertEqualsWithDelta(13, $s[$glavno->id . '|szb|ROUNDUP']['stanje'], 0.001);
        $this->assertEqualsWithDelta(9, $s[$eko->id . '|szb|ROUNDUP']['stanje'], 0.001);   // 10 + 5 − 2×2 − 1×2

        Livewire::test(SkladisteManager::class)->call('obrisi', SkladisteUlaz::whereNotNull('prijenos_par')->first()->id);
        $this->assertSame(0, SkladisteUlaz::whereNotNull('prijenos_par')->count());

        // skladište s ulazima se ne briše
        Livewire::test(SkladisteManager::class)->call('obrisiSkladiste', $eko->id)->assertSet('poruka', fn ($p) => str_contains($p, 'ima ulaze'));
        $this->assertNotNull($eko->fresh());
    }

    public function test_nabava_inventura_i_upozorenje(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u, 2);
        $s = $this->skladiste($u);
        $this->actingAs($u);

        Livewire::test(SkladisteManager::class)
            ->call('novaNabava', 'gnojivo')
            ->set('form.naziv', 'UREA 46% N')
            ->set('form.kolicina', '2')
            ->set('form.jedinica', 't')
            ->set('form.cijena', '1200')
            ->call('spremi')
            ->assertHasNoErrors();

        $ulaz = SkladisteUlaz::first();
        $this->assertSame($s->id, $ulaz->skladiste_id);
        $this->assertEquals(2000, $ulaz->kolicina);
        $this->assertSame('kg', $ulaz->jedinica);

        Gnojidba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => date('Y-m-d'), 'tip_gnojiva' => 'UREA 46% N', 'kolicina_kg_ha' => 900]);
        $kljuc = $s->id . '|gnojivo|UREA 46% N';
        $this->assertSame('malo', Skladiste::stanje($u)[$kljuc]['status']);   // 200 od 2000 kg
        $this->get('/dashboard')->assertSee('pri kraju ili u minusu');

        $h = md5($kljuc);
        Livewire::test(SkladisteManager::class)
            ->set("inventura.$h", '150')
            ->call('spremiInventuru', $h)
            ->assertHasNoErrors();
        $this->assertEqualsWithDelta(150, Skladiste::stanje($u)[$kljuc]['stanje'], 0.001);
        $this->assertTrue(SkladisteUlaz::where('korekcija', true)->exists());
    }

    public function test_tudji_podaci(): void
    {
        $u = User::factory()->create();
        $drugi = User::factory()->create();
        $s = $this->skladiste($u);
        $this->ulaz($s, 'szb', 'Roundup', 5, 'L');

        $n = Skladiste::napomena($u, 'szb', 'roundup', 2.5, 3);
        $this->assertStringContainsString('ostaje -2,5 L', $n['tekst']);
        $this->assertTrue($n['minus']);
        $this->assertNull(Skladiste::napomena($drugi, 'szb', 'Roundup'));

        $this->actingAs($drugi);
        Livewire::test(SkladisteManager::class)->call('obrisi', SkladisteUlaz::first()->id);
        $this->assertSame(1, SkladisteUlaz::count());
        // tuđe skladište ne može se izabrati u prskanju
        $k = $this->kultura($drugi);
        Livewire::test(PrskanjeTable::class)->set('form.kultura_id', $k->id)->set('form.kolicina_sredstva_l_ha', '1')
            ->set('form.skladiste_id', $s->id)->call('saveNew');
        $this->assertNull(Prskanje::where('user_id', $drugi->id)->first()->skladiste_id);
    }
}
