<!DOCTYPE html>
<html lang="hr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Google prijava · AgroLog</title></head>
<body style="font:16px system-ui;padding:32px;color:#033728">
    <p>Otvaram Google prijavu u pregledniku…</p>
    <a href="{{ $launch }}">Nastavi s Googleom</a>
    <p><a href="{{ route('login') }}">Natrag na prijavu</a></p>
    <script>window.location.replace(@json($launch));</script>
</body></html>
