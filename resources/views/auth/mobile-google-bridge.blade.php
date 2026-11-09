<!DOCTYPE html>
<html lang="hr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Prijavljujem vas · AgroLog</title></head>
<body style="font:16px system-ui;padding:32px;color:#033728">
    <p id="status">Dovršavam prijavu…</p>
    <form method="post" action="{{ route('auth.mobile.google.complete') }}" id="prijava">
        @csrf
        <input type="hidden" name="code" id="code">
        <input type="hidden" name="state" id="state">
    </form>
    <p><a href="{{ route('login') }}">Natrag na prijavu</a></p>
    <script>
        // The one-time code stays in the fragment, outside HTTP URLs, access logs and referrers.
        const podaci = new URLSearchParams(location.hash.slice(1));
        history.replaceState(null, '', location.pathname);
        const code = podaci.get('code') || '', state = podaci.get('state') || '';
        if (/^[a-f0-9]{64}$/.test(code) && /^[a-f0-9]{64}$/.test(state)) {
            document.getElementById('code').value = code;
            document.getElementById('state').value = state;
            document.getElementById('prijava').submit();
        } else {
            document.getElementById('status').textContent = 'Prijava nije dovršena. Pokušajte ponovno.';
        }
    </script>
</body></html>
