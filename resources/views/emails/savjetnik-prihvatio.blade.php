@php
    $v = $pristup->vlasnik;
    $s = $pristup->savjetnik;
    $unos = $pristup->prava === 'unos';
@endphp
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Savjetnik je prihvatio pristup · AgroLog</title>
</head>
<body style="margin:0; padding:0; background:#eef4ec; font-family:'Segoe UI', Roboto, Arial, Helvetica, sans-serif; color:#1f2937; -webkit-font-smoothing:antialiased;">
<div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ $s->name }} sada ima pristup vašoj evidenciji ({{ $unos ? 'pregled i unos' : 'samo pregled' }}).</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef4ec; padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">
                <tr>
                    <td style="background:#022a20; background-image:linear-gradient(135deg, #022a20 0%, #033b2c 60%, #033b2c 100%); border-radius:18px 18px 0 0; padding:28px 34px 26px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td><img src="{{ asset('images/logo/agro-log-bijeli.png') }}" alt="agro-log.app" width="155" height="34" style="display:block; height:34px; width:155px; border:0;"></td>
                                <td align="right"><span style="display:inline-block; font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#022a20; background:#86bc25; border-radius:999px; padding:4px 10px;">Savjetnik</span></td>
                            </tr>
                        </table>
                        <h1 style="margin:22px 0 0; font-size:25px; line-height:1.3; font-weight:800; color:#ffffff;">{{ $s->name }} je prihvatio pristup</h1>
                    </td>
                </tr>
                <tr><td style="height:4px; background:#86bc25; line-height:4px; font-size:0;">&nbsp;</td></tr>

                <tr>
                    <td style="background:#ffffff; padding:30px 34px 6px;">
                        <p style="margin:0 0 16px; font-size:16px; line-height:1.65; color:#111827;">Pozdrav, <strong>{{ $v->naziv_gospodarstva ?: $v->name }}</strong>!</p>
                        <p style="margin:0 0 18px; font-size:16px; line-height:1.65; color:#374151;">
                            Savjetnik <strong>{{ $s->name }}</strong> ({{ $s->email }}) prihvatio je vaš poziv i od sada ima pristup vašoj evidenciji na AgroLogu.
                        </p>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f8ed; border:1px solid #c7e0a8; border-radius:14px; margin:0 0 22px;">
                            <tr>
                                <td style="padding:16px 20px; font-size:14px; line-height:1.6; color:#374151;">
                                    <p style="margin:0 0 4px; font-size:12px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#19653a;">Prava savjetnika</p>
                                    <p style="margin:0 0 8px; font-size:18px; font-weight:800; color:#033b2c;">{{ $unos ? 'Pregled i unos' : 'Samo pregled' }}</p>
                                    @if($unos)
                                        Svaku izmjenu koju savjetnik napravi vidite u profilu, pod <strong>Pristup savjetniku → Povijest izmjena</strong> — tko je, kada i što mijenjao.
                                    @else
                                        Savjetnik može pregledavati evidenciju i ispisivati PDF, ali ne može ništa mijenjati.
                                    @endif
                                </td>
                            </tr>
                        </table>
                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:4px 0 22px;">
                            <tr>
                                <td style="background:#2b7d3b; border-radius:12px; box-shadow:0 6px 18px rgba(43,125,59,.35);">
                                    <a href="{{ route('profile.edit') }}#savjetnici" style="display:inline-block; padding:14px 28px; color:#ffffff; font-weight:700; font-size:16px; text-decoration:none; border-radius:12px;">Upravljanje pristupom &nbsp;&rarr;</a>
                                </td>
                            </tr>
                        </table>
                        <p style="margin:0 0 8px; font-size:13px; line-height:1.6; color:#6b7280;">Prava možete promijeniti ili pristup ukinuti jednim klikom, u bilo kojem trenutku. Niste vi pozvali ovog savjetnika? Ukinite pristup i javite nam se.</p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#ffffff; padding:14px 34px 30px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #e5e7eb;">
                            <tr>
                                <td style="padding-top:18px; font-size:14px; line-height:1.6; color:#4b5563;">
                                    Lijep pozdrav,<br><strong style="color:#033b2c;">Tim AgroLog</strong><br>
                                    <span style="color:#6b7280;">Pitanja: <a href="mailto:info@on-click.hr" style="color:#19653a; text-decoration:none;">info@on-click.hr</a></span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f8faf7; border-radius:0 0 18px 18px; padding:20px 34px; font-size:12px; line-height:1.6; color:#9ca3af; text-align:center;">
                        <a href="{{ url('/') }}" style="color:#19653a; text-decoration:none; font-weight:600;">agro-log.app</a>
                        &nbsp;·&nbsp; <a href="{{ route('docs') }}" style="color:#6b7280; text-decoration:none;">Upute</a>
                        <br><br>Ovaj mail ste dobili jer ste na AgroLogu pozvali savjetnika. AgroLog izrađuje <a href="https://on-click.hr" style="color:#9ca3af;">ON-CLICK</a>.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
