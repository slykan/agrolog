<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Obavijest o preporuci: nova preporuka gospodarstvu, odgovor agronomu i obrnuto. */
class PreporukaMail extends Mailable
{
    use Queueable;

    public function __construct(
        public string $predmet, public string $naslov, public string $uvod, public array $stavke,
        public string $link, public string $gumb, public ?string $odgovorNa = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->predmet,
            replyTo: [new Address($this->odgovorNa ?: 'info@on-click.hr')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.preporuka');
    }
}
