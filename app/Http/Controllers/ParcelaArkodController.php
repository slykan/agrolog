<?php

namespace App\Http\Controllers;

use App\Models\Parcela;
use App\Support\ArkodServis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Oblik parcele iz ARKOD-a za kartu na dashboardu; parcele bez spremljenog poligona dohvate ga jednom i spreme. */
class ParcelaArkodController extends Controller
{
    public function __invoke(Request $request, Parcela $parcela): JsonResponse
    {
        abort_unless($parcela->user_id === $request->user()->id, 404);

        if (! $parcela->arkod_poligon && ! $parcela->rucno_ucrtana) {
            try {
                $arkod = ArkodServis::dohvati($parcela->arkod_broj);
            } catch (\Throwable $e) {
                report($e);
                $arkod = null;
            }
            if ($arkod) {
                $parcela->update(Parcela::arkodPolja($arkod));
            }
        }

        return response()->json([
            'arkod'   => $parcela->arkod_broj,
            'poligon' => $parcela->arkod_poligon,
        ]);
    }
}
