<?php

namespace App\Http\Controllers;

use App\Jobs\PosaljiObavijest;
use App\Mail\ObavijestMail;
use App\Models\Obavijest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Admin → Obavijesti: mail svim korisnicima. Prvo se pošalje probni mail adminu, zatim svima;
 * mailovi idu kroz red čekanja (database), 8 u minuti, da se ne prijeđe ograničenje slanja servera.
 */
class ObavijestController extends Controller
{
    private const U_MINUTI = 8;

    // GET /admin/obavijesti
    public function index()
    {
        return view('admin.obavijesti', [
            'obavijesti' => Obavijest::latest()->limit(20)->get(),
            'primatelja' => Obavijest::primatelji()->count(),
            'odjavljenih' => User::whereNotNull('obavijesti_odjava_at')->count(),
            'naCekanju' => \Illuminate\Support\Facades\DB::table('jobs')->where('payload', 'like', '%PosaljiObavijest%')->count(),
        ]);
    }

    // POST /admin/obavijesti — akcija "proba" (samo adminu) ili "svima"
    public function posalji(Request $request)
    {
        $podaci = $request->validate([
            'predmet'    => 'required|string|max:150',
            'naslov'     => 'required|string|max:150',
            'tekst'      => 'required|string|max:10000',
            'gumb_tekst' => 'nullable|required_with:gumb_url|string|max:60',
            'gumb_url'   => 'nullable|required_with:gumb_tekst|url|max:500',
            'akcija'     => 'required|in:proba,svima',
        ], [], ['gumb_tekst' => 'tekst gumba', 'gumb_url' => 'poveznica gumba']);

        if ($podaci['akcija'] === 'proba') {
            $proba = new Obavijest($podaci);   // ne sprema se
            Mail::to($request->user()->email)->send(new ObavijestMail($proba, $request->user()));

            return back()->withInput()->with('poruka', 'Probni mail poslan na ' . $request->user()->email . '. Provjerite ga pa pošaljite svima.');
        }

        $obavijest = Obavijest::create($podaci + ['user_id' => $request->user()->id, 'poslano_at' => now()]);

        $i = 0;
        Obavijest::primateljiSvihKanala()->orderBy('id')->select('id')->chunkById(500, function ($korisnici) use ($obavijest, &$i) {
            foreach ($korisnici as $k) {
                PosaljiObavijest::dispatch($obavijest->id, $k->id)
                    ->onConnection('database')
                    ->delay(now()->addMinutes(intdiv($i, self::U_MINUTI)));
                $i++;
            }
        });
        $obavijest->update(['primatelja' => $i]);

        $trajanje = max(1, (int) ceil($i / self::U_MINUTI));

        return redirect()->route('admin.obavijesti')->with('poruka',
            "Obavijest je u redu za slanje: {$i} primatelja. Slanje traje oko {$trajanje} min (" . self::U_MINUTI . ' mailova u minuti).');
    }

    // POST /admin/korisnici/{user}/obavijesti — admin uključuje/isključuje korisnika s liste za obavijesti
    public function prekidac(User $user)
    {
        $user->forceFill(['obavijesti_odjava_at' => $user->obavijesti_odjava_at ? null : now()])->save();

        return back()->with('poruka', $user->email . ($user->obavijesti_odjava_at ? ' više ne prima obavijesti.' : ' ponovno prima obavijesti.'));
    }

    // GET|POST /obavijesti/odjava/{user} (potpisani link iz maila)
    public function odjava(Request $request, User $user)
    {
        if (! $user->obavijesti_odjava_at) {
            $user->forceFill(['obavijesti_odjava_at' => now()])->save();
        }

        if ($request->isMethod('post')) {
            return response()->noContent();   // jednoklik odjava iz mail klijenta
        }

        return view('obavijesti-odjava', ['user' => $user]);
    }
}
