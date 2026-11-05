<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/** Podsjetnik na rokove mehanizacije (registracija, tehnički, osiguranje, servis, pregled prskalice). */
class PodsjetnikRokovaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public Collection $uskoro, public Collection $istekli) {}

    public function envelope(): Envelope
    {
        $prvi = $this->istekli->first() ?? $this->uskoro->first();
        $ukupno = $this->uskoro->count() + $this->istekli->count();
        $naslov = $ukupno === 1
            ? ($this->istekli->isNotEmpty() ? 'Istekao rok: ' : 'Uskoro ističe: ') . $prvi->opis . ' — ' . $prvi->stroj->naziv
            : 'AgroLog podsjetnik: ' . $ukupno . ' rokova za mehanizaciju';

        return new Envelope(subject: $naslov, replyTo: [new Address('info@on-click.hr', 'AgroLog podrška')]);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.podsjetnik-rokova');
    }
}
