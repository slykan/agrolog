@php
    $v = $pristup->vlasnik;
    $gospodarstvo = $v->naziv_gospodarstva ?: $v->name;
@endphp
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Poziv je prihvaćen · AgroLog</title>
</head>
<body style="margin:0; padding:0; background:#eef4ec; font-family:'Segoe UI', Roboto, Arial, Helvetica, sans-serif; color:#1f2937; -webkit-font-smoothing:antialiased;">
<div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ $gospodarstvo }} je sada među vašim klijentima.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef4ec; padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">
                <tr>
                    <td style="background:#022a20; background-image:linear-gradient(135deg, #022a20 0%, #033b2c 60%, #033b2c 100%); border-radius:18px 18px 0 0; padding:28px 34px 26px;">
                        <img src="{{ asset('images/logo/agro-log-bijeli.png') }}" alt="agro-log.app" width="155" height="34" style="display:block; height:34px; width:155px; border:0;">
                        <h1 style="margin:22px 0 0; font-size:25px; line-height:1.3; font-weight:800; color:#ffffff;">Poziv je prihvaćen: {{ $gospodarstvo }}</h1>
                    </td>
                </tr>
                <tr><td style="height:4px; background:#86bc25; line-height:4px; font-size:0;">&nbsp;</td></tr>
                <tr>
                    <td style="background:#ffffff; padding:30px 34px 30px;">
                        <p style="margin:0 0 18px; font-size:16px; line-height:1.65; color:#374151;">Gospodarstvo je sada na vašem popisu klijenata. Vaša prava: <strong style="color:#033b2c;">{{ \App\Models\Pristup::PRAVA[$pristup->prava] }}</strong>.</p>
                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:4px 0 22px;">
                            <tr>
                                <td style="background:#2b7d3b; border-radius:12px; box-shadow:0 6px 18px rgba(43,125,59,.35);">
                                    <a href="{{ route('klijenti') }}" style="display:inline-block; padding:14px 28px; color:#ffffff; font-weight:700; font-size:16px; text-decoration:none; border-radius:12px;">Moji klijenti &nbsp;&rarr;</a>
                                </td>
                            </tr>
                        </table>
                        <p style="margin:0; font-size:14px; line-height:1.6; color:#4b5563;">Lijep pozdrav,<br><strong style="color:#033b2c;">Tim AgroLog</strong></p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f8faf7; border-radius:0 0 18px 18px; padding:20px 34px; font-size:12px; line-height:1.6; color:#6b7280; text-align:center;">
                        <a href="{{ url('/') }}" style="color:#19653a; text-decoration:none; font-weight:600;">agro-log.app</a>
                        &nbsp;·&nbsp; <a href="{{ route('docs') }}#savjetnik" style="color:#6b7280; text-decoration:none;">Upute</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
