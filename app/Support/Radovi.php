<?php

namespace App\Support;

use App\Models\Kultura;
use App\Models\OpciTrosak;
use App\Models\Rad;

/** Gotove vrste ostalih radova po vrsti kulture i vrste općih troškova; korisnikove vlastite vrste dodaju se iz ranijih zapisa. */
class Radovi
{
    public const VRSTE = [
        'ratarstvo' => ['Oranje', 'Tanjuranje', 'Podrivanje', 'Sjetvospremač', 'Sjetva', 'Valjanje', 'Kultiviranje', 'Međuredna kultivacija',
            'Malčiranje', 'Zaoravanje žetvenih ostataka', 'Baliranje', 'Prijevoz uroda', 'Sušenje', 'Navodnjavanje'],
        'vinograd' => ['Rezidba', 'Vezanje', 'Zelena rezidba', 'Plijevljenje zaperaka', 'Pinciranje', 'Prorjeđivanje grozdova', 'Uklanjanje lišća',
            'Košnja međurednog prostora', 'Frezanje', 'Malčiranje', 'Usitnjavanje rozgve', 'Popravak armature', 'Navodnjavanje'],
        'maslinik' => ['Rezidba', 'Usitnjavanje granja', 'Košnja', 'Frezanje', 'Malčiranje', 'Prerada u uljari', 'Navodnjavanje'],
        'vocnjak' => ['Rezidba', 'Prorjeđivanje plodova', 'Usitnjavanje granja', 'Košnja međurednog prostora', 'Frezanje', 'Malčiranje',
            'Postavljanje mreže protiv tuče', 'Popravak armature', 'Navodnjavanje'],
    ];

    public const SKUPINE = ['ratarstvo' => 'Ratarstvo i povrće', 'vinograd' => 'Vinograd', 'maslinik' => 'Maslinik', 'vocnjak' => 'Voćnjak'];

    public const OPCE_VRSTE = ['Vožnja', 'Zakup zemlje', 'Osiguranje usjeva', 'Članarina', 'Struja', 'Voda', 'Knjigovodstvo', 'Analiza tla', 'Ostalo'];

    /** Početni prijedlog cijene po km (bez tvrdnje o poreznom statusu); poslije se predlaže zadnja upisana. */
    public const CIJENA_KM = 0.50;

    public static function skupina(?Kultura $k): string
    {
        if (! $k) {
            return 'ratarstvo';
        }
        $n = mb_strtolower((string) $k->naziv);
        if (str_contains($n, 'vinov') || str_contains($n, 'vinograd') || str_contains($n, 'loza')) {
            return 'vinograd';
        }
        if (str_contains($n, 'maslin')) {
            return 'maslinik';
        }

        return $k->trajni || Kultura::jeTrajniNaziv($k->naziv) ? 'vocnjak' : 'ratarstvo';
    }

    /** Ponuđene vrste rada: gotove za skupinu kulture + korisnikove ranije upisane (bez duplikata). */
    public static function vrsteZa(int $userId, ?Kultura $k): array
    {
        $gotove = self::VRSTE[self::skupina($k)];

        return self::spoji($gotove, Rad::where('user_id', $userId)->distinct()->pluck('vrsta')->all());
    }

    public static function opceVrste(int $userId): array
    {
        return self::spoji(self::OPCE_VRSTE, OpciTrosak::where('user_id', $userId)->distinct()->pluck('vrsta')->all());
    }

    private static function spoji(array $gotove, array $vlastite): array
    {
        $kljucevi = array_map(fn ($v) => mb_strtolower($v), $gotove);
        foreach ($vlastite as $v) {
            if (! in_array(mb_strtolower($v), $kljucevi, true)) {
                $gotove[] = $v;
                $kljucevi[] = mb_strtolower($v);
            }
        }

        return $gotove;
    }
}
