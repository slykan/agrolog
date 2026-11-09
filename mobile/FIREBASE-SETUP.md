# Firebase Cloud Messaging — aktivacija

Serverski dio i Firebase konfiguracija za projekt `agrolog-4e3e7` objavljeni su 9. 11. 2026. HTTP v1 provjera s `validate_only: true` uspješno je vratila HTTP 200; time su provjerene ovlasti bez stvarnog slanja korisnicima. U trenutku provjere nije bilo registriranih mobilnih uređaja. **Prikaz notifikacije i dodir na stvarnom mobitelu još trebaju potvrdu.** APK 0.4 sadrži konfiguraciju za `app.agro_log.mobile`. Sigurnosna kopija Android konfiguracije je u `C:\Users\Korisnik\Desktop\AgroLog-backups\firebase\google-services-2026-11-09-2cb580c8.json`, a privatni serverski ključ ima lokalnu `.backup` kopiju u privatnoj mapi.

Sigurnosna kopija servera prije objave: `/home/agrolog/deploy-backups/mobile-push-20261109T084241Z`. Serverski `.env` zaštićen je od izmjena i ostao je nepromijenjen; konfiguracija čita javni project ID iz privatne service account datoteke, osim kada je `FIREBASE_PROJECT_ID` eksplicitno postavljen. Privatni ključ ostaje u datoteci s dopuštenjima 0600 i ne ulazi u config cache. Izravno objavljene promjene treba uključiti u repozitorij prije sljedeće objave pomoću git reset/pull skripte.

## Firebase konzola

1. Otvoriti https://console.firebase.google.com/ s Google računom koji upravlja Agrologom. Kreirati projekt AgroLog; ako već postoji Google Cloud projekt korišten za Google prijavu, može se razmotriti dodavanje Firebasea tom projektu. Za ovaj kod nisu potrebni Firebase Authentication, Firestore ni Analytics.
2. Dodati **Android aplikaciju** s točnim package nameom `app.agro_log.mobile`. Preuzeti `google-services.json` i spremiti u `mobile/android/app/google-services.json`. Datoteka je ignorirana u Gitu; Gradle plugin iz nje generira javnu konfiguraciju aplikacije. Bez nje se projekt može kompilirati, ali FCM nije inicijaliziran i neće tražiti dopuštenje za obavijesti.
3. U Project settings → Cloud Messaging provjeriti da je Firebase Cloud Messaging API (HTTP v1) omogućen.
4. Pripremiti service account ovlašten za slanje FCM poruka ovom projektu (uloga Firebase Cloud Messaging API Admin / `roles/firebasecloudmessaging.admin`). JSON privatni ključ držati izvan repozitorija i izvan javnog web direktorija. Za ovaj projekt očekivana putanja je `storage/app/private/firebase-service-account.json`. Ključ ne slati u chat niti ugrađivati u APK. Ako se koristi konzolni „Generate new private key”, preuzimanje i pohranu treba obaviti vlasnik računa; za dugoročno izdanje razmotriti autentifikaciju bez dugotrajnog privatnog ključa.

## Server

Najprije objaviti model `MobilePushDevice` i novu migraciju. Pokrenuti samo novu migraciju:

```bash
/opt/cpanel/ea-php83/root/usr/bin/php artisan migrate --path=database/migrations/2026_11_09_120000_create_mobile_push_devices.php --force
```

Postaviti privatni ključ na server, s dopuštenjima 0600 za korisnika `agrolog`. Project ID se zadano čita iz te datoteke; može se nadjačati kroz `FIREBASE_PROJECT_ID`. Ako ključ nije na zadanoj putanji, postaviti `FIREBASE_CREDENTIALS` na apsolutnu putanju. Nijedan privatni ključ nije potreban za izgradnju APK-a.

Nakon migracije objaviti i ove promjene:

- `app/Http/Controllers/MobilePushController.php`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `app/Models/MobilePushDevice.php`, `User.php`, `Obavijest.php`
- `app/Support/MobilePush.php`, `WebPush.php`, `PushUpozorenja.php`
- `config/services.php`, `routes/web.php`
- `resources/views/layouts/app.blade.php`

Osvježiti `config:cache`, `route:cache` i `view:clear` koristeći serverski PHP. Postojeće Google prijave i web push obavijesti zadržane su uz novu mobilnu integraciju.

## Android i provjera

Izgraditi APK nakon dodavanja stvarnog `google-services.json`. Prijaviti se u Agrolog i dopustiti obavijesti kad Android zatraži. Dopuštenje se traži tek na prijavljenom ekranu i samo jednom; nakon odbijanja korisnik ga može promijeniti u postavkama Androida. Nema dodatnog JavaScript mosta prema Androidu.

Postojeći scheduler i poslovi šalju i mobilni push uz postojeći web push: preporuke, admin obavijesti i podržani podsjetnici. Korisnik s isključenim push prekidačem ne prima nove mobilne obavijesti. Tokenski zapisi šifrirani su Laravelovim APP_KEY ključem i nisu uključeni u JSON prikaz modela. Odjava uklanja token vezan uz tu sesiju; drugi uređaji ostaju prijavljeni. Aplikacija odbacuje podatkovne poruke za drugi ili odjavljeni račun.

Uspješnu dostavu FCM API-ju ne poistovjećivati s prikazom na uređaju. Provjeriti prikaz dok je aplikacija otvorena i zatvorena, dodir na obavijest, Android odbijanje dopuštenja, odjavu, drugi račun na istom uređaju i postojeće web push obavijesti. Android prisilno zaustavljanje aplikacije i sustavne/baterijske restrikcije mogu spriječiti ili odgoditi dostavu.

Serverski endpoint `POST /push/mobile/test` šalje probnu obavijest isključivo prijavljenom korisniku; zahtijeva njegovu sesiju, CSRF zaštitu i ima ograničenje učestalosti. Ne koristiti masovnu admin obavijest za provjeru integracije.

## Reference

- https://firebase.google.com/docs/android/setup
- https://firebase.google.com/docs/cloud-messaging/android/get-started
- https://firebase.google.com/docs/cloud-messaging/send/v1-api
