<?php

namespace Tests\Feature;

use App\Models\Gnojidba;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\Skladiste as SkladisteModel;
use App\Models\SkladisteUlaz;
use App\Models\Stroj;
use App\Models\StrojRok;
use App\Models\StrojServis;
use App\Models\User;
use App\Support\DashKartice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashKarticeTest extends TestCase
{
    use RefreshDatabase;

    public function test_mnozina(): void
    {
        $f = fn ($n) => DashKartice::mn($n, 'parcela', 'parcele', 'parcela');
        $this->assertSame('1 parcela', $f(1));
        $this->assertSame('3 parcele', $f(3));
        $this->assertSame('5 parcela', $f(5));
        $this->assertSame('12 parcela', $f(12));
        $this->assertSame('21 parcela', $f(21));
        $this->assertSame('22 parcele', $f(22));
        $this->assertSame('0 parcela', $f(0));
    }

    public function test_brojke_u_karticama(): void
    {
        $this->travelTo('2026-11-05 10:00');
        $u = User::factory()->create();
        $drugi = User::factory()->create();
        Parcela::create(['user_id' => $drugi->id, 'arkod_broj' => '999', 'povrsina_ha' => 100]);
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 12.5]);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Kukuruz', 'posadjena_povrsina_ha' => 10, 'godina' => 2026]);
        Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Ječam', 'posadjena_povrsina_ha' => 2.5, 'godina' => 2026]);
        $prsk = fn ($d) => Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => $d, 'kolicina_sredstva_l_ha' => 1,
            'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);
        $prsk('2026-05-10');
        $prsk('2026-05-20');
        $prsk('2025-12-02');   // prvi mjesec prozora od 12 mjeseci
        $prsk('2025-11-30');   // izvan prozora
        $prsk('2026-12-15');   // budući datum, ne broji se
        Gnojidba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => '2026-03-01', 'tip_gnojiva' => 'KAN 27% N', 'kolicina_kg_ha' => 10, 'posadjena_povrsina_ha' => 10]);
        $s = SkladisteModel::create(['user_id' => $u->id, 'naziv' => 'Glavno', 'zadano' => true]);
        SkladisteUlaz::create(['user_id' => $u->id, 'skladiste_id' => $s->id, 'vrsta' => 'gnojivo', 'naziv' => 'KAN 27% N', 'datum' => '2026-01-01', 'kolicina' => 2000, 'jedinica' => 'kg', 'cijena' => 1000]);
        $st = Stroj::create(['user_id' => $u->id, 'vrsta' => 'traktor', 'naziv' => 'IMT 539']);
        StrojRok::create(['user_id' => $u->id, 'stroj_id' => $st->id, 'vrsta' => 'servis', 'datum_isteka' => now()->addDays(5), 'podsjetnik_dana' => 30]);
        StrojServis::create(['user_id' => $u->id, 'stroj_id' => $st->id, 'datum' => '2026-02-01', 'opis' => 'Ulje', 'trosak' => 200]);

        [$parcele, $gnojidba, $prskanje, $skladiste, $meh, $troskovi] = DashKartice::za($u);

        $this->assertSame('12,50 ha', $parcele['brojka']);
        $this->assertSame('1 parcela · 2 kulture', $parcele['pod']);
        $this->assertSame('Kukuruz', $parcele['redovi'][0]['naziv']);
        $this->assertEquals(100, $parcele['redovi'][0]['posto']);
        $this->assertEquals(25, $parcele['redovi'][1]['posto']);

        $this->assertSame('3 zapisa', $prskanje['brojka']);
        $this->assertSame('zadnji 20.05.2026.', $prskanje['pod']);
        $this->assertCount(12, $prskanje['stupci']);
        $this->assertSame(1, $prskanje['stupci'][0]['broj']);
        $this->assertSame(2, $prskanje['stupci'][5]['broj']);
        $this->assertSame('1 zapis', $gnojidba['brojka']);

        $this->assertSame('1 artikl', $skladiste['brojka']);
        $this->assertSame('1 skladište', $skladiste['pod']);
        $this->assertNull($skladiste['prazno']);
        $this->assertSame('1 stroj', $meh['brojka']);
        $this->assertSame(1, $meh['segmenti'][1]['broj']);   // uskoro

        // utrošak 10 kg/ha × 10 ha × 0,50 €/kg = 50 €, plus servis 200 €
        $this->assertSame('250 €', $troskovi['brojka']);

        $this->actingAs($u)->get('/dashboard')->assertOk()->assertSee('12,50 ha')->assertSee('zadnji 20.05.2026.')->assertSee('250 €');
    }

    public function test_prazno_gospodarstvo_pokazuje_sljedeci_korak(): void
    {
        $u = User::factory()->create();
        [$parcele, , , $skladiste, $meh, $troskovi] = DashKartice::za($u);
        $this->assertSame('Dodajte prvu parcelu →', $parcele['prazno']);
        $this->assertSame('Dodajte prvo skladište →', $skladiste['prazno']);
        $this->assertSame('Dodajte prvi stroj →', $meh['prazno']);
        $this->assertSame('Upišite cijene uz nabavu →', $troskovi['prazno']);
        $this->actingAs($u)->get('/dashboard')->assertOk()->assertSee('Dodajte prvi stroj');
    }
}
