<?php

namespace App\Http\Controllers;

use App\Models\Gnojidba;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\User;
use Illuminate\Http\Request;

/** Admin pregled registriranih korisnika (samo za ADMIN_EMAILS, vidi Gate 'admin'). */
class AdminController extends Controller
{
    private const SORT = [
        'registracija' => 'created_at',
        'aktivnost'    => 'last_seen_at',
        'ime'          => 'name',
        'parcele'      => 'parcele_count',
        'zapisi'       => 'zapisi_count',
    ];

    // GET /admin/korisnici
    public function korisnici(Request $request)
    {
        $q    = trim((string) $request->query('q', ''));
        $sort = array_key_exists($request->query('sort'), self::SORT) ? $request->query('sort') : 'registracija';
        $dir  = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $korisnici = User::query()
            ->withCount(['parcele', 'kulture', 'gnojidbe', 'prskanja'])
            ->withSum('parcele', 'povrsina_ha')
            ->selectRaw('(select count(*) from gnojidbe where gnojidbe.user_id = users.id)
                       + (select count(*) from prskanja where prskanja.user_id = users.id) as zapisi_count')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('naziv_gospodarstva', 'like', "%{$q}%")
                ->orWhere('mipg', 'like', "%{$q}%")
                ->orWhere('oib', 'like', "%{$q}%")))
            ->orderBy(self::SORT[$sort], $dir)
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $statistika = [
            'korisnika'     => User::count(),
            'novih_7'       => User::where('created_at', '>=', now()->subDays(7))->count(),
            'novih_30'      => User::where('created_at', '>=', now()->subDays(30))->count(),
            'aktivnih_30'   => User::where('last_seen_at', '>=', now()->subDays(30))->count(),
            's_podacima'    => User::has('parcele')->count(),
            'parcela'       => Parcela::count(),
            'hektara'       => (float) Parcela::sum('povrsina_ha'),
            'gnojidbi'      => Gnojidba::count(),
            'prskanja'      => Prskanje::count(),
        ];

        return view('admin.korisnici', compact('korisnici', 'statistika', 'q', 'sort', 'dir'));
    }

    // GET /admin/korisnici/{user}
    public function korisnik(User $user)
    {
        $user->loadCount(['parcele', 'kulture', 'gnojidbe', 'prskanja']);

        $parcele = $user->parcele()->with(['kulture' => fn ($q) => $q->orderByDesc('godina')])->orderBy('arkod_broj')->get();
        $prskanja = $user->prskanja()->with('kultura.parcela')->latest('datum_tretiranja')->limit(10)->get();
        $gnojidbe = $user->gnojidbe()->with('kultura.parcela')->latest('datum')->limit(10)->get();

        $zadnjiZapis = collect([
            $user->prskanja()->max('created_at'),
            $user->gnojidbe()->max('created_at'),
        ])->filter()->max();

        return view('admin.korisnik', compact('user', 'parcele', 'prskanja', 'gnojidbe', 'zadnjiZapis'));
    }
}
