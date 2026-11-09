# AgroLog mobilna aplikacija — prototip 0.4

Verzija 0.4 povezuje Android FCM push s postojećim obavijestima. Android konfiguracija za projekt `agrolog-4e3e7` uključena je u APK, serverski dio je objavljen, a Firebase dry-run provjera ovlasti vraća HTTP 200. **Stvarna dostava na mobitel još treba potvrdu.** Nakon instalacije i dopuštenja za obavijesti, u Profil → Obavijesti dostupan je gumb za probnu notifikaciju. Detalji su u [FIREBASE-SETUP.md](FIREBASE-SETUP.md).

Verzija 0.3 dodaje početni splash ekran s postojećim AgroLog logotipom i uklanja gornju traku „probna verzija / Osvježi”. Splash se otpušta kad prvi web ekran postane vidljiv, pri pogrešci ili najkasnije nakon osam sekundi; nema namjernog minimalnog čekanja. Google prijava ostaje ista. Za ovu verziju treba instalirati novi APK, bez novih serverskih promjena.

Android projekt nalazi se u `android/`. Koristi Android WebView za postojeće HTTPS Laravel/Livewire ekrane na `https://agro-log.app/dashboard`. Baza i poslovna logika ostaju na serveru. WebView nema JavaScript most prema Androidu.

## Izgradnja

Otvoriti `android/` u Android Studiju ili koristiti JDK 17+ i Android SDK 36:

```powershell
cd mobile/android
.\gradlew.bat assembleDebug testDebugUnitTest lintDebug
```

APK: `android/app/build/outputs/apk/debug/app-debug.apk`.

Minimalna verzija: Android 10. Identifikator `app.agro_log.mobile` je privremen i treba ga potvrditi prije registracije aplikacije u trgovinama. Probni APK potpisan je razvojnim ključem; nije izdanje za Play Store.

## Uključeno

- Postojeći web ekrani, prijava e-mailom i lozinkom, kolačići sesije. Opcija „Zapamti me” upravlja trajnom prijavom prema postojećem backendu.
- Google prijava kroz vanjski preglednik i povratak gumbom „Vrati se u AgroLog”. Koristi postojeći web Google OAuth client i callback; nema Google tajni u APK-u.
- Android Natrag, osvježavanje, prilagodba rubovima ekrana i tipkovnici.
- Ekran pogreške s ponovnim pokušajem za mrežne pogreške i HTTP 5xx.
- Vanjske HTTPS, telefonske i e-mail poveznice otvaraju se izvan aplikacije.
- Preuzimanje izvještaja s istog izvora kroz Android DownloadManager uz postojeću sesiju.
- Samo HTTPS; nema pristupa lokalnim datotekama ni prihvaćanja nevaljanih TLS certifikata.
- Oznaka `AgroLogNative/<verzija> Android` u User-Agentu za razlikovanje aplikacije od preglednika.

## Objava Google prijave na serveru

Serverski dio 0.2 objavljen je 9. 11. 2026. Migracija je provedena i rute su aktivne. Provjere uživo potvrđuju pripremu mobilne prijave, preusmjeravanje Googleu s postojećim callbackom, zaštitu potpisa i vezivanje povratka uz sesiju. Cijeli tok s pravim Google računom na Androidu još treba potvrditi.

Objava je izvedena izravnim prijenosom ograničenog skupa datoteka, uz kopiju prethodnih datoteka u `/home/agrolog/deploy-backups/mobile-google-20261109T065116Z`. Prije iduće objave preko `git reset --hard origin/main` ove promjene treba uključiti u repozitorij, inače bi se izmijenjene praćene datoteke vratile na staru verziju.

APK 0.2 treba nove serverske datoteke prije nego Google prijava može raditi. E-mail prijava ostaje dostupna. Potrebno je objaviti:

- `app/Http/Controllers/Auth/MobileGoogleController.php`
- `app/Http/Controllers/Auth/SocialAuthController.php`
- `app/Support/MobileGoogleLogin.php`
- `routes/auth.php`
- `resources/views/auth/mobile-google-*.blade.php` (tri predloška)
- `database/migrations/2026_11_09_100000_create_mobile_google_logins.php`

Na serveru nakon objave, iz korijena projekta:

```bash
/opt/cpanel/ea-php83/root/usr/bin/php artisan migrate --path=database/migrations/2026_11_09_100000_create_mobile_google_logins.php --force
/opt/cpanel/ea-php83/root/usr/bin/php artisan route:cache
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:clear
```

Nisu potrebne nove Google konzola postavke ni npm build za ovu promjenu. Postojeći Google client mora imati ispravan callback i raditi na webu.

Sesija WebViewa dobiva serverom generirano slučajno stanje. Potpisana poveznica otvara Google u vanjskom pregledniku. Nakon provjerenog Google callbacka izdaje se jednokratni kod koji vrijedi 60 sekundi; u bazi su samo hash koda i stanja. Povratni `agrolog://auth/google` link sam po sebi nije dovoljan za prijavu: kod se može iskoristiti samo iz izvorne WebView sesije s odgovarajućim stanjem. Kod u HTTPS povratnom koraku ostaje u fragmentu URL-a, a dovršavanje ide kroz običan POST sa CSRF zaštitom. Presretanje custom scheme poveznice drugom aplikacijom može omesti povratak, ali ne daje pristup izvornoj sesiji. Za izdanje u trgovini razmotriti verificirane Android App Links.

Provjeriti na uređaju: postojeći Google račun, novi Google račun, odustajanje, istek povratne poveznice, povratak nakon gašenja procesa aplikacije i obična web Google prijava. Google interakcija sa stvarnim računom nije zamijenjena mock testovima.

## Ograničenja i nastavak

Ovo je tehnički prototip, nije verzija spremna za trgovine. Web promjene za skrivanje PWA instalacijskog poziva vrijede nakon uobičajene objave web projekta; ništa se automatski ne objavljuje.

1. Na uređaju provjeriti prijavu/odjavu i „Zapamti me”, parcele, spremanje prskanja, istek sesije, CSV/XLSX izvoz, mrežne prekide i tipkovnicu. Pregled i izgradnja koda ne potvrđuju ove korisničke tokove.
2. Google prijavu 0.2 aktivirati objavom serverskih datoteka i migracije, zatim provjeriti stvarnim računom na mobitelu.
3. Dodati mobilne push obavijesti. Postojeći Web Push nije mobilna push integracija.
4. GPS dopuštenja, fotografiranje/odabir datoteka, sistemsko dijeljenje i ispis još nisu povezani.
5. Prilagoditi mobilnu navigaciju i testirati sve Livewire obrasce. Nema lokalne pohrane evidencije ni offline unosa.
6. Izraditi iOS aplikaciju i provjeriti na Macu/Xcodeu i stvarnom iPhoneu. iOS projekt još nije izrađen.
7. Za trgovine pripremiti potpisivanje, ikone, slike zaslona, podatke o privatnosti, pregled dopuštenja, testni račun i tražene testne distribucije. Prolazak pregleda nije zajamčen.

## Tehničke reference

- https://developer.android.com/develop/ui/views/layout/webapps/webview
- https://developer.android.com/build/releases/agp-8-13-0-release-notes
- https://developer.apple.com/app-store/review/guidelines/#minimum-functionality
- https://developers.google.com/identity/protocols/oauth2/policies
