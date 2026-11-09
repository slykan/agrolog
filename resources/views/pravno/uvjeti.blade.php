@extends('layouts.pravno')
@section('naslov', 'Uvjeti korištenja')
@section('opis', 'Uvjeti korištenja stranice agro-log.app i aplikacije AgroLog za evidenciju poljoprivrednih radova.')

@section('sadrzaj')
    @php
        $n = 'text-xl font-semibold text-gray-800 mb-3';
        $lista = 'list-disc pl-6 space-y-2';
        $a = 'text-green-700 hover:underline';
    @endphp

    <section>
        <h2 class="{{ $n }}">1. Tko pruža uslugu</h2>
        <p>AgroLog (agro-log.app) pruža Alan Fadljević (u nastavku: „mi”), kontakt <a href="mailto:info@agro-log.app" class="{{ $a }}">info@agro-log.app</a>. Registracijom ili korištenjem usluge prihvaćate ove uvjete. Ako se s njima ne slažete, nemojte koristiti uslugu.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">2. Što je AgroLog</h2>
        <p>AgroLog je alat za vođenje evidencije prskanja, gnojidbe i berbe, parcela i kultura, skladišta, mehanizacije i troškova. Usluga je trenutno besplatna. Ako jednom uvedemo plaćene mogućnosti, unaprijed ćemo vas obavijestiti i nećemo vam ništa naplatiti bez vašeg pristanka.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">3. Vaš račun</h2>
        <ul class="{{ $lista }}">
            <li>Upisujte točne podatke i ažurirajte ih.</li>
            <li>Čuvajte svoju lozinku i ne dijelite račun. Za suradnju s agronomom ili zadrugom koristite <strong>Pristup savjetniku</strong>.</li>
            <li>Odgovorni ste za radnje na svom računu. Ako posumnjate na neovlašten pristup, odmah nam javite.</li>
            <li>Račun možete obrisati u svakom trenutku. Vidi <a href="{{ route('brisanje') }}" class="{{ $a }}">Brisanje računa</a>.</li>
        </ul>
    </section>

    <section>
        <h2 class="{{ $n }}">4. Vaši podaci</h2>
        <p>Podaci koje upisujete ostaju vaši. Koristimo ih samo za pružanje usluge, kako je opisano u <a href="{{ route('privacy') }}" class="{{ $a }}">Pravilima privatnosti</a>. Možete ih preuzeti pod Izvještaji. Preporučujemo da povremeno preuzmete kopiju, posebno evidenciju koju ste dužni čuvati.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">5. Pristup savjetniku</h2>
        <p>Pozvani savjetnik radi u vašem gospodarstvu s pravima koja mu dodijelite. Izmjene se bilježe u povijest, a pristup možete ukinuti u svakom trenutku. Vaš odnos sa savjetnikom stvar je vas i njega, i mi nismo njegova strana. Savjetnik mora imati vaše dopuštenje i podacima se smije koristiti samo za posao koji za vas obavlja.</p>
        <p class="mt-3">Savjetnik može i sam pozvati poljoprivrednika mailom, a pristup nastaje tek kad ga poljoprivrednik prihvati. Oznaku agronoma ili savjetnika upisuje sam korisnik i mi je ne provjeravamo, pa prije prihvaćanja provjerite tko vas poziva. Pozive smijete slati samo osobama s kojima surađujete, ne kao oglašavanje ili masovne poruke. Račun koji to krši možemo ograničiti ili ugasiti.</p>
        <p class="mt-3">Preporuke za prskanje i gnojidbu daje savjetnik i za njih odgovara on, a AgroLog ih samo prenosi. Provjera prema registru je pomoć i ne zamjenjuje stručnu procjenu ni čitanje uputa na etiketi. Poljoprivrednik sam odlučuje hoće li preporuku primijeniti.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">6. Evidencija, provjere i izračuni su pomoć, ne stručni savjet</h2>
        <ul class="{{ $lista }}">
            <li>Provjera prema registru FIS-a, karenca, broj tretmana, dani za prskanje, prognoza, ARKOD podaci, izračuni dušika, zalihe, troškovi i dobit služe kao pomoć. Mogu biti nepotpuni ili zastarjeli, jer ovise o vanjskim izvorima i o onome što upišete.</li>
            <li><strong>Mjerodavni su etiketa sredstva, važeći propisi i službeni podaci.</strong> Prije primjene sredstva i prije berbe provjerite uvjete sami ili sa stručnom osobom.</li>
            <li>Vi ste odgovorni za točnost i potpunost evidencije te za ispunjavanje svojih obveza prema propisima i nadležnim tijelima, uključujući čuvanje i predaju evidencije.</li>
            <li>Podsjetnici i obavijesti mogu kasniti ili ne stići, pa ne zamjenjuju vaše praćenje rokova.</li>
        </ul>
    </section>

    <section>
        <h2 class="{{ $n }}">7. Što nije dopušteno</h2>
        <ul class="{{ $lista }}">
            <li>upisivati tuđe podatke bez ovlaštenja ili neistinite podatke s namjerom da se nekoga dovede u zabludu,</li>
            <li>pokušavati pristupiti tuđim računima ili podacima, ometati rad usluge ili zaobilaziti njezinu zaštitu,</li>
            <li>automatizirano masovno preuzimati sadržaj ili ga kopirati radi izrade konkurentske usluge,</li>
            <li>koristiti uslugu protivno zakonu.</li>
        </ul>
    </section>

    <section>
        <h2 class="{{ $n }}">8. Dostupnost i promjene usluge</h2>
        <p>Nastojimo da usluga radi bez prekida, ali to ne možemo jamčiti, npr. zbog održavanja, kvarova ili ispada vanjskih servisa. Mogućnosti usluge možemo mijenjati. Ako bismo uslugu ili njezin bitan dio trajno ukinuli, unaprijed ćemo vas obavijestiti kako biste stigli preuzeti podatke.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">9. Odgovornost</h2>
        <p>Usluga se pruža besplatno i „takva kakva jest”. U najvećoj mjeri koju zakon dopušta ne odgovaramo za neizravnu štetu, izgubljenu dobit, izgubljene potpore, kazne ili štetu na usjevima koja proizlazi iz korištenja usluge ili oslanjanja na podatke u njoj. To ne ograničava odgovornost za štetu prouzročenu namjerno ili krajnjom nepažnjom, ni prava koja vam kao potrošaču pripadaju po prisilnim propisima.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">10. Sadržaj i oglasi</h2>
        <p>Tekstovi, izgled, logotip i kod AgroLoga zaštićeni su i ne smiju se kopirati bez dopuštenja. Vodiči su opće informacije. U AgroLogu, uključujući početnu stranicu aplikacije, mogu se prikazivati oglasi, označeni kao oglas. Za proizvode i usluge oglašivača odgovara oglašivač, a prikaz oglasa ne znači da ih preporučujemo.</p>
        <p class="mt-3">Prezentaciju na popisu Gospodarstva objavljuje samo gospodarstvo i odgovara za njezin sadržaj: točnost podataka, prava na fotografije i ponudu. Prvu objavu pregledavamo, a prezentaciju koja je netočna, neprimjerena ili protivna zakonu možemo skinuti s popisa. AgroLog ne posreduje u prodaji i nije strana u dogovoru između kupca i gospodarstva.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">11. Prestanak korištenja</h2>
        <p>Možete prestati koristiti uslugu i obrisati račun kad god želite. Ako ozbiljno ili opetovano kršite ove uvjete, možemo ograničiti ili ukinuti vaš račun. Osim kod hitnih slučajeva zaštite sigurnosti, prije toga ćemo vas upozoriti i omogućiti vam da preuzmete podatke.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">12. Izmjene uvjeta</h2>
        <p>Uvjete možemo mijenjati. O bitnim promjenama obavijestit ćemo vas mailom ili u aplikaciji, najmanje 15 dana prije nego stupe na snagu. Ako se s njima ne slažete, možete prestati koristiti uslugu i obrisati račun prije tog datuma.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">13. Mjerodavno pravo i sporovi</h2>
        <p>Na ove uvjete primjenjuje se pravo Republike Hrvatske. Spor ćemo najprije pokušati riješiti dogovorom, pa nam pišite na <a href="mailto:info@agro-log.app" class="{{ $a }}">info@agro-log.app</a>. Ako to ne uspije, nadležan je stvarno nadležni sud u Hrvatskoj, uz prava koja vam kao potrošaču pripadaju po prisilnim propisima.</p>
    </section>
@endsection
