<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Jednom dnevno javlja nove stavke iz zvona: karence i skladište. Rokovi mehanizacije idu uz svoje podsjetnike,
 * a podsjetnik "za dopuniti" (evidencija 2027., EPPO) ostaje samo u zvonu.
 */
class PushUpozorenja
{
    private const VRSTE = ['karenca-', 'zaliha-'];

    /** @return int broj korisnika kojima je poslano */
    public static function posalji(): int
    {
        $poslano = 0;
        User::where('push_obavijesti', true)
            ->where(fn ($q) => $q->whereHas('pushPretplate')->orWhereHas('mobilePushDevices'))
            ->chunkById(100, function ($korisnici) use (&$poslano) {
            foreach ($korisnici as $user) {
                $poslano += self::zaKorisnika($user) ? 1 : 0;
            }
        });

        return $poslano;
    }

    private static function zaKorisnika(User $user): bool
    {
        $stavke = Upozorenja::za($user)->filter(fn ($s) => str_starts_with($s['kljuc'], self::VRSTE[0]) || str_starts_with($s['kljuc'], self::VRSTE[1]))->values();
        $kljucevi = $stavke->pluck('kljuc');

        // ono što više nije aktivno se zaboravlja: kad sredstvo opet padne pri kraj, javlja se ponovno
        DB::table('push_poslano')->where('user_id', $user->id)->whereNotIn('kljuc', $kljucevi)->delete();

        $vec = DB::table('push_poslano')->where('user_id', $user->id)->pluck('kljuc');
        $nove = $stavke->reject(fn ($s) => $vec->contains($s['kljuc']))->values();
        if ($nove->isEmpty()) {
            return false;
        }

        $prva = $nove->first();
        if ($nove->count() === 1) {
            $stiglo = WebPush::korisniku($user, $prva['naslov'], $prva['tekst'], $prva['link']);
        } else {
            $stiglo = WebPush::korisniku($user, 'AgroLog: nove obavijesti (' . $nove->count() . ')',
                $nove->take(3)->map(fn ($s) => $s['naslov'])->implode(', '), route('dashboard'));
        }
        if (! $stiglo) {
            return false;   // pokušava se opet sutra
        }

        DB::table('push_poslano')->insert($nove->map(fn ($s) => ['user_id' => $user->id, 'kljuc' => $s['kljuc'], 'created_at' => now()])->all());

        return true;
    }
}
