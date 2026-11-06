<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $predmet }}</title>
</head>
<body style="margin:0; padding:0; background:#eef4ec; font-family:'Segoe UI', Roboto, Arial, Helvetica, sans-serif; color:#1f2937; -webkit-font-smoothing:antialiased;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef4ec; padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">
                <tr>
                    <td style="background:#022a20; border-radius:18px 18px 0 0; padding:22px 34px;">
                        <img src="{{ asset('images/logo/agro-log-bijeli.png') }}" alt="agro-log.app" width="155" height="34" style="display:block; height:34px; width:155px; border:0;">
                    </td>
                </tr>
                <tr><td style="height:4px; background:#86bc25; line-height:4px; font-size:0;">&nbsp;</td></tr>
                <tr>
                    <td style="background:#ffffff; padding:30px 34px 10px;">
                        <p style="margin:0 0 22px; font-size:16px; line-height:1.65; color:#1f2937; word-break:break-word;">{!! nl2br(e($tekst)) !!}</p>
                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
                            <tr>
                                <td style="background:#2b7d3b; border-radius:12px;">
                                    <a href="{{ route('oglasavanje') }}" style="display:inline-block; padding:13px 26px; color:#ffffff; font-weight:700; font-size:15px; text-decoration:none;">Pogledajte mjesta i uvjete</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f8faf7; border-radius:0 0 18px 18px; padding:18px 34px; font-size:12px; line-height:1.6; color:#6b7280;">
                        Vašu adresu pronašli smo među javno objavljenim poslovnim kontaktima. Ako ne želite ovakve poruke, samo odgovorite na ovaj mail i više vam se nećemo javljati.
                        <a href="{{ route('privacy') }}" style="color:#4b5563;">Pravila privatnosti</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
