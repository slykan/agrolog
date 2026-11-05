<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Podsjetnik na rokove</title>
</head>
<body style="margin:0; padding:0; background:#f3f7f1; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f7f1; padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border-radius:16px; overflow:hidden;">
                <tr>
                    <td style="background:#022a20; padding:24px 32px;">
                        <img src="{{ asset('images/logo/agro-log-bijeli.png') }}" alt="agro-log.app" width="137" height="30" style="display:block; height:30px; width:137px; border:0;">
                        <div style="color:#ffffffcc; font-size:13px; margin-top:6px;">Podsjetnik za mehanizaciju</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px 32px;">
                        <p style="margin:0 0 18px; font-size:15px; line-height:1.6;">
                            Pozdrav {{ $user->naziv_gospodarstva ?: $user->name }}, ovo su rokovi za vašu mehanizaciju na koje treba misliti:
                        </p>

                        @foreach ([['Istekli rokovi', $istekli, '#b91c1c', '#fef2f2'], ['Uskoro ističu', $uskoro, '#92400e', '#fffbeb']] as [$naslov, $rokovi, $boja, $pozadina])
                            @if($rokovi->isNotEmpty())
                                <h2 style="margin:18px 0 8px; font-size:15px; color:{{ $boja }};">{{ $naslov }}</h2>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; border-radius:10px; background:{{ $pozadina }};">
                                    @foreach($rokovi as $r)
                                        <tr>
                                            <td style="padding:10px 14px; border-bottom:1px solid #ffffff;">
                                                <strong>{{ $r->stroj->naziv }}</strong>@if($r->stroj->registracija) <span style="color:#6b7280;">({{ $r->stroj->registracija }})</span>@endif<br>
                                                <span style="color:#4b5563;">{{ $r->opis }}</span>
                                            </td>
                                            <td align="right" style="padding:10px 14px; border-bottom:1px solid #ffffff; white-space:nowrap; color:{{ $boja }};">
                                                <strong>{{ $r->datum_isteka->format('d.m.Y.') }}</strong><br>
                                                <span style="font-size:12px;">{{ $r->dana < 0 ? 'isteklo prije ' . abs($r->dana) . ' d.' : ($r->dana === 0 ? 'ističe danas' : 'za ' . $r->dana . ' d.') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif
                        @endforeach

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0 8px;">
                            <tr>
                                <td style="background:#2b7d3b; border-radius:10px;">
                                    <a href="{{ route('mehanizacija') }}" style="display:inline-block; padding:12px 24px; color:#ffffff; font-weight:bold; font-size:15px; text-decoration:none;">Otvori mehanizaciju &rarr;</a>
                                </td>
                            </tr>
                        </table>
                        <p style="margin:12px 0 0; font-size:13px; line-height:1.6; color:#6b7280;">
                            Kad obavite registraciju, pregled ili servis, kliknite <strong>Obnovljeno</strong> uz rok. AgroLog predloži sljedeći datum i podsjeti vas opet na vrijeme.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 32px; background:#f9fafb; font-size:12px; color:#9ca3af;">
                        Podsjetnik šaljemo jer ste u mehanizaciju na <a href="{{ url('/') }}" style="color:#9ca3af;">agro-log.app</a> upisali ove rokove.
                        Za pojedini rok podsjetnik isključujete u <a href="{{ route('mehanizacija') }}" style="color:#9ca3af;">mehanizaciji</a> (Uredi → bez podsjetnika).
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
