<?php

namespace App\Jobs;

use App\Mail\ObavijestMail;
use App\Models\Obavijest;
use App\Models\User;
use App\Support\WebPush;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/** Jedan mail obavijesti jednom korisniku; red čekanja (database) obrađuje scheduler svake minute. */
class PosaljiObavijest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $backoff = 300;

    public function __construct(public int $obavijestId, public int $userId) {}

    public function handle(): void
    {
        $obavijest = Obavijest::find($this->obavijestId);
        $user = User::find($this->userId);

        if (! $obavijest || ! $user) {
            return;   // u međuvremenu obrisan
        }

        if (! $user->obavijesti_odjava_at) {
            Mail::to($user->email)->send(new ObavijestMail($obavijest, $user));
        }
        // push nakon maila: ako mail padne i posao se ponovi, push ne stiže dvaput
        WebPush::korisniku($user, $obavijest->naslov, Str::limit(trim(html_entity_decode(strip_tags($obavijest->tekst))), 140),
            $obavijest->gumb_url ?: route('novosti'));
    }
}
