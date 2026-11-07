@extends('layouts.pravno')
@section('naslov', 'Pravila privatnosti')
@section('opis', 'Koje osobne podatke AgroLog obrađuje, zašto, s kime ih dijeli, koliko ih čuva i koja su vaša prava.')

@section('sadrzaj')
    @php
        $n = 'text-xl font-semibold text-gray-800 mb-3';
        $lista = 'list-disc pl-6 space-y-2';
        $a = 'text-green-700 hover:underline';
    @endphp

    <section>
        <h2 class="{{ $n }}">1. Voditelj obrade</h2>
        <p>Voditelj obrade vaših osobnih podataka je Alan Fadljević. Za sva pitanja o privatnosti pišite na <a href="mailto:info@agro-log.app" class="{{ $a }}">info@agro-log.app</a>. Ova pravila vrijede za stranicu agro-log.app i aplikaciju AgroLog, u skladu s Uredbom (EU) 2016/679 (GDPR).</p>
    </section>

    <section>
        <h2 class="{{ $n }}">2. Koje podatke obrađujemo</h2>
        <ul class="{{ $lista }}">
            <li><strong>Račun:</strong> ime i prezime, e-mail, lozinka (samo u šifriranom obliku), naziv gospodarstva, MIPG i OIB. Kod prijave Google računom i Googleov ID te slika profila.</li>
            <li><strong>Parcele i kulture:</strong> ARKOD brojevi, nazivi i oblici parcela koje sami ucrtate, površine, oblik parcele, kulture, sorte, broj stabala.</li>
            <li><strong>Lokacija uređaja:</strong> koristi se samo kad pri ucrtavanju parcele kliknete gumb za lokaciju. Spremamo jedino točke koje dodate u oblik parcele.</li>
            <li><strong>Evidencija:</strong> zapisi o prskanju, gnojidbi i berbi, uključujući količine, cijene i kupce koje upišete.</li>
            <li><strong>Skladište, mehanizacija i troškovi:</strong> nabave i zalihe, strojevi, registracijske oznake, rokovi, servisi, ostali radovi, opći troškovi i vožnje (odredište i kilometri koje upišete), prinosi, potpore i iznosi.</li>
            <li><strong>Pristup savjetniku:</strong> e-mail pozvane osobe, prava pristupa i povijest izmjena, uključujući ime savjetnika.</li>
            <li><strong>Preporuke savjetnika:</strong> sadržaj preporuke, kultura i parcela na koju se odnosi, vaš odgovor i pitanja. Vidite ih vi i savjetnik koji ih je poslao.</li>
            <li><strong>Poziv od savjetnika:</strong> ako vas agronom ili savjetnik pozove, od njega dobivamo vašu e-mail adresu i čuvamo je uz podatak o slanju i vašem odgovoru.</li>
            <li><strong>Obavijesti:</strong> vaši odabiri za mail i push te tehnički podaci o pretplati uređaja na push.</li>
            <li><strong>Tehnički podaci:</strong> IP adresa, vrsta preglednika, podaci o sesiji i vrijeme zadnje aktivnosti.</li>
            <li><strong>Upiti:</strong> ono što nam pošaljete mailom ili kontakt obrascem: ime i prezime, e-mail, po želji naziv tvrtke i OIB te poruka.</li>
            <li><strong>Poslovni kontakti:</strong> ako vam pošaljemo ponudu za oglašavanje, čuvamo naziv tvrtke, javno objavljenu poslovnu e-mail adresu, datum slanja te podatak ako ste rekli da ne želite poruke.</li>
            <li><strong>Oglasi:</strong> prikaze i klikove na naš oglasni banner brojimo samo zbirno, po danu i mjestu na stranici, bez podataka o osobi.</li>
        </ul>
    </section>

    <section>
        <h2 class="{{ $n }}">3. Zašto ih obrađujemo i na kojoj osnovi</h2>
        <ul class="{{ $lista }}">
            <li><strong>Pružanje usluge</strong>, uključujući evidenciju, izvještaje, podsjetnike na rokove i pristup savjetniku: izvršenje ugovora (čl. 6. st. 1. b GDPR-a).</li>
            <li><strong>Sigurnost, sprječavanje zlouporabe i tehnička podrška:</strong> legitimni interes (čl. 6. st. 1. f).</li>
            <li><strong>Poziv od savjetnika:</strong> legitimni interes savjetnika i poljoprivrednika da uspostave suradnju (čl. 6. st. 1. f). Poziv možete odbiti bez računa, nakon čega vam taj savjetnik ne može poslati novi. Pristup vašim podacima nastaje samo ako ga sami prihvatite.</li>
            <li><strong>Ponude za oglašavanje poslovnim kontaktima:</strong> legitimni interes predstavljanja usluge. Možete odbiti daljnje poruke odgovorom na mail, nakon čega vam više ne pišemo.</li>
            <li><strong>Odgovor na upit s kontakt obrasca:</strong> legitimni interes, odnosno radnje prije sklapanja ugovora, npr. kod upita o oglašavanju (čl. 6. st. 1. f i b). Poruku ne spremamo u aplikaciji, nego stiže na naš mail.</li>
            <li><strong>Novosti o AgroLogu mailom i pushem:</strong> legitimni interes da korisnike obavijestimo o usluzi koju koriste. Možete ih ugasiti u svakom trenutku.</li>
            <li><strong>Oglasni kolačići:</strong> vaša privola (čl. 6. st. 1. a), koju možete povući. Vidi <a href="{{ route('kolacici') }}" class="{{ $a }}">Pravila o kolačićima</a>.</li>
            <li><strong>Zakonske obveze</strong>, ako nas na to obvezuje propis ili nadležno tijelo (čl. 6. st. 1. c).</li>
        </ul>
        <p class="mt-3">Ne donosimo odluke o vama isključivo automatiziranom obradom koje bi imale pravne učinke na vas. Upozorenja u aplikaciji, npr. o karenci, samo su pomoć pri radu.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">4. S kime dijelimo podatke</h2>
        <p class="mb-3">Vaše podatke ne prodajemo. Mogu ih vidjeti ili primiti:</p>
        <ul class="{{ $lista }}">
            <li><strong>Savjetnik kojeg sami pozovete</strong> vidi, a uz vaše dopuštenje i mijenja, evidenciju vašeg gospodarstva. Pristup možete ukinuti u svakom trenutku.</li>
            <li><strong>Ovlaštene osobe voditelja obrade,</strong> radi podrške i održavanja.</li>
            <li><strong>Pružatelji hostinga i slanja e-pošte,</strong> radi rada usluge.</li>
            <li><strong>Agencija za plaćanja u poljoprivredi, ribarstvu i ruralnom razvoju (ARKOD):</strong> ARKOD broj, radi dohvata parcele i prikaza slojeva karte.</li>
            <li><strong>Esri (satelitska karta i pretraga adresa) i Open-Meteo (prognoza i pretraga mjesta):</strong> vaš preglednik im se obraća izravno, pa vide IP adresu, područje na karti koje gledate i upisanu adresu ili mjesto.</li>
            <li><strong>Google:</strong> ako se prijavljujete Google računom, ako prihvatite oglasne kolačiće i ako push obavijesti na vašem uređaju idu preko Googlea.</li>
            <li><strong>Push servisi preglednika</strong> (Google, Apple, Microsoft, Mozilla): dostavljaju obavijest na uređaj. Sadržaj je šifriran, a vide adresu pretplate i vrijeme slanja.</li>
            <li><strong>Bunny.net (fontovi) i unpkg (biblioteka za kartu):</strong> preglednik ih dohvaća izravno, pa vide IP adresu.</li>
            <li><strong>Nadležna tijela,</strong> ako to zahtijeva zakon.</li>
        </ul>
        <p class="mt-3">Registar FIS-a Ministarstva poljoprivrede koristimo za podatke o sredstvima, a pritom mu ne šaljemo vaše osobne podatke.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">5. Prijenos izvan EU-a</h2>
        <p>Neki od navedenih pružatelja, npr. Google, Apple, Microsoft i Esri, imaju sjedište ili obrađuju podatke u SAD-u. Takav se prijenos oslanja na mehanizme iz poglavlja V. GDPR-a koje ti pružatelji navode u svojim uvjetima. Podaci vaše evidencije ostaju na našem poslužitelju.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">6. Koliko čuvamo podatke</h2>
        <ul class="{{ $lista }}">
            <li>Podatke računa i evidencije čuvamo dok je račun aktivan.</li>
            <li>Nakon <a href="{{ route('brisanje') }}" class="{{ $a }}">brisanja računa</a> podaci se odmah brišu iz aplikacije, a preostali tragovi, npr. u sigurnosnim kopijama i zapisima poslužitelja, najkasnije u roku od 30 dana, osim ako zakon nalaže dulje čuvanje.</li>
            <li>Zapis o pozivu od savjetnika čuvamo dok savjetnik ima račun, među ostalim i da odbijeni poziv ne bi stigao ponovno.</li>
            <li>Zapis o poslanoj ponudi čuvamo da vam ne pišemo ponovno, a adresu koja je odbila poruke da je više ne kontaktiramo.</li>
            <li>Upite čuvamo dok su potrebni za rješavanje, a duže samo ako iz njih nastane poslovni odnos ili to traži zakon.</li>
            <li>Vi ste dužni čuvati svoju evidenciju koliko propisi traže, za sredstva za zaštitu bilja najmanje tri godine. Zato je preuzmite prije brisanja računa.</li>
        </ul>
    </section>

    <section>
        <h2 class="{{ $n }}">7. Sigurnost</h2>
        <p>Koristimo HTTPS, lozinke čuvamo šifrirane, a pristup podacima je ograničen. Nijedan sustav nije potpuno siguran. Ako dođe do povrede podataka koja vas ugrožava, obavijestit ćemo vas i nadležno tijelo kako propisi nalažu.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">8. Vaša prava</h2>
        <p class="mb-3">Imate pravo na pristup, ispravak, brisanje, ograničenje obrade, prenosivost podataka, prigovor na obradu temeljem legitimnog interesa i povlačenje privole. Velik dio možete sami:</p>
        <ul class="{{ $lista }}">
            <li>ispraviti podatke u <strong>Profilu</strong>,</li>
            <li>preuzeti evidenciju pod <strong>Izvještaji</strong> (PDF, Excel, CSV),</li>
            <li>ugasiti obavijesti u <strong>Profil → Obavijesti</strong>,</li>
            <li>obrisati račun u <strong>Profilu</strong>.</li>
        </ul>
        <p class="mt-3">Za ostalo pišite na <a href="mailto:info@agro-log.app" class="{{ $a }}">info@agro-log.app</a>, a odgovorit ćemo u roku od mjesec dana. Možete podnijeti i pritužbu Agenciji za zaštitu osobnih podataka (AZOP, azop.hr).</p>
    </section>

    <section>
        <h2 class="{{ $n }}">9. Maloljetnici</h2>
        <p>AgroLog je namijenjen osobama koje vode ili rade na poljoprivrednom gospodarstvu i nije namijenjen djeci mlađoj od 16 godina.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">10. Izmjene</h2>
        <p>Ako bitno promijenimo ova pravila, obavijestit ćemo vas u aplikaciji ili mailom. Datum zadnje izmjene je na vrhu stranice.</p>
    </section>
@endsection
