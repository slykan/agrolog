<?php

namespace App\Mail;

use App\Models\Obavijest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/** Obavijest korisniku iz admina, s linkom za odjavu. */
class ObavijestMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $odjavaUrl;

    public function __construct(public Obavijest $obavijest, public User $user)
    {
        $this->odjavaUrl = URL::signedRoute('obavijesti.odjava', ['user' => $user->id]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->obavijest->predmet,
            replyTo: [new Address('info@on-click.hr', 'AgroLog podrška')],
        );
    }

    public function headers(): Headers
    {
        // jednim klikom odjava u Gmailu/Outlooku
        return new Headers(text: [
            'List-Unsubscribe' => '<' . $this->odjavaUrl . '>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.obavijest');
    }
}
