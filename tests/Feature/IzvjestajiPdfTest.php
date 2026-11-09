<?php

namespace Tests\Feature;

use App\Models\Berba;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Na mobitelu i u aplikaciji evidencija se preuzima kao PDF datoteka umjesto ispisa iz preglednika. */
class IzvjestajiPdfTest extends TestCase
{
    use RefreshDatabase;

    private const MOBITEL = ['User-Agent' => 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 Mobile Safari/537.36'];
    private const RACUNALO = ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/130.0 Safari/537.36'];
    private const APLIKACIJA = ['User-Agent' => 'Mozilla/5.0 (Linux; Android 14) AgroLogNative/0.5.0 Android'];

    public function test_pdf_se_preuzima_kao_datoteka(): void
    {
        $u = User::factory()->create(['naziv_gospodarstva' => 'OPG Šarić Čepin']);
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1234567', 'povrsina_ha' => 2]);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Pšenica', 'posadjena_povrsina_ha' => 2, 'godina' => date('Y')]);
        Berba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => date('Y') . '-07-10', 'kolicina_kg' => 12000, 'jedinica' => 't', 'kupac' => 'Žitar d.o.o.']);

        foreach (['berba', 'prskanje', 'gnojidba'] as $vrsta) {
            $r = $this->actingAs($u)->get(route('izvjestaji.pdf', ['vrsta' => $vrsta]));
            $r->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF', $r->getContent());
            $this->assertStringContainsString('attachment; filename="evidencija-', $r->headers->get('Content-Disposition'));
        }
        $this->get(route('izvjestaji.pdf', ['vrsta' => 'nesto']))->assertNotFound();
        auth()->logout();
        $this->get(route('izvjestaji.pdf', ['vrsta' => 'berba']))->assertRedirect(route('login'));
    }

    // Livewire unutar jednog testa pamti prvi render komponente, pa svaki User-Agent ide u zasebni test
    private function provjeriMobilni(array $ua): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->get(route('prskanje'), $ua)->assertOk()
            ->assertSee(route('izvjestaji.pdf', ['vrsta' => 'prskanje']), false)->assertDontSee('window.print()', false);
    }

    public function test_na_mobitelu_tablica_preuzima_pdf(): void
    {
        $this->provjeriMobilni(self::MOBITEL);
    }

    public function test_u_aplikaciji_tablica_preuzima_pdf(): void
    {
        $this->provjeriMobilni(self::APLIKACIJA);
    }

    public function test_na_mobitelu_izvjestaji_nude_samo_pdf(): void
    {
        $this->actingAs(User::factory()->create())->get(route('izvjestaji'), self::MOBITEL)->assertOk()
            ->assertSee(route('izvjestaji.pdf', ['vrsta' => 'gnojidba']), false)->assertDontSee('🖨 Ispis');
    }

    public function test_na_racunalu_ispis_ostaje_uz_pdf(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->get(route('izvjestaji'), self::RACUNALO)->assertSee('🖨 Ispis')->assertSee(route('izvjestaji.pdf', ['vrsta' => 'berba']), false);
    }

    public function test_na_racunalu_tablica_ima_ispis(): void
    {
        $this->actingAs(User::factory()->create())->get(route('prskanje'), self::RACUNALO)->assertSee('window.print()', false);
    }

    public function test_ispis_u_pregledniku_i_dalje_radi(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->get(route('izvjestaji.ispis', ['vrsta' => 'gnojidba']))->assertOk()
            ->assertSee('window.print()', false)->assertSee('class="zaglavlje"', false);
    }
}
