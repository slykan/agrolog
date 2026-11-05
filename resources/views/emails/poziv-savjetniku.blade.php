@php
    $v = $pristup->vlasnik;
    $gospodarstvo = $v->naziv_gospodarstva ?: $v->name;
    $link = route('poziv.savjetnik', $pristup->token);
    $unos = $pristup->prava === 'unos';
@endphp
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Poziv savjetniku · AgroLog</title>
</head>
<body style="margin:0; padding:0; background:#eef4ec; font-family:'Segoe UI', Roboto, Arial, Helvetica, sans-serif; color:#1f2937; -webkit-font-smoothing:antialiased;">
<div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ $gospodarstvo }} vam daje pristup svojoj evidenciji gnojidbe i prskanja na AgroLogu — {{ $unos ? 'pregled i unos' : 'samo pregled' }}.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef4ec; padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">

                {{-- zaglavlje --}}
                <tr>
                    <td style="background:#022a20; background-image:linear-gradient(135deg, #022a20 0%, #033b2c 60%, #033b2c 100%); border-radius:18px 18px 0 0; padding:28px 34px 26px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td><img src="{{ asset('images/logo/agro-log-bijeli.png') }}" alt="agro-log.app" width="155" height="34" style="display:block; height:34px; width:155px; border:0;"></td>
                                <td align="right"><span style="display:inline-block; font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#022a20; background:#86bc25; border-radius:999px; padding:4px 10px;">Poziv</span></td>
                            </tr>
                        </table>
                        <h1 style="margin:22px 0 0; font-size:25px; line-height:1.3; font-weight:800; color:#ffffff;">{{ $gospodarstvo }} vas poziva kao savjetnika</h1>
                        <p style="margin:8px 0 0; font-size:15px; line-height:1.5; color:#cfe6a9;">Pristup evidenciji gnojidbe i prskanja na AgroLogu</p>
                    </td>
                </tr>
                <tr><td style="height:4px; background:#86bc25; line-height:4px; font-size:0;">&nbsp;</td></tr>

                {{-- sadržaj --}}
                <tr>
                    <td style="background:#ffffff; padding:30px 34px 6px;">
                        <p style="margin:0 0 16px; font-size:16px; line-height:1.65; color:#111827;">Poštovani,</p>
                        <p style="margin:0 0 18px; font-size:16px; line-height:1.65; color:#374151;">
                            <strong>{{ $v->name }}</strong>{{ $v->naziv_gospodarstva ? ' (' . $v->naziv_gospodarstva . ')' : '' }} vodi evidenciju na AgroLogu i želi da mu pomognete kao savjetnik.
                            Nakon prihvaćanja gospodarstvo vidite u svom popisu <strong>Klijenti</strong>.
                        </p>

                        {{-- kartica s pravima --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f8ed; border:1px solid #c7e0a8; border-radius:14px; margin:0 0 22px;">
                            <tr>
                                <td style="padding:18px 20px;">
                                    <p style="margin:0 0 4px; font-size:12px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#19653a;">Vaša prava</p>
                                    <p style="margin:0 0 10px; font-size:18px; font-weight:800; color:#033b2c;">{{ $unos ? 'Pregled i unos' : 'Samo pregled' }}</p>
                                    <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:14px; line-height:1.55; color:#374151;">
                                        @foreach(array_filter([
                                            'Parcele, kulture i karta ARKOD parcela',
                                            'Evidencija prskanja i gnojidbe, s provjerom prema registru FIS-a',
                                            'Skladište, troškovi i dobit po hektaru',
                                            'Ispis i PDF evidencije te Excel za Ministarstvo',
                                            $unos ? 'Unos i izmjena zapisa — svaka vaša izmjena bilježi se u povijest koju gospodarstvo vidi' : null,
                                        ]) as $stavka)
                                            <tr><td valign="top" style="padding:2px 8px 2px 0; color:#4f9a3a; font-weight:700;">✓</td><td style="padding:2px 0;">{{ $stavka }}</td></tr>
                                        @endforeach
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:4px 0 14px;">
                            <tr>
                                <td style="background:#2b7d3b; border-radius:12px; box-shadow:0 6px 18px rgba(43,125,59,.35);">
                                    <a href="{{ $link }}" style="display:inline-block; padding:14px 30px; color:#ffffff; font-weight:700; font-size:16px; text-decoration:none; border-radius:12px;">Prihvati poziv &nbsp;&rarr;</a>
                                </td>
                            </tr>
                        </table>
                        <p style="margin:0 0 22px; font-size:13px; line-height:1.6; color:#6b7280;">
                            Nemate račun na AgroLogu? Na istoj poveznici možete se besplatno registrirati — poziv se zatim prihvaća jednim klikom.
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb; border-radius:12px; margin:0 0 8px;">
                            <tr>
                                <td style="padding:14px 18px; font-size:13px; line-height:1.6; color:#4b5563;">
                                    <strong style="color:#374151;">Sigurnost:</strong> pristup vrijedi dok ga gospodarstvo ne ukine u svom profilu.
                                    Ne vidite osobne podatke računa ni lozinku, a gospodarstvo uvijek vidi tko je i kada mijenjao evidenciju.
                                    Ako ne očekujete ovaj poziv, slobodno ga zanemarite.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- potpis --}}
                <tr>
                    <td style="background:#ffffff; padding:14px 34px 30px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #e5e7eb;">
                            <tr>
                                <td style="padding-top:18px; font-size:14px; line-height:1.6; color:#4b5563;">
                                    Lijep pozdrav,<br>
                                    <strong style="color:#033b2c;">Tim AgroLog</strong><br>
                                    <span style="color:#6b7280;">Odgovor na ovaj mail stiže izravno gospodarstvu ({{ $v->email }}).</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- podnožje --}}
                <tr>
                    <td style="background:#f8faf7; border-radius:0 0 18px 18px; padding:20px 34px; font-size:12px; line-height:1.6; color:#9ca3af; text-align:center;">
                        <a href="{{ url('/') }}" style="color:#19653a; text-decoration:none; font-weight:600;">agro-log.app</a>
                        &nbsp;·&nbsp; <a href="{{ route('docs') }}" style="color:#6b7280; text-decoration:none;">Upute</a>
                        &nbsp;·&nbsp; <a href="mailto:info@on-click.hr" style="color:#6b7280; text-decoration:none;">info@on-click.hr</a>
                        <br><br>
                        Poveznica ne radi? Kopirajte je u preglednik:<br>
                        <span style="color:#6b7280; word-break:break-all;">{{ $link }}</span>
                        <br><br>AgroLog izrađuje <a href="https://on-click.hr" style="color:#9ca3af;">ON-CLICK</a>.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
