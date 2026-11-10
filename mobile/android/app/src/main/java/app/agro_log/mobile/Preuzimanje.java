package app.agro_log.mobile;

import android.app.Activity;
import android.content.ActivityNotFoundException;
import android.content.ContentResolver;
import android.content.ContentValues;
import android.content.Intent;
import android.net.Uri;
import android.os.Environment;
import android.provider.MediaStore;
import android.webkit.CookieManager;
import android.webkit.URLUtil;
import android.widget.Toast;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

/**
 * Preuzimanje izvještaja (PDF, CSV, XLSX) s agro-log.app uz prijavu iz WebViewa: datoteka se spremi u
 * Preuzimanja/AgroLog i odmah otvori. Za razliku od DownloadManagera korisnik odmah vidi rezultat ili grešku.
 */
final class Preuzimanje {
    private static final ExecutorService POZADINA = Executors.newSingleThreadExecutor();
    private final Activity activity;

    Preuzimanje(Activity activity) { this.activity = activity; }

    void preuzmi(String url, String userAgent, String disposition, String mime) {
        if (!NavigationPolicy.isInternal(url)) { toast("Ovo preuzimanje nije podržano."); return; }
        toast("Preuzimam…");
        String cookie = CookieManager.getInstance().getCookie(url);
        POZADINA.execute(() -> {
            HttpURLConnection veza = null;
            try {
                veza = (HttpURLConnection) new URL(url).openConnection();
                veza.setInstanceFollowRedirects(false);   // preusmjeravanje = istekla prijava (vodi na /login)
                veza.setConnectTimeout(15000);
                veza.setReadTimeout(60000);
                if (cookie != null) veza.setRequestProperty("Cookie", cookie);
                veza.setRequestProperty("User-Agent", userAgent);
                int status = veza.getResponseCode();
                String tip = veza.getContentType() == null ? "" : veza.getContentType().split(";")[0].trim();
                if (status >= 300 && status < 400) { naUi(() -> toast("Prijava je istekla. Prijavite se ponovno pa pokušajte opet.")); return; }
                if (status != 200 || tip.startsWith("text/html")) { naUi(() -> toast("Preuzimanje nije uspjelo (" + status + "). Pokušajte ponovno.")); return; }

                String vrsta = tip.isEmpty() ? (mime == null ? "application/octet-stream" : mime) : tip;
                String ime = URLUtil.guessFileName(url, veza.getHeaderField("Content-Disposition") != null
                    ? veza.getHeaderField("Content-Disposition") : disposition, vrsta);
                Uri datoteka = spremi(veza.getInputStream(), ime, vrsta);
                naUi(() -> otvori(datoteka, vrsta, ime));
            } catch (Exception e) {
                naUi(() -> toast("Preuzimanje nije uspjelo. Provjerite internetsku vezu i pokušajte ponovno."));
            } finally {
                if (veza != null) veza.disconnect();
            }
        });
    }

    /** Sprema u Preuzimanja/AgroLog preko MediaStorea (Android 10+, bez dopuštenja za pohranu). */
    private Uri spremi(InputStream ulaz, String ime, String vrsta) throws Exception {
        ContentResolver resolver = activity.getContentResolver();
        ContentValues podaci = new ContentValues();
        podaci.put(MediaStore.Downloads.DISPLAY_NAME, ime);
        podaci.put(MediaStore.Downloads.MIME_TYPE, vrsta);
        podaci.put(MediaStore.Downloads.RELATIVE_PATH, Environment.DIRECTORY_DOWNLOADS + "/AgroLog");
        podaci.put(MediaStore.Downloads.IS_PENDING, 1);
        Uri uri = resolver.insert(MediaStore.Downloads.EXTERNAL_CONTENT_URI, podaci);
        if (uri == null) throw new IllegalStateException("MediaStore");
        try (InputStream in = ulaz; OutputStream out = resolver.openOutputStream(uri)) {
            if (out == null) throw new IllegalStateException("izlaz");
            byte[] buffer = new byte[16384];
            for (int n; (n = in.read(buffer)) != -1; ) out.write(buffer, 0, n);
        } catch (Exception e) {
            resolver.delete(uri, null, null);
            throw e;
        }
        podaci.clear();
        podaci.put(MediaStore.Downloads.IS_PENDING, 0);
        resolver.update(uri, podaci, null, null);
        return uri;
    }

    private void otvori(Uri datoteka, String vrsta, String ime) {
        Intent otvori = new Intent(Intent.ACTION_VIEW).setDataAndType(datoteka, vrsta)
            .addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION | Intent.FLAG_ACTIVITY_NEW_TASK);
        try {
            activity.startActivity(otvori);
            toast("Spremljeno u Preuzimanja/AgroLog: " + ime);
        } catch (ActivityNotFoundException e) {
            toast("Spremljeno u Preuzimanja/AgroLog: " + ime + ". Za otvaranje treba aplikacija za ovu vrstu datoteke.");
        }
    }

    private void naUi(Runnable r) { if (!activity.isDestroyed()) activity.runOnUiThread(r); }
    private void toast(String poruka) { Toast.makeText(activity, poruka, Toast.LENGTH_LONG).show(); }
}
