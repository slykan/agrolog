package app.agro_log.mobile;

import android.Manifest;
import android.app.Notification;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.os.Build;
import com.google.firebase.messaging.FirebaseMessagingService;
import com.google.firebase.messaging.RemoteMessage;
import java.util.Map;

public final class AgroMessagingService extends FirebaseMessagingService {
    @Override public void onMessageReceived(RemoteMessage message) {
        Map<String, String> data = message.getData();
        String user = getSharedPreferences(NativePush.PREFERENCES, Context.MODE_PRIVATE).getString("user", "");
        if (user.isEmpty() || !user.equals(data.get("user_id"))) return;
        if (Build.VERSION.SDK_INT >= 33 && checkSelfPermission(Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) return;
        NotificationManager manager = getSystemService(NotificationManager.class);
        if (!manager.areNotificationsEnabled()) return;
        manager.createNotificationChannel(new NotificationChannel("agrolog-obavijesti", "AgroLog obavijesti", NotificationManager.IMPORTANCE_DEFAULT));
        String url = data.get("url");
        if (!NavigationPolicy.isInternal(url)) url = NavigationPolicy.HOME;
        int id = message.getMessageId() == null ? (int) System.currentTimeMillis() : message.getMessageId().hashCode();
        Intent intent = new Intent(this, MainActivity.class).putExtra("push_url", url)
            .addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_SINGLE_TOP);
        PendingIntent action = PendingIntent.getActivity(this, id, intent, PendingIntent.FLAG_UPDATE_CURRENT | PendingIntent.FLAG_IMMUTABLE);
        manager.notify(id, new Notification.Builder(this, "agrolog-obavijesti")
            .setSmallIcon(R.drawable.ic_notification).setContentTitle(data.getOrDefault("naslov", "AgroLog"))
            .setContentText(data.getOrDefault("tekst", "Nova obavijest"))
            .setStyle(new Notification.BigTextStyle().bigText(data.getOrDefault("tekst", "Nova obavijest")))
            .setVisibility(Notification.VISIBILITY_PRIVATE).setAutoCancel(true).setContentIntent(action).build());
    }
}
