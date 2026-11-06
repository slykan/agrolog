<?php

namespace App\Mail;

use App\Http\Controllers\KontaktController;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Ponuda za oglašavanje jednoj tvrtki. Odgovor ide na našu adresu. */
class PonudaOglasivacuMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $predmet, public string $tekst) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->predmet, replyTo: [new Address(KontaktController::PRIMATELJ, 'AgroLog')]);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ponuda-oglasivacu');
    }
}
