<?php

namespace App\Jobs;

use App\Mail\ObavijestMail;
use App\Models\Obavijest;
use App\Models\User;
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

        // u međuvremenu obrisan ili odjavljen — ne šalje se
        if (! $obavijest || ! $user || $user->obavijesti_odjava_at) {
            return;
        }

        Mail::to($user->email)->send(new ObavijestMail($obavijest, $user));
    }
}
