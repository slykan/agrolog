<?php

namespace Tests\Feature;

use App\Mail\PreporukaMail;
use App\Models\Parcela;
use App\Models\Prezentacija;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PrezentacijaTest extends TestCase
{
    use RefreshDatabase;

    private function podaci(array $viska = []): array
    {
        return $viska + [
            'vidljivo' => '1', 'naziv' => 'OPG Horvat', 'mjesto' => 'Hrastin', 'zupanija' => 'Osječko-baranjska',
            'opis' => 'Obiteljsko gospodarstvo s voćnjakom jabuka i kruški. Prodajemo sok i ocat na kućnom pragu.',
            'oznake' => ['Jabuke', 'Džemovi i sokovi'], 'telefon' => '091 234 5678', 'whatsapp' => '1', 'viber' => '0',
            'kucni_prag' => '1', 'radno_vrijeme' => 'pon–sub 8–18 h', 'web' => 'opg-horvat.hr',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.admin_emails' => ['admin@agro-log.app']]);
        Mail::fake();
    }

    public function test_bez_kvacice_nista_ne_trazi_i_ne_stvara(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->post(route('profile.prezentacija'), ['vidljivo' => '0'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(0, Prezentacija::count());
    }

    public function test_s_kvacicom_polja_su_obavezna(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->post(route('profile.prezentacija'), ['vidljivo' => '1', 'opis' => 'prekratko'])
            ->assertSessionHasErrorsIn('prezentacija', ['naziv', 'mjesto', 'zupanija', 'opis', 'oznake', 'telefon']);
        $this->assertSame(0, Prezentacija::count());
    }

    public function test_tijek_od_spremanja_do_javne_stranice(): void
    {
        $u = User::factory()->create();
        Parcela::create(['user_id' => $u->id, 'arkod_broj' => '111', 'povrsina_ha' => 1, 'lat' => 45.5678, 'lng' => 18.7012]);
        Parcela::create(['user_id' => $u->id, 'arkod_broj' => '222', 'povrsina_ha' => 1, 'lat' => 45.5722, 'lng' => 18.7088]);

        $this->actingAs($u)->post(route('profile.prezentacija'), $this->podaci(['slika' => UploadedFile::fake()->image('voćnjak.png', 2400, 1200)]))
            ->assertSessionHasNoErrors()->assertSessionHas('status', 'prezentacija-na-pregledu');
        $p = Prezentacija::first();
        $this->assertSame('opg-horvat-hrastin', $p->slug);
        $this->assertSame('https://opg-horvat.hr', $p->web);
        $this->assertEquals([45.57, 18.71], [(float) $p->lat, (float) $p->lng]);   // približno, iz parcela
        $this->assertSame('na_pregledu', $p->status());
        [$w] = getimagesize(public_path($p->slika));
        $this->assertSame(1600, $w);
        Mail::assertQueued(PreporukaMail::class, fn ($m) => $m->hasTo('admin@agro-log.app') && str_contains($m->predmet, 'odobrenje'));

        // prije odobrenja nije javno
        auth()->logout();
        $this->get(route('gospodarstvo', $p->slug))->assertNotFound();
        $this->get(route('gospodarstva'))->assertOk()->assertDontSee('OPG Horvat');

        // admin odobri
        $admin = User::factory()->create(['email' => 'admin@agro-log.app']);
        $this->actingAs($admin)->post(route('admin.gospodarstva.odobri', $p))->assertRedirect();
        Mail::assertQueued(PreporukaMail::class, fn ($m) => $m->hasTo($u->email) && str_contains($m->predmet, 'na AgroLog popisu'));
        auth()->logout();

        $this->get(route('gospodarstva', ['oznaka' => 'Džemovi i sokovi']))->assertOk()->assertSee('OPG Horvat');
        $this->get(route('gospodarstva', ['oznaka' => 'Med']))->assertOk()->assertDontSee('OPG Horvat');
        $stranica = $this->get(route('gospodarstvo', $p->slug))->assertOk()->assertSee('Prikaži broj')->assertSee('pon–sub 8–18 h');
        $this->assertStringNotContainsString('234 5678', $stranica->getContent());   // broj nije u HTML-u
        $this->getJson(route('gospodarstvo.telefon', $p->slug))->assertOk()
            ->assertJson(['telefon' => '091 234 5678', 'tel' => '+385912345678', 'whatsapp' => 'https://wa.me/385912345678', 'viber' => null]);
        $this->get('/sitemap.xml')->assertSee(route('gospodarstvo', $p->slug), false);

        // skidanje kvačice: odmah nestaje, podaci ostaju
        $this->actingAs($u)->post(route('profile.prezentacija'), ['vidljivo' => '0']);
        auth()->logout();
        $this->get(route('gospodarstvo', $p->slug))->assertNotFound();
        $this->assertNotNull($p->fresh()->opis);

        Prezentacija::obrisiSliku($p->fresh()->slika);
    }

    public function test_odbijanje_i_ponovno_slanje(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->post(route('profile.prezentacija'), $this->podaci());
        $p = Prezentacija::first();
        $admin = User::factory()->create(['email' => 'admin@agro-log.app']);
        $this->actingAs($admin)->post(route('admin.gospodarstva.odbij', $p), ['razlog' => 'Opis treba više detalja.'])->assertRedirect();
        $this->assertSame('odbijeno', $p->fresh()->status());

        $this->actingAs($u)->post(route('profile.prezentacija'), $this->podaci(['opis' => str_repeat('Detaljniji opis gospodarstva. ', 4)]));
        $this->assertSame('na_pregledu', $p->fresh()->status());
        $this->assertNull($p->fresh()->odbijeno_razlog);
    }

    public function test_samo_admin_odobrava(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->post(route('profile.prezentacija'), $this->podaci());
        $p = Prezentacija::first();
        $this->actingAs(User::factory()->create())->post(route('admin.gospodarstva.odobri', $p))->assertForbidden();
        $this->assertNull($p->fresh()->odobreno_at);
    }

    public function test_profil_prikazuje_sekciju(): void
    {
        $this->actingAs(User::factory()->create())->get(route('profile.edit'))->assertOk()->assertSee('Prezentacija na AgroLogu');
    }
}
