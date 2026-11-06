<?php

namespace App\Mail;

use App\Http\Controllers\KontaktController;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Poruka s kontakt obrasca: nama (odgovor ide pošiljatelju) ili kopija pošiljatelju (odgovor ide nama). */
class KontaktMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @param array{tema: string, tema_naziv: string, ime: string, tvrtka: ?string, oib: ?string, email: string, poruka: string} $podaci */
    public function __construct(public array $podaci, public bool $kopija) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->kopija
                ? 'Primili smo vašu poruku · AgroLog'
                : '[' . $this->podaci['tema_naziv'] . '] ' . $this->podaci['ime'] . ($this->podaci['tvrtka'] ? ', ' . $this->podaci['tvrtka'] : ''),
            replyTo: [$this->kopija
                ? new Address(KontaktController::PRIMATELJ, 'AgroLog')
                : new Address($this->podaci['email'], $this->podaci['ime'])],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.kontakt');
    }
}
