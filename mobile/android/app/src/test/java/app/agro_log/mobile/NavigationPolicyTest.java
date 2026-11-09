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
}
