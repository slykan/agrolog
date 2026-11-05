<?php

namespace App\Mail;

use App\Models\Pristup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Gospodarstvu: savjetnik je prihvatio poziv i sada ima pristup. */
class SavjetnikPrihvatioMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Pristup $pristup) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Savjetnik ' . $this->pristup->savjetnik->name . ' je prihvatio pristup vašem gospodarstvu',
            replyTo: [new Address('info@on-click.hr', 'AgroLog podrška')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.savjetnik-prihvatio');
    }
}
