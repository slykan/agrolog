package app.agro_log.mobile;

import org.junit.Test;
import static org.junit.Assert.*;

public class NavigationPolicyTest {
    @Test public void acceptsOnlyExactSecureOrigin() {
        assertTrue(NavigationPolicy.isInternal("https://agro-log.app/dashboard"));
        assertTrue(NavigationPolicy.isInternal("https://agro-log.app:443/prskanje/izvoz.xlsx?godina=2026"));
        String[] rejected = {
            "http://agro-log.app/dashboard", "https://agro-log.app.evil.test/",
            "https://evil.test/?next=https://agro-log.app", "https://agro-log.app@evil.test/",
            "https://evil@agro-log.app/", "https://agro-log.app:444/",
            "javascript:alert(1)", "file:///data/data/app.agro_log.mobile/", "intent://agro-log.app/",
            "/dashboard", "not a url", "", null
        };
        for (String url : rejected) assertFalse(String.valueOf(url), NavigationPolicy.isInternal(url));
    }

    @Test public void printRequestOnlyOnOwnOrigin() {
        assertTrue(NavigationPolicy.isPrintRequest("https://agro-log.app/__aplikacija/ispis"));
        assertFalse(NavigationPolicy.isPrintRequest("https://evil.test/__aplikacija/ispis"));
        assertFalse(NavigationPolicy.isPrintRequest("https://agro-log.app/prskanje"));
    }

    @Test public void externalSchemesAreAllowListed() {
        String[] allowed = {"https://wa.me/385912345678", "viber://chat?number=%2B385912345678", "whatsapp://send?phone=385",
            "tel:+385912345678", "mailto:info@agro-log.app", "sms:+385912345678", "geo:45.5,18.7"};
        for (String url : allowed) assertTrue(url, NavigationPolicy.isAllowedExternal(url));
        String[] rejected = {"intent://scan/#Intent;scheme=zxing;end", "javascript:alert(1)", "file:///sdcard/x",
            "content://x/y", "data:text/html,x", "http://agro-log.app/", "market://details?id=x", "", null};
        for (String url : rejected) assertFalse(String.valueOf(url), NavigationPolicy.isAllowedExternal(url));
    }
}
