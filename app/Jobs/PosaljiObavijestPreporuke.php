<?php

namespace App\Jobs;

use App\Mail\PreporukaMail;
use App\Models\User;
use App\Support\WebPush;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/** Obavijest o preporuci jednom korisniku, mailom i pushem, prema njegovim postavkama. */
class PosaljiObavijestPreporuke implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $backoff = 300;

    public function __construct(
        public int $userId, public string $predmet, public string $naslov, public string $uvod, public array $stavke,
        public string $link, public string $gumb, public ?string $odgovorNa = null,
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        if (! $user) {
            return;
        }
        if (! $user->obavijesti_odjava_at) {
            Mail::to($user)->send(new PreporukaMail($this->predmet, $this->naslov, $this->uvod, $this->stavke, $this->link, $this->gumb, $this->odgovorNa));
        }
        try {
            WebPush::korisniku($user, $this->naslov, $this->stavke[0] ?? $this->uvod, $this->link);
        } catch (\Throwable $e) {
            report($e);   // mail je već otišao, pa se posao ne ponavlja zbog pusha
        }
    }
}
