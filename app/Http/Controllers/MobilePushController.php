<?php

namespace App\Http\Controllers;

use App\Models\MobilePushDevice;
use App\Support\MobilePush;
use Illuminate\Http\Request;

class MobilePushController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate(['token' => ['required', 'string', 'min:20', 'max:4096']]);
        abort_unless($request->user()->push_obavijesti, 403);
        $sessionHash = hash('sha256', $request->session()->getId());
        $hash = hash('sha256', $data['token']);
        // Token rotation must not leave the previous token attached to the same session.
        MobilePushDevice::where('user_id', $request->user()->id)->where('session_hash', $sessionHash)
            ->where('token_hash', '!=', $hash)->delete();
        MobilePushDevice::updateOrCreate(['token_hash' => $hash], [
            'user_id' => $request->user()->id, 'token' => $data['token'], 'session_hash' => $sessionHash,
        ]);

        return response()->json(['ok' => true]);
    }

    public function test(Request $request)
    {
        return response()->json(['uredjaja' => MobilePush::korisniku($request->user(), 'AgroLog',
            'Push obavijesti rade na vašem mobitelu.', route('dashboard'))]);
    }
}
