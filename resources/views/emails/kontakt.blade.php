@php
    $p = $podaci;
    $redovi = array_filter(['Tema' => $p['tema_naziv'], 'Ime i prezime' => $p['ime'], 'Tvrtka' => $p['tvrtka'] ?? null, 'OIB' => $p['oib'] ?? null, 'E-mail' => $p['email']]);
    $naslov = $kopija ? 'Primili smo vašu poruku' : 'Nova poruka: ' . $p['tema_naziv'];
@endphp
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $naslov }} · AgroLog</title>
</head>
<body style="margin:0; padding:0; background:#eef4ec; font-family:'Segoe UI', Roboto, Arial, Helvetica, sans-serif; color:#1f2937; -webkit-font-smoothing:antialiased;">
<div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ $kopija ? 'Kopija poruke koju ste poslali AgroLogu.' : $p['ime'] . ' šalje poruku s kontakt obrasca.' }}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef4ec; padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">
                <tr>
                    <td style="background:#022a20; background-image:linear-gradient(135deg, #022a20 0%, #033b2c 60%, #033b2c 100%); border-radius:18px 18px 0 0; padding:28px 34px 26px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td><img src="{{ asset('images/logo/agro-log-bijeli.png') }}" alt="agro-log.app" width="155" height="34" style="display:block; height:34px; width:155px; border:0;"></td>
                                <td align="right"><span style="display:inline-block; font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#022a20; background:#86bc25; border-radius:999px; padding:5px 12px;">{{ $kopija ? 'Kopija' : 'Kontakt' }}</span></td>
                            </tr>
                        </table>
                        <h1 style="margin:22px 0 0; font-size:25px; line-height:1.3; font-weight:800; color:#ffffff;">{{ $naslov }}</h1>
                    </td>
                </tr>
                <tr><td style="height:4px; background:#86bc25; line-height:4px; font-size:0;">&nbsp;</td></tr>

                <tr>
                    <td style="background:#ffffff; padding:30px 34px 6px;">
                        @if($kopija)
                            <p style="margin:0 0 18px; font-size:16px; line-height:1.65; color:#374151;">Pozdrav, <strong style="color:#111827;">{{ $p['ime'] }}</strong>! Hvala što ste nam se javili. Javit ćemo vam se čim prije, a ovo je kopija vaše poruke.</p>
                        @endif

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f8ed; border:1px solid #c7e0a8; border-radius:14px; margin:0 0 20px;">
                            <tr>
                                <td style="padding:8px 20px;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                        @foreach($redovi as $naziv => $vrijednost)
                                            <tr>
                                                <td style="padding:7px 12px 7px 0; font-size:13px; color:#4b5563; white-space:nowrap; vertical-align:top;">{{ $naziv }}</td>
                                                <td style="padding:7px 0; font-size:15px; font-weight:600; color:#033b2c; word-break:break-word;">
                                                    @if($naziv === 'E-mail' && ! $kopija)
                                                        <a href="mailto:{{ $vrijednost }}" style="color:#19653a;">{{ $vrijednost }}</a>
                                                    @else
                                                        {{ $vrijednost }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 8px; font-size:12px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#19653a;">Poruka</p>
                        <p style="margin:0 0 24px; font-size:16px; line-height:1.65; color:#111827; word-break:break-word;">{!! nl2br(e($p['poruka'])) !!}</p>

                        @unless($kopija)
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 22px;">
                                <tr>
                                    <td style="background:#2b7d3b; border-radius:12px; box-shadow:0 6px 18px rgba(43,125,59,.35);">
                                        <a href="mailto:{{ $p['email'] }}?subject={{ rawurlencode('Re: ' . $p['tema_naziv'] . ' · AgroLog') }}" style="display:inline-block; padding:14px 28px; color:#ffffff; font-weight:700; font-size:16px; text-decoration:none;">Odgovori</a>
                                    </td>
                                </tr>
                            </table>
                        @endunless
                    </td>
                </tr>
                <tr>
                    <td style="background:#ffffff; padding:6px 34px 30px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #e5e7eb;">
                            <tr>
                                <td style="padding-top:18px; font-size:14px; line-height:1.6; color:#4b5563;">
                                    @if($kopija)
                                        Lijep pozdrav,<br><strong style="color:#033b2c;">Tim AgroLog</strong>
                                    @else
                                        Poslano s kontakt obrasca na agro-log.app, {{ now('Europe/Zagreb')->format('d.m.Y.') }} u {{ now('Europe/Zagreb')->format('H:i') }}.
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f8faf7; border-radius:0 0 18px 18px; padding:20px 34px; font-size:12px; line-height:1.6; color:#6b7280; text-align:center;">
                        <a href="{{ url('/') }}" style="color:#19653a; text-decoration:none; font-weight:600;">agro-log.app</a>
                        @if($kopija)
                            <br><br>Ovaj mail ste dobili jer ste na kontakt obrascu zatražili kopiju poruke. Ako to niste bili vi, slobodno ga zanemarite.
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
