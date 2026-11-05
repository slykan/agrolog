<?php

namespace Tests\Feature;

use App\Livewire\PrskanjeTable;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\SzbSredstvo;
use App\Models\User;
use App\Support\ProvjeraSzb;
use App\Support\Upozorenja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ProvjeraSzbTest extends TestCase
{
    use RefreshDatabase;

    private function priprema(string $kultura = 'Pšenica'): array
    {
        Http::fake(['fis.mps.hr/*' => Http::response(file_get_contents(base_path('tests/fixtures/fis-detalji-amistar.json')), 200)]);
        SzbSredstvo::create(['fis_id' => 20, 'naziv' => 'AMISTAR 250 SC', 'registracijski_broj' => 'UP/I-320-20/08-01/99', 'aktivno' => true]);
        $u = User::factory()->create();
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 10]);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => $kultura, 'posadjena_povrsina_ha' => 5, 'godina' => 2026]);

        return [$u, $k];
    }

    private function prskaj(User $u, Kultura $k, string $datum): void
    {
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => $datum, 'trgovacki_naziv_sredstva' => 'Amistar 250 SC',
            'kolicina_sredstva_l_ha' => 1, 'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);
    }

    public function test_registrirano_karenca_i_broj_tretmana(): void
    {
        [$u, $k] = $this->priprema('Ozima pšenica');
        $r = ProvjeraSzb::provjeri($u, $k->id, 'amistar 250 sc', '2026-05-01');
        $tekst = collect($r['stavke'])->pluck('tekst')->implode(' | ');
        $this->assertStringContainsString('Registriran za kulturu Pšenica', $tekst);
        $this->assertStringContainsString('Karenca 35 dana: berba/žetva ne prije 05.06.2026.', $tekst);
        $this->assertStringContainsString('1. tretman od najviše 2', $tekst);
        $this->assertSame('Pšenica', $r['kultura_fis']);
        $this->assertNotEmpty($r['primjene']);

        // treći tretman istim sredstvom → upozorenje
        $this->prskaj($u, $k, '2026-04-10');
        $this->prskaj($u, $k, '2026-04-25');
        $r = ProvjeraSzb::provjeri($u, $k->id, 'AMISTAR 250 SC', '2026-05-01');
        $this->assertTrue(collect($r['stavke'])->contains(fn ($s) => $s['razina'] === 'upozorenje' && str_contains($s['tekst'], 'Ovo je 3. tretman')));

        // detalji su spremljeni: drugi put FIS se ne pita
        Http::assertSentCount(1);
    }

    public function test_nije_registrirano_za_kulturu(): void
    {
        [$u, $k] = $this->priprema('Suncokret');
        $r = ProvjeraSzb::provjeri($u, $k->id, 'AMISTAR 250 SC');
        $this->assertSame('upozorenje', $r['stavke'][0]['razina']);
        $this->assertStringContainsString('nije registriran za kulturu „Suncokret”', $r['stavke'][0]['tekst']);
        $this->assertStringContainsString('Ječam', $r['stavke'][0]['tekst']);
    }

    public function test_fis_ne_odgovara(): void
    {
        Http::fake(['fis.mps.hr/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout')]);
        SzbSredstvo::create(['fis_id' => 20, 'naziv' => 'AMISTAR 250 SC', 'aktivno' => true]);
        $u = User::factory()->create();
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1', 'povrsina_ha' => 1]);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Pšenica', 'posadjena_povrsina_ha' => 1, 'godina' => 2026]);
        $r = ProvjeraSzb::provjeri($u, $k->id, 'AMISTAR 250 SC');
        $this->assertStringContainsString('ne odgovara', $r['stavke'][0]['tekst']);
        $this->assertTrue(Cache::has('fis-nedostupan'));
    }

    public function test_forma_prskanja_i_karenca_u_zvonu(): void
    {
        $this->travelTo('2026-05-10 10:00');
        [$u, $k] = $this->priprema();
        $this->actingAs($u);
        Livewire::test(PrskanjeTable::class)
            ->call('addRow')
            ->set('form.kultura_id', $k->id)
            ->set('form.trgovacki_naziv_sredstva', 'AMISTAR 250 SC')
            ->assertSee('Provjera prema registru FIS-a')
            ->assertSee('Registriran za kulturu Pšenica');

        $this->prskaj($u, $k, '2026-05-01');   // karenca do 05.06.2026.
        $z = Upozorenja::za($u->fresh());
        $this->assertTrue($z->contains(fn ($s) => $s['razina'] === 'info' && str_contains($s['tekst'], 'ne prije 05.06.2026.')));
    }
}
