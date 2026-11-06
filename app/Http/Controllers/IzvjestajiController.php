<?php

namespace App\Http\Controllers;

use App\Support\Izvjestaji;
use App\Support\SzbPopisi;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Izvještaji za razdoblje: ispis (PDF iz preglednika) evidencije prskanja i gnojidbe, CSV gnojidbe i prometa skladišta. */
class IzvjestajiController extends Controller
{
    public function ispis(Request $request, string $vrsta): View
    {
        abort_unless(in_array($vrsta, ['prskanje', 'gnojidba', 'berba'], true), 404);
        [$od, $do] = Izvjestaji::razdoblje($request);
        $user = $request->user();

        return view('izvjestaji.ispis', [
            'vrsta' => $vrsta, 'od' => $od, 'do' => $do, 'user' => $user,
            'redovi' => match ($vrsta) { 'prskanje' => Izvjestaji::prskanja($user, $od, $do), 'gnojidba' => Izvjestaji::gnojidbe($user, $od, $do), 'berba' => Izvjestaji::berbe($user, $od, $do) },
            'vrste' => SzbPopisi::VRSTE_UPORABE_KRATKO, 'sustavi' => SzbPopisi::SUSTAVI,
            'automatski' => ! $request->boolean('bez_ispisa'),
        ]);
    }

    public function gnojidbeCsv(Request $request): StreamedResponse
    {
        [$od, $do] = Izvjestaji::razdoblje($request);
        $redovi = Izvjestaji::gnojidbe($request->user(), $od, $do);
        $b = fn ($v, $d = 3) => Izvjestaji::csvBroj($v, $d);

        return $this->csv('evidencija-gnojidbe-' . $od->format('Y-m-d') . '-' . $do->format('Y-m-d') . '.csv',
            ['Datum', 'ARKOD ID parcele', 'Kultura', 'Površina (ha)', 'Gnojivo', 'Količina (kg/ha)', 'Ukupno (kg)', 'N %', 'P2O5 %', 'K2O %', 'Stajski gnoj', 'kg N/ha', 'Ukupno kg N'],
            $redovi->map(fn ($r) => [
                $r->datum->format('d.m.Y'), \App\Models\Parcela::oznaka($r->arkod_broj, $r->rucno_ucrtana), $r->kultura_naziv, $b($r->povrsina, 2), $r->tip_gnojiva,
                $b($r->kolicina_kg_ha, 2), $b($r->ukupno, 2), $b($r->n_posto, 2), $b($r->p_posto, 2), $b($r->k_posto, 2),
                $r->organsko ? 'da' : 'ne', $b($r->kg_n_ha, 1), $b($r->kg_n, 1),
            ]));
    }

    public function berbeCsv(Request $request): StreamedResponse
    {
        [$od, $do] = Izvjestaji::razdoblje($request);
        $redovi = Izvjestaji::berbe($request->user(), $od, $do);
        $b = fn ($v, $d = 3) => Izvjestaji::csvBroj($v, $d);

        return $this->csv('berba-' . $od->format('Y-m-d') . '-' . $do->format('Y-m-d') . '.csv',
            ['Datum', 'ARKOD ID parcele', 'Kultura', 'Sorta', 'Klasa', 'Količina (kg)', 'Cijena (€/kg)', 'Iznos (€)', 'Pobrana površina (ha)', 'Kupac', 'Napomena'],
            $redovi->map(fn ($r) => [
                $r->datum->format('d.m.Y'), \App\Models\Parcela::oznaka($r->kultura?->parcela?->arkod_broj, $r->kultura?->parcela?->rucno_ucrtana), $r->kultura?->naziv, $r->sorta, $r->klasa,
                $b($r->kolicina_kg, 2), $b($r->cijena_kg, 4), $b($r->iznos, 2), $b($r->povrsina_ha, 2), $r->kupac, $r->napomena,
            ]));
    }

    public function skladisteCsv(Request $request): StreamedResponse
    {
        [$od, $do] = Izvjestaji::razdoblje($request);
        $redovi = Izvjestaji::prometSkladista($request->user(), $od, $do);
        $b = fn ($v, $d = 3) => Izvjestaji::csvBroj($v, $d);

        return $this->csv('promet-skladista-' . $od->format('Y-m-d') . '-' . $do->format('Y-m-d') . '.csv',
            ['Datum', 'Skladište', 'Promet', 'Sredstvo / gnojivo', 'Količina', 'Jedinica', 'Iznos (€)', 'ARKOD', 'Kultura', 'Napomena'],
            $redovi->map(fn ($r) => [
                $r['datum']->format('d.m.Y'), $r['skladiste'], $r['promet'], $r['artikl'], $b($r['kolicina']), $r['jedinica'],
                $b($r['iznos'], 2), $r['parcela'], $r['kultura'], $r['napomena'],
            ]));
    }

    /** CSV za Excel: UTF-8 s BOM-om (č, ć, š, ž, đ), točka-zarez, decimalni zarez. */
    private function csv(string $ime, array $zaglavlje, $redovi): StreamedResponse
    {
        return response()->streamDownload(function () use ($zaglavlje, $redovi) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $zaglavlje, ';');
            foreach ($redovi as $r) {
                fputcsv($out, $r, ';');
            }
            fclose($out);
        }, $ime, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
