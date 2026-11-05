<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $obavijest->naslov }}</title>
</head>
<body style="margin:0; padding:0; background:#eef4ec; font-family:'Segoe UI', Roboto, Arial, Helvetica, sans-serif; color:#1f2937; -webkit-font-smoothing:antialiased;">
{{-- skriveni pregled u popisu mailova --}}
<div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', $obavijest->tekst), 110) }}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef4ec; padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">

                {{-- zaglavlje --}}
                <tr>
                    <td style="background:#022a20; background-image:linear-gradient(135deg, #022a20 0%, #033b2c 60%, #033b2c 100%); border-radius:18px 18px 0 0; padding:28px 34px 26px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <img src="{{ asset('images/logo/agro-log-bijeli.png') }}" alt="agro-log.app" width="155" height="34" style="display:block; height:34px; width:155px; border:0;">
                                </td>
                                <td align="right">
                                    <span style="display:inline-block; font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#022a20; background:#86bc25; border-radius:999px; padding:4px 10px;">Obavijest</span>
                                </td>
                            </tr>
                        </table>
                        <h1 style="margin:22px 0 0; font-size:26px; line-height:1.25; font-weight:800; color:#ffffff;">{{ $obavijest->naslov }}</h1>
                    </td>
                </tr>
                {{-- zeleni naglasak --}}
                <tr><td style="height:4px; background:#86bc25; line-height:4px; font-size:0;">&nbsp;</td></tr>

                {{-- sadržaj --}}
                <tr>
                    <td style="background:#ffffff; padding:30px 34px 10px;">
                        <p style="margin:0 0 16px; font-size:16px; line-height:1.65; color:#111827;">Pozdrav, <strong>{{ $user->naziv_gospodarstva ?: $user->name }}</strong>!</p>
                        @foreach($obavijest->odlomci() as $odlomak)
                            <p style="margin:0 0 16px; font-size:16px; line-height:1.65; color:#374151;">{!! nl2br(e($odlomak)) !!}</p>
                        @endforeach

                        @if($obavijest->gumb_tekst && $obavijest->gumb_url)
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:26px 0 12px;">
                                <tr>
                                    <td style="background:#2b7d3b; border-radius:12px; box-shadow:0 6px 18px rgba(22,163,74,.35);">
                                        <a href="{{ $obavijest->gumb_url }}" style="display:inline-block; padding:14px 28px; color:#ffffff; font-weight:700; font-size:16px; text-decoration:none; border-radius:12px;">{{ $obavijest->gumb_tekst }} &nbsp;&rarr;</a>
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </td>
                </tr>

                {{-- potpis --}}
                <tr>
                    <td style="background:#ffffff; padding:12px 34px 30px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #e5e7eb;">
                            <tr>
                                <td style="padding-top:18px; font-size:14px; line-height:1.6; color:#4b5563;">
                                    Lijep pozdrav,<br>
                                    <strong style="color:#033b2c;">Tim AgroLog</strong><br>
                                    <span style="color:#6b7280;">Pitanja i prijedlozi: <a href="mailto:info@on-click.hr" style="color:#19653a; text-decoration:none;">info@on-click.hr</a> — ili samo odgovorite na ovaj mail.</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- podnožje --}}
                <tr>
                    <td style="background:#f8faf7; border-radius:0 0 18px 18px; padding:20px 34px; font-size:12px; line-height:1.6; color:#9ca3af; text-align:center;">
                        <a href="{{ url('/') }}" style="color:#19653a; text-decoration:none; font-weight:600;">agro-log.app</a>
                        &nbsp;·&nbsp; <a href="{{ route('novosti') }}" style="color:#6b7280; text-decoration:none;">Novosti</a>
                        &nbsp;·&nbsp; <a href="{{ route('docs') }}" style="color:#6b7280; text-decoration:none;">Upute</a>
                        &nbsp;·&nbsp; <a href="{{ route('alati') }}" style="color:#6b7280; text-decoration:none;">Besplatni alati</a>
                        <br><br>
                        Ovaj mail ste dobili jer imate račun na AgroLogu.
                        Ne želite primati obavijesti? <a href="{{ $odjavaUrl }}" style="color:#9ca3af; text-decoration:underline;">Odjavite se jednim klikom</a>.
                        <br>AgroLog izrađuje <a href="https://on-click.hr" style="color:#9ca3af;">ON-CLICK</a>.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
