package app.agro_log.mobile;

import android.app.Activity;
import android.app.DownloadManager;
import android.content.ActivityNotFoundException;
import android.content.Intent;
import android.graphics.Color;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.os.Handler;
import android.os.Looper;
import android.view.Gravity;
import android.view.View;
import android.webkit.CookieManager;
import android.webkit.URLUtil;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

public class MainActivity extends Activity {
    private WebView web;
    private LinearLayout error;
    private ProgressBar progress;
    private String retryUrl = NavigationPolicy.HOME;
    private boolean failed;
    private NativePush nativePush;
    private boolean splashReady;
    private final Handler launchHandler = new Handler(Looper.getMainLooper());
    private final Runnable releaseSplash = () -> splashReady = true;

    @Override public void onCreate(Bundle state) {
        setTheme(R.style.AppTheme);
        super.onCreate(state);
        nativePush = new NativePush(this);
        LinearLayout root = new LinearLayout(this);
        root.setOrientation(LinearLayout.VERTICAL);
        root.setBackgroundColor(Color.WHITE);
        if (android.os.Build.VERSION.SDK_INT >= 30) {
            root.setOnApplyWindowInsetsListener((view, insets) -> {
                android.graphics.Insets bars = insets.getInsets(
                    android.view.WindowInsets.Type.systemBars() | android.view.WindowInsets.Type.ime());
                view.setPadding(bars.left, bars.top, bars.right, bars.bottom);
                return insets;
            });
        } else {
            root.setOnApplyWindowInsetsListener((view, insets) -> {
                view.setPadding(insets.getSystemWindowInsetLeft(), insets.getSystemWindowInsetTop(),
                    insets.getSystemWindowInsetRight(), insets.getSystemWindowInsetBottom());
                return insets;
            });
        }
        progress = new ProgressBar(this, null, android.R.attr.progressBarStyleHorizontal);
        progress.setIndeterminate(true);
        root.addView(progress, new LinearLayout.LayoutParams(-1, 5));

        error = new LinearLayout(this);
        error.setOrientation(LinearLayout.VERTICAL);
        error.setGravity(Gravity.CENTER);
        error.setPadding(32, 32, 32, 32);
        TextView message = new TextView(this);
        message.setText("AgroLog trenutno nije dostupan.\nProvjerite internetsku vezu i pokušajte ponovno.\n\nUnos bez interneta još nije dostupan.");
        message.setGravity(Gravity.CENTER);
        error.addView(message);
        Button retry = new Button(this);
        retry.setText("Pokušaj ponovno");
        retry.setOnClickListener(v -> reload());
        error.addView(retry);
        error.setVisibility(View.GONE);
        root.addView(error, new LinearLayout.LayoutParams(-1, 0, 1));

        web = new WebView(this);
        root.addView(web, new LinearLayout.LayoutParams(-1, 0, 1));
        setContentView(root);
        // Keep the system launch screen until the first web screen is ready.
        // A bounded wait also releases it on slow connections and restored activities.
        root.getViewTreeObserver().addOnPreDrawListener(new android.view.ViewTreeObserver.OnPreDrawListener() {
            @Override public boolean onPreDraw() {
                if (!splashReady) return false;
                root.getViewTreeObserver().removeOnPreDrawListener(this);
                return true;
            }
        });
        launchHandler.postDelayed(releaseSplash, 8000);
        WebSettings settings = web.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setAllowFileAccess(false);
        settings.setAllowContentAccess(false);
        settings.setMixedContentMode(WebSettings.MIXED_CONTENT_NEVER_ALLOW);
        settings.setUserAgentString(settings.getUserAgentString() + " AgroLogNative/" + BuildConfig.VERSION_NAME + " Android");
        CookieManager.getInstance().setAcceptCookie(true);
        CookieManager.getInstance().setAcceptThirdPartyCookies(web, false);
        WebView.setWebContentsDebuggingEnabled(BuildConfig.DEBUG);
        web.setWebChromeClient(new android.webkit.WebChromeClient());
        web.setWebViewClient(new WebViewClient() {
            @Override public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                Uri uri = request.getUrl();
                if (!request.isForMainFrame()) return !NavigationPolicy.isInternal(uri.toString());
                if (NavigationPolicy.isInternal(uri.toString())) {
                    if ("/auth/google".equals(uri.getPath())) {
                        Uri prepare = Uri.parse(NavigationPolicy.ORIGIN + "/auth/mobile/google").buildUpon()
                            .appendQueryParameter("savjetnik", uri.getQueryParameter("savjetnik"))
                            .appendQueryParameter("prezentacija", uri.getQueryParameter("prezentacija")).build();
                        web.loadUrl(prepare.toString());
                        return true;
                    }
                    if ("/auth/mobile/google/launch".equals(uri.getPath())) {
                        try { startActivity(new Intent(Intent.ACTION_VIEW, uri).addCategory(Intent.CATEGORY_BROWSABLE)); }
                        catch (ActivityNotFoundException e) { toast("Za Google prijavu potreban je preglednik."); }
                        return true;
                    }
                    return false;
                }
                if ("https".equals(uri.getScheme()) || "mailto".equals(uri.getScheme()) || "tel".equals(uri.getScheme())) {
                    try { startActivity(new Intent(Intent.ACTION_VIEW, uri).addCategory(Intent.CATEGORY_BROWSABLE)); }
                    catch (ActivityNotFoundException e) { toast("Nema aplikacije za otvaranje poveznice."); }
                }
                return true;
            }
            @Override public void onPageStarted(WebView view, String url, android.graphics.Bitmap icon) {
                failed = false;
                if (NavigationPolicy.isInternal(url)) retryUrl = url;
                progress.setVisibility(View.VISIBLE);
                error.setVisibility(View.GONE);
                web.setVisibility(View.VISIBLE);
            }
            @Override public void onPageFinished(WebView view, String url) {
                finishSplash();
                progress.setVisibility(View.GONE);
                CookieManager.getInstance().flush();
                nativePush.sync(web);
                if (failed) showError();
            }
            @Override public void onPageCommitVisible(WebView view, String url) {
                finishSplash();
            }
            @Override public void onReceivedError(WebView view, WebResourceRequest request, WebResourceError reason) {
                if (request.isForMainFrame()) showError();
            }
            @Override public void onReceivedHttpError(WebView view, WebResourceRequest request, WebResourceResponse response) {
                if (request.isForMainFrame() && response.getStatusCode() >= 500) showError();
            }
        });
        web.setDownloadListener((url, userAgent, disposition, mime, length) -> {
            if (!NavigationPolicy.isInternal(url)) { toast("Ovo preuzimanje nije podržano."); return; }
            try {
                DownloadManager.Request request = new DownloadManager.Request(Uri.parse(url));
                String cookie = CookieManager.getInstance().getCookie(url);
                if (cookie != null) request.addRequestHeader("Cookie", cookie);
                request.addRequestHeader("User-Agent", userAgent);
                if (mime != null) request.setMimeType(mime);
                request.setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED);
                request.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS,
                    URLUtil.guessFileName(url, disposition, mime));
                ((DownloadManager) getSystemService(DOWNLOAD_SERVICE)).enqueue(request);
                toast("Izvještaj se preuzima u Preuzimanja.");
            } catch (RuntimeException e) { toast("Preuzimanje nije uspjelo. Pokušajte ponovno."); }
        });
        if (android.os.Build.VERSION.SDK_INT >= 33) {
            getOnBackInvokedDispatcher().registerOnBackInvokedCallback(0, this::goBack);
        }
        if (!handleGoogleReturn(getIntent()) && !handlePush(getIntent())) {
            if (state == null || web.restoreState(state) == null) web.loadUrl(NavigationPolicy.HOME);
        }
    }

    private void toast(String message) { Toast.makeText(this, message, Toast.LENGTH_LONG).show(); }
    @Override protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        if (!handleGoogleReturn(intent)) handlePush(intent);
    }
    private boolean handlePush(Intent intent) {
        String url = intent.getStringExtra("push_url");
        intent.removeExtra("push_url");
        if (url == null) return false;
        web.loadUrl(NavigationPolicy.isInternal(url) ? url : NavigationPolicy.HOME);
        return true;
    }
    @Override public void onRequestPermissionsResult(int requestCode, String[] permissions, int[] results) {
        super.onRequestPermissionsResult(requestCode, permissions, results);
        if (requestCode == 44) nativePush.sync(web);
    }
    @Override protected void onResume() {
        super.onResume();
        if (web != null) nativePush.sync(web);
    }
    private boolean handleGoogleReturn(Intent intent) {
        Uri uri = intent.getData();
        if (uri == null || !"agrolog".equals(uri.getScheme()) || !"auth".equals(uri.getHost())
            || !"/google".equals(uri.getPath())) return false;
        // Do not replay a consumed callback on Activity recreation or keep it in saved state.
        intent.setData(null);
        String code = uri.getQueryParameter("code"), state = uri.getQueryParameter("state");
        if (code == null || state == null || !code.matches("[a-f0-9]{64}") || !state.matches("[a-f0-9]{64}")) {
            toast("Google prijava nije dovršena. Pokušajte ponovno.");
            web.loadUrl(NavigationPolicy.ORIGIN + "/login");
            return true;
        }
        // Server validates the one-time code against the original WebView session.
        // Fragment values never enter HTTP request URLs or access logs.
        web.loadUrl(NavigationPolicy.ORIGIN + "/auth/mobile/google/return#code=" + code + "&state=" + state);
        return true;
    }
    private void showError() {
        finishSplash();
        failed = true;
        progress.setVisibility(View.GONE);
        web.setVisibility(View.GONE);
        error.setVisibility(View.VISIBLE);
    }
    private void finishSplash() {
        splashReady = true;
        launchHandler.removeCallbacks(releaseSplash);
    }
    private void reload() { web.loadUrl(retryUrl); }
    private void goBack() { if (web.canGoBack()) web.goBack(); else finish(); }
    // Legacy fallback for API 29–32. API 33+ registers OnBackInvokedCallback above.
    @android.annotation.SuppressLint("GestureBackNavigation")
    @Override public void onBackPressed() { goBack(); }
    @Override protected void onSaveInstanceState(Bundle state) { super.onSaveInstanceState(state); web.saveState(state); }
    @Override protected void onPause() { CookieManager.getInstance().flush(); super.onPause(); }
    @Override protected void onDestroy() { launchHandler.removeCallbacks(releaseSplash); web.destroy(); super.onDestroy(); }
}
