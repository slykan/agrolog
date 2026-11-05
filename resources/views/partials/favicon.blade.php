{{-- ikona stranice: ico i png (novi logotip agro-log.app), apple-touch za iPhone; manifest = instalacija kao aplikacija (PWA) --}}
<link rel="icon" href="/favicon.ico" sizes="16x16 32x32 48x48">
<link rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">
<link rel="icon" href="/icon-192.png" type="image/png" sizes="192x192">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#033728">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="AgroLog">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<script>
    // poziv za instalaciju (Android/Chrome) stigne rano — spremi ga da ga traka "Instaliraj" može kasnije pokazati
    window.addEventListener('beforeinstallprompt', function (e) { e.preventDefault(); window.agroInstalacija = e; window.dispatchEvent(new Event('agro-instalacija')); });
    if ('serviceWorker' in navigator) { window.addEventListener('load', function () { navigator.serviceWorker.register('/sw.js').catch(function () {}); }); }
</script>
