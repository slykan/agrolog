<?php

namespace App\Mail;

use App\Models\Pristup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Savjetniku: poljoprivrednik je prihvatio njegov poziv. */
class PozivPrihvacenMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Pristup $pristup) {}

    public function envelope(): Envelope
    {
        $v = $this->pristup->vlasnik;

        return new Envelope(
            subject: 'Poziv je prihvaćen: ' . ($v->naziv_gospodarstva ?: $v->name),
            replyTo: [new Address('info@on-click.hr', 'AgroLog podrška')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.poziv-prihvacen');
    }
}
