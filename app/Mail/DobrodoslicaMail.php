<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Mail dobrodošlice svakom novom korisniku (registracija e-mailom ili Googleom). */
class DobrodoslicaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Dobrodošli u AgroLog — vaš račun je otvoren',
            replyTo: [new Address('info@on-click.hr', 'AgroLog podrška')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.dobrodoslica');
    }
}
