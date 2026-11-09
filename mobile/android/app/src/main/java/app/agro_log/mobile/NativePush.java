package app.agro_log.mobile;

import android.Manifest;
import android.app.Activity;
import android.app.NotificationManager;
import android.content.Context;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.os.Build;
import android.webkit.CookieManager;
import android.webkit.WebView;
import com.google.firebase.FirebaseApp;
import com.google.firebase.messaging.FirebaseMessaging;
import org.json.JSONArray;
import org.json.JSONObject;
import java.net.HttpURLConnection;
import java.net.URL;
import java.nio.charset.StandardCharsets;
import java.util.concurrent.CompletableFuture;

/** Synchronizes only the current, authenticated WebView account; no JS-to-native bridge. */
final class NativePush {
    static final String PREFERENCES = "agrolog-native-push";
    private final Activity activity;
    private final SharedPreferences preferences;
    private String registered = "";
    private boolean registering;

    NativePush(Activity activity) {
        this.activity = activity;
        preferences = activity.getSharedPreferences(PREFERENCES, Context.MODE_PRIVATE);
    }

    void sync(WebView web) {
        if (!NavigationPolicy.isInternal(web.getUrl())) return;
        web.evaluateJavascript("(()=>{const m=document.querySelector('meta[name=agro-native-push]');"
            + "const c=document.querySelector('meta[name=csrf-token]');"
            + "return m&&c?[m.dataset.user,m.dataset.enabled,c.content]:null})()", result -> {
            if (activity.isDestroyed() || !NavigationPolicy.isInternal(web.getUrl())) return;
            try {
                if ("null".equals(result)) { clear(); return; }
                JSONArray values = new JSONArray(result);
                String user = values.getString(0), csrf = values.getString(2);
                if (!"1".equals(values.getString(1)) || !user.matches("[0-9]+")) { clear(); return; }
                if (!user.equals(preferences.getString("user", ""))) {
                    clear();
                    preferences.edit().putString("user", user).apply();
                }
                if (FirebaseApp.getApps(activity).isEmpty()) return;
                NotificationManager manager = activity.getSystemService(NotificationManager.class);
                if (Build.VERSION.SDK_INT >= 33 && activity.checkSelfPermission(Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) {
                    if (!preferences.getBoolean("permissionAsked", false)) {
                        preferences.edit().putBoolean("permissionAsked", true).apply();
                        activity.requestPermissions(new String[]{Manifest.permission.POST_NOTIFICATIONS}, 44);
                    }
                    return;
                }
                if (!manager.areNotificationsEnabled()) return;
                FirebaseMessaging.getInstance().setAutoInitEnabled(true);
                FirebaseMessaging.getInstance().getToken().addOnSuccessListener(activity, token -> {
                    String signature = user + ":" + csrf + ":" + token;
                    if (signature.equals(registered) || registering || !user.equals(preferences.getString("user", ""))) return;
                    String cookie = CookieManager.getInstance().getCookie(NavigationPolicy.ORIGIN);
                    if (cookie == null) return;
                    registering = true;
                    CompletableFuture.supplyAsync(() -> register(token, csrf, cookie)).thenAccept(ok -> activity.runOnUiThread(() -> {
                        registering = false;
                        if (ok && user.equals(preferences.getString("user", ""))) registered = signature;
                    }));
                });
            } catch (org.json.JSONException ignored) {
                clear();
            }
        });
    }

    private boolean register(String token, String csrf, String cookie) {
        HttpURLConnection connection = null;
        try {
            connection = (HttpURLConnection) new URL(NavigationPolicy.ORIGIN + "/push/mobile/device").openConnection();
            connection.setRequestMethod("POST");
            connection.setInstanceFollowRedirects(false);
            connection.setConnectTimeout(10000);
            connection.setReadTimeout(10000);
            connection.setDoOutput(true);
            connection.setRequestProperty("Cookie", cookie);
            connection.setRequestProperty("X-CSRF-TOKEN", csrf);
            connection.setRequestProperty("Accept", "application/json");
            connection.setRequestProperty("Origin", NavigationPolicy.ORIGIN);
            connection.setRequestProperty("Content-Type", "application/json");
            byte[] data = new JSONObject().put("token", token).toString().getBytes(StandardCharsets.UTF_8);
            try (java.io.OutputStream output = connection.getOutputStream()) { output.write(data); }
            return connection.getResponseCode() == 200;
        } catch (Exception ignored) { return false; }
        finally { if (connection != null) connection.disconnect(); }
    }

    private void clear() {
        if (preferences.contains("user")) {
            preferences.edit().remove("user").apply();
            activity.getSystemService(NotificationManager.class).cancelAll();
        }
        registered = "";
    }
}
