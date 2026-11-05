<?php

namespace App\Mail;

use App\Models\Pristup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Poziv savjetniku: gospodarstvo daje pristup svojoj evidenciji na AgroLogu. */
class PozivSavjetnikuMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Pristup $pristup) {}

    public function envelope(): Envelope
    {
        $v = $this->pristup->vlasnik;

        return new Envelope(
            subject: ($v->naziv_gospodarstva ?: $v->name) . ' vas poziva kao savjetnika na AgroLogu',
            replyTo: [new Address($v->email, $v->naziv_gospodarstva ?: $v->name)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.poziv-savjetniku');
    }
}
