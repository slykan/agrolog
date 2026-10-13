<?php

namespace App\Http\Controllers;

use App\Models\Prskanje;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Izvoz evidencije o uporabi sredstava za zaštitu bilja u strojno čitljivom obliku (CSV),
 * s podacima koje traži Provedbena uredba (EU) 2023/564.
 */
class EvidencijaIzvozController extends Controller
{
    public function prskanja(Request $request): StreamedResponse
    {
        $godina = (int) $request->query('godina') ?: null;

        $redovi = Prskanje::where('prskanja.user_id', $request->user()->id)
            ->join('kulture', 'kulture.id', '=', 'prskanja.kultura_id')
            ->join('parcele', 'parcele.id', '=', 'kulture.parcela_id')
            ->when($godina, fn ($q) => $q->whereYear('prskanja.datum_tretiranja', $godina))
            ->orderBy('prskanja.datum_tretiranja')
            ->orderBy('prskanja.id')
            ->select('prskanja.*', 'kulture.naziv as kultura_naziv', 'parcele.arkod_broj')
            ->get();

        $ime = 'evidencija-szb'.($godina ? '-'.$godina : '').'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($redovi) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM da Excel ispravno prikaže č, ć, š, ž, đ
            fputcsv($out, [
                'Datum primjene', 'Vrijeme od', 'Vrijeme do', 'ARKOD ID parcele', 'Kultura',
                'Tretirana površina (ha)', 'Trgovački naziv sredstva', 'Registracijski broj',
                'Doza (l/ha ili kg/ha)', 'Količina vode (l/ha)',
            ], ';');
            $broj = fn ($v) => $v === null ? '' : str_replace('.', ',', rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.'));
            foreach ($redovi as $r) {
                fputcsv($out, [
                    $r->datum_tretiranja?->format('Y-m-d'),
                    $r->vrijeme_od ? substr($r->vrijeme_od, 0, 5) : '',
                    $r->vrijeme_do ? substr($r->vrijeme_do, 0, 5) : '',
                    $r->arkod_broj,
                    $r->kultura_naziv,
                    $broj($r->tretirana_povrsina_ha),
                    $r->trgovacki_naziv_sredstva,
                    $r->registracijski_broj,
                    $broj($r->kolicina_sredstva_l_ha),
                    $broj($r->kolicina_vode_l_ha),
                ], ';');
            }
            fclose($out);
        }, $ime, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
