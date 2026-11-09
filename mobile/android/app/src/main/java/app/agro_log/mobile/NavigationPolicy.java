package app.agro_log.mobile;

import java.net.URI;

/** Keep authenticated navigation inside the exact HTTPS origin. */
public final class NavigationPolicy {
    public static final String ORIGIN = "https://agro-log.app";
    public static final String HOME = ORIGIN + "/dashboard";

    public static boolean isInternal(String value) {
        try {
            URI uri = URI.create(value);
            return "https".equalsIgnoreCase(uri.getScheme())
                && "agro-log.app".equalsIgnoreCase(uri.getHost())
                && (uri.getPort() == -1 || uri.getPort() == 443)
                && uri.getRawUserInfo() == null;
        } catch (RuntimeException e) { return false; }
    }

    /** Adresa koju web (od 0.5) otvara umjesto window.print(); aplikacija je presretne i pokrene Androidov ispis. */
    public static boolean isPrintRequest(String value) {
        if (!isInternal(value)) return false;
        return "/__aplikacija/ispis".equals(URI.create(value).getPath());
    }

    /**
     * Vanjske poveznice koje smiju otvoriti druge aplikacije: preglednik, mail, telefon, SMS, Viber, WhatsApp, karta.
     * Nikad intent://, javascript:, file:, content: ni data: (mogli bi pokrenuti tuđe komponente ili lokalne datoteke).
     */
    public static boolean isAllowedExternal(String value) {
        if (value == null) return false;
        try {
            String scheme = URI.create(value).getScheme();
            if (scheme == null) return false;
            switch (scheme.toLowerCase(java.util.Locale.ROOT)) {
                case "https": case "mailto": case "tel": case "sms": case "viber": case "whatsapp": case "geo":
                    return true;
                default:
                    return false;
            }
        } catch (RuntimeException e) { return false; }
    }

    private NavigationPolicy() {}
}
