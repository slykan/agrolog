<?php

namespace App\Http\Controllers;

use App\Models\PushPretplata;
use App\Support\WebPush;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Pretplata uređaja na web push. Isti uređaj uvijek pripada korisniku koji je zadnji prijavljen na njemu. */
class PushController extends Controller
{
    public function spremi(Request $request): JsonResponse
    {
        $d = $request->validate([
            'endpoint' => 'required|url|max:2000',
            'keys.p256dh' => 'required|string|max:255',
            'keys.auth' => 'required|string|max:64',
        ]);
        abort_unless(WebPush::dozvoljenEndpoint($d['endpoint']), 422, 'Nepoznat push servis.');

        PushPretplata::updateOrCreate(['endpoint_hash' => hash('sha256', $d['endpoint'])], [
            'user_id' => $request->user()->id, 'endpoint' => $d['endpoint'],
            'p256dh' => $d['keys']['p256dh'], 'auth' => $d['keys']['auth'],
        ]);

        return response()->json(['ok' => true]);
    }

    public function ukloni(Request $request): JsonResponse
    {
        $d = $request->validate(['endpoint' => 'required|string|max:2000']);
        PushPretplata::where('user_id', $request->user()->id)->where('endpoint_hash', hash('sha256', $d['endpoint']))->delete();

        return response()->json(['ok' => true]);
    }

    public function proba(Request $request): JsonResponse
    {
        $user = $request->user();
        $n = 0;
        foreach (PushPretplata::where('user_id', $user->id)->get() as $p) {
            $n += WebPush::posalji($p, 'AgroLog', 'Probna obavijest: push obavijesti rade na ovom uređaju.', route('dashboard')) ? 1 : 0;
        }

        return response()->json(['uredjaja' => $n]);
    }
}
