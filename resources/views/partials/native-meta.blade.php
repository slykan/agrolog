{{--
    Za Android aplikaciju (AgroLogNative): tko je prijavljen i smije li primati push. Mora biti na SVAKOJ stranici,
    i javnoj: aplikacija nakon svake stranice čita ovu oznaku, a kad je nema, misli da je korisnik odjavljen
    (zaboravi ga i obriše obavijesti). Gost dobiva prazan data-user, što aplikacija ispravno tumači kao odjavu.
    U klijentovom gospodarstvu oznaka nosi stvarnog korisnika (savjetnika), ne vlasnika.
--}}
@php
    $nativeKorisnik = request()->attributes->get('savjetnik') ?? auth()->user();
@endphp
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="agro-native-push" data-user="{{ $nativeKorisnik?->id }}" data-enabled="{{ $nativeKorisnik && $nativeKorisnik->push_obavijesti ? '1' : '0' }}">
<script>
    // Android aplikacija: window.print() u WebViewu ne radi ništa, pa ispis ide preko posebne adrese koju aplikacija presretne
    // (samo od verzije 0.5, starija aplikacija tu adresu ne zna presresti)
    (function () {
        var v = /AgroLogNative\/(\d+)\.(\d+)/.exec(navigator.userAgent);
        if (v && (+v[1] > 0 || +v[2] >= 5)) {
            window.print = function () { window.location.href = '/__aplikacija/ispis'; };
        }
    })();
</script>
