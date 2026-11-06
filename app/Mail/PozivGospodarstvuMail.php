<?php

namespace App\Mail;

use App\Models\PozivGospodarstvu;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/** Poljoprivredniku: savjetnik ga poziva na suradnju. Pristup nastaje tek kad on prihvati. */
class PozivGospodarstvuMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PozivGospodarstvu $poziv) {}

    public function envelope(): Envelope
    {
        $s = $this->poziv->savjetnik;

        return new Envelope(
            subject: Str::limit($s->name, 60) . ' vas poziva na suradnju u AgroLogu',
            replyTo: [new Address($s->email, Str::limit($s->name, 60))],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.poziv-gospodarstvu');
    }
}
