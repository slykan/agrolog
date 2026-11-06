@php
    $s = $poziv->savjetnik;
    $ime = \Illuminate\Support\Str::limit($s->name, 60);
    $tvrtka = \Illuminate\Support\Str::limit((string) $s->naziv_gospodarstva, 60);
    $link = route('poziv.gospodarstvo', $poziv->token);
@endphp
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Poziv na suradnju · AgroLog</title>
</head>
<body style="margin:0; padding:0; background:#eef4ec; font-family:'Segoe UI', Roboto, Arial, Helvetica, sans-serif; color:#1f2937; -webkit-font-smoothing:antialiased;">
<div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ $ime }} vas poziva da surađujete u AgroLogu. Bez vašeg pristanka ne vidi vaše podatke.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef4ec; padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">
                <tr>
                    <td style="background:#022a20; background-image:linear-gradient(135deg, #022a20 0%, #033b2c 60%, #033b2c 100%); border-radius:18px 18px 0 0; padding:28px 34px 26px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td><img src="{{ asset('images/logo/agro-log-bijeli.png') }}" alt="agro-log.app" width="155" height="34" style="display:block; height:34px; width:155px; border:0;"></td>
                                <td align="right"><span style="display:inline-block; font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#022a20; background:#86bc25; border-radius:999px; padding:4px 10px;">Poziv</span></td>
                            </tr>
                        </table>
                        <h1 style="margin:22px 0 0; font-size:25px; line-height:1.3; font-weight:800; color:#ffffff;">{{ $ime }} vas poziva na suradnju</h1>
                        <p style="margin:8px 0 0; font-size:15px; line-height:1.5; color:#cfe6a9;">Vođenje evidencije gnojidbe i prskanja na AgroLogu</p>
                    </td>
                </tr>
                <tr><td style="height:4px; background:#86bc25; line-height:4px; font-size:0;">&nbsp;</td></tr>

                <tr>
                    <td style="background:#ffffff; padding:30px 34px 6px;">
                        <p style="margin:0 0 16px; font-size:16px; line-height:1.65; color:#111827;">Poštovani,</p>
                        <p style="margin:0 0 18px; font-size:16px; line-height:1.65; color:#374151;">
                            <strong>{{ $ime }}</strong>{{ $tvrtka ? ' (' . $tvrtka . ')' : '' }}, {{ $s->email }}, predstavlja se kao agronom ili savjetnik i želi vam pomagati s evidencijom u AgroLogu.
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f8ed; border:1px solid #c7e0a8; border-radius:14px; margin:0 0 22px;">
                            <tr>
                                <td style="padding:18px 20px;">
                                    <p style="margin:0 0 8px; font-size:12px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#19653a;">Vi odlučujete</p>
                                    <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:14px; line-height:1.55; color:#374151;">
                                        @foreach([
                                            'Ne vidi ništa od vaših podataka dok ne prihvatite poziv',
                                            'Sami birate: samo pregled ili pregled i unos',
                                            'Svaku izmjenu savjetnika vidite u povijesti izmjena',
                                            'Pristup možete ukinuti u svakom trenutku',
                                        ] as $stavka)
                                            <tr><td valign="top" style="padding:2px 8px 2px 0; color:#4f9a3a; font-weight:700;">✓</td><td style="padding:2px 0;">{{ $stavka }}</td></tr>
                                        @endforeach
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:4px 0 14px;">
                            <tr>
                                <td style="background:#2b7d3b; border-radius:12px; box-shadow:0 6px 18px rgba(43,125,59,.35);">
                                    <a href="{{ $link }}" style="display:inline-block; padding:14px 30px; color:#ffffff; font-weight:700; font-size:16px; text-decoration:none; border-radius:12px;">Pogledaj poziv &nbsp;&rarr;</a>
                                </td>
                            </tr>
                        </table>
                        <p style="margin:0 0 22px; font-size:13px; line-height:1.6; color:#6b7280;">
                            Nemate račun na AgroLogu? Na istoj poveznici možete se registrirati, a nakon toga se vraćate na poziv.
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb; border-radius:12px; margin:0 0 8px;">
                            <tr>
                                <td style="padding:14px 18px; font-size:13px; line-height:1.6; color:#4b5563;">
                                    <strong style="color:#374151;">Ne poznajete ovu osobu?</strong> Zanemarite ovaj mail ili na poveznici odaberite <strong>Ne želim</strong>, pa vam ta osoba preko AgroLoga više ne može poslati poziv. Za odbijanje vam ne treba račun.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="background:#ffffff; padding:14px 34px 30px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #e5e7eb;">
                            <tr>
                                <td style="padding-top:18px; font-size:14px; line-height:1.6; color:#4b5563;">
                                    Lijep pozdrav,<br>
                                    <strong style="color:#033b2c;">Tim AgroLog</strong><br>
                                    <span style="color:#6b7280;">Odgovor na ovaj mail stiže izravno osobi koja vas poziva ({{ $s->email }}).</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f8faf7; border-radius:0 0 18px 18px; padding:20px 34px; font-size:12px; line-height:1.6; color:#6b7280; text-align:center;">
                        <a href="{{ url('/') }}" style="color:#19653a; text-decoration:none; font-weight:600;">agro-log.app</a>
                        &nbsp;·&nbsp; <a href="{{ route('docs') }}#savjetnik" style="color:#6b7280; text-decoration:none;">Upute</a>
                        &nbsp;·&nbsp; <a href="{{ route('privacy') }}" style="color:#6b7280; text-decoration:none;">Privatnost</a>
                        <br><br>Ovaj mail ste dobili jer je osoba navedena gore upisala vašu adresu na AgroLogu. Šaljemo ga samo kao pojedinačni poziv.
                        <br><br>Poveznica ne radi? Kopirajte je u preglednik:<br>
                        <span style="word-break:break-all;">{{ $link }}</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
