<?php

namespace App\Support;

use App\Jobs\PosaljiObavijestPreporuke;
use App\Models\Parcela;
use App\Models\Preporuka;
use Illuminate\Support\Str;

/** Prijelazi stanja preporuke i obavijesti o njima. */
class Preporuke
{
    public static function oznakaParcele(?Parcela $p): ?string
    {
        if (! $p) {
            return null;
        }

        return $p->rucno_ucrtana ? $p->arkod_broj . ' (ručno ucrtana)' : 'ARKOD ' . $p->arkod_broj;
    }

    /** Javlja agronomu što je gospodarstvo napravilo s preporukom. */
    public static function javiSavjetniku(Preporuka $p, string $sto, ?string $tekst = null): void
    {
        if (! $p->savjetnik_id) {
            return;
        }
        $g = $p->vlasnik;
        $ime = $g->naziv_gospodarstva ?: $g->name;
        PosaljiObavijestPreporuke::dispatch(
            $p->savjetnik_id,
            $ime . ': ' . $sto,
            $ime . ': ' . $sto,
            'Gospodarstvo je odgovorilo na vašu preporuku.',
            array_values(array_filter([$p->sazetak() ?: Str::limit((string) $p->napomena, 120), $tekst ? '„' . $tekst . '”' : null])),
            route('klijenti.preporuke'),
            'Pogledaj preporuke',
            $g->email,
        );
    }

    /** Zapis iz evidencije zatvara preporuku; poziva se samo nakon stvarnog unosa. */
    public static function zavrsiUnosom(?int $id, int $vlasnikId, string $vrsta, int $zapisId): void
    {
        if (! $id) {
            return;
        }
        $p = Preporuka::where('id', $id)->where('vlasnik_id', $vlasnikId)->where('vrsta', $vrsta)->whereIn('status', Preporuka::OTVORENO)->first();
        if ($p) {
            $p->update(['status' => 'napravljeno', 'zapis_id' => $zapisId, 'odgovoreno_at' => now(), 'savjetnik_vidio_at' => null]);
            self::javiSavjetniku($p, 'napravljeno');
        }
    }

    /** Otvorena preporuka za predpopunjavanje unosa, ili null ako nije njegova ili je već zatvorena. */
    public static function zaUnos(int $id, int $vlasnikId, string $vrsta): ?Preporuka
    {
        $p = Preporuka::where('id', $id)->where('vlasnik_id', $vlasnikId)->where('vrsta', $vrsta)->first();

        return $p && $p->imaUnos() ? $p : null;
    }
}
