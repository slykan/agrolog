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

    private NavigationPolicy() {}
}
