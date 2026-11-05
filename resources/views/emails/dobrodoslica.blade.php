<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dobrodošli u AgroLog</title>
</head>
<body style="margin:0; padding:0; background:#f3f7f1; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f7f1; padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border-radius:16px; overflow:hidden;">
                {{-- zaglavlje --}}
                <tr>
                    <td style="background:#022a20; padding:28px 32px;">
                        <img src="{{ asset('images/logo/agro-log-bijeli.png') }}" alt="agro-log.app" width="155" height="34" style="display:block; height:34px; width:155px; border:0;">
                        <div style="color:#ffffffcc; font-size:13px; margin-top:6px;">Evidencija gnojidbe i prskanja po ARKOD parcelama</div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:32px;">
                        <h1 style="margin:0 0 12px; font-size:22px; color:#033b2c;">Dobrodošli, {{ $user->naziv_gospodarstva ?: $user->name }}!</h1>
                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                            Vaš AgroLog račun je otvoren. Prijavljujete se e-mailom
                            <strong>{{ $user->email }}</strong>{{ $user->google_id ? ' ili gumbom „Prijava putem Googlea”' : '' }}.
                            Korištenje je besplatno, bez pretplate i kreditne kartice.
                        </p>

                        <h2 style="margin:24px 0 12px; font-size:16px; color:#033b2c;">Kako početi — tri koraka</h2>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; line-height:1.5;">
                            @foreach ([
                                ['1', 'Dodajte ARKOD parcelu', 'U izborniku „Parcele & Kulture” upišite ARKOD broj i površinu parcele.'],
                                ['2', 'Dodajte kulturu', 'Na parcelu dodajte kulturu (npr. pšenica) — EPPO kod za evidenciju prepozna se sam.'],
                                ['3', 'Unesite prskanje ili gnojidbu', 'Svaki zapis kasnije možete urediti, ispisati kao PDF ili izvesti u Excel.'],
                            ] as [$broj, $naslov, $opis])
                                <tr>
                                    <td width="36" valign="top" style="padding:0 12px 14px 0;">
                                        <div style="width:28px; height:28px; line-height:28px; border-radius:14px; background:#86bc25; color:#022a20; font-weight:bold; text-align:center;">{{ $broj }}</div>
                                    </td>
                                    <td valign="top" style="padding:0 0 14px;">
                                        <strong>{{ $naslov }}</strong><br>
                                        <span style="color:#4b5563;">{{ $opis }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </table>

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:8px 0 24px;">
                            <tr>
                                <td style="background:#2b7d3b; border-radius:10px;">
                                    <a href="{{ route('parcele') }}" style="display:inline-block; padding:12px 24px; color:#ffffff; font-weight:bold; font-size:15px; text-decoration:none;">Otvori AgroLog &rarr;</a>
                                </td>
                            </tr>
                        </table>

                        <div style="background:#f3f8ed; border:1px solid #e3efd3; border-radius:12px; padding:16px; font-size:14px; line-height:1.5;">
                            <strong style="color:#033b2c;">Spremni za 2027.</strong><br>
                            Od 1. siječnja 2027. evidencija o uporabi sredstava za zaštitu bilja vodi se u propisanom elektroničkom formatu.
                            AgroLog evidenciju izvozi izravno u Ministarstvovu Excel tablicu.
                            <a href="{{ route('novost', 'evidencija-szb-format-ministarstva-2027') }}" style="color:#19653a;">Saznajte više</a>.
                        </div>

                        <p style="margin:24px 0 0; font-size:14px; line-height:1.6; color:#4b5563;">
                            Upute za korištenje su na <a href="{{ route('docs') }}" style="color:#19653a;">agro-log.app/upute</a>,
                            a za pitanja, prijedloge ili prijavu problema pišite na
                            <a href="mailto:info@on-click.hr" style="color:#19653a;">info@on-click.hr</a> (ili samo odgovorite na ovaj mail).
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 32px; background:#f9fafb; font-size:12px; color:#9ca3af;">
                        Ovaj mail ste dobili jer ste otvorili račun na <a href="{{ url('/') }}" style="color:#9ca3af;">agro-log.app</a>.
                        AgroLog izrađuje <a href="https://on-click.hr" style="color:#9ca3af;">ON-CLICK</a>.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
