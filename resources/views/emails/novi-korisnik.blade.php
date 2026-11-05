<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #333; max-width: 500px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #2b7d3b;">AgroLog — novi korisnik</h2>
    <table style="width:100%; border-collapse: collapse; font-size: 14px;">
        <tr>
            <td style="padding: 6px 0; color: #666; width: 160px;">Naziv gospodarstva</td>
            <td style="padding: 6px 0; font-weight: bold;">{{ $user->naziv_gospodarstva }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; color: #666;">Ime i prezime</td>
            <td style="padding: 6px 0;">{{ $user->name }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; color: #666;">Email</td>
            <td style="padding: 6px 0;">{{ $user->email }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; color: #666;">MIPG</td>
            <td style="padding: 6px 0;">{{ $user->mipg ?: '—' }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; color: #666;">OIB</td>
            <td style="padding: 6px 0;">{{ $user->oib ?: '—' }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; color: #666;">Registriran</td>
            <td style="padding: 6px 0;">{{ $user->created_at->format('d.m.Y. H:i') }}</td>
        </tr>
    </table>
    <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
    <p style="font-size: 12px; color: #999;">AgroLog</p>
</body>
</html>
