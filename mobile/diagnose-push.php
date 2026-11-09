<?php

// CLI diagnostics; never prints registration tokens or credentials.
$root = '/home/agrolog/agrolog';
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (! Illuminate\Support\Facades\Schema::hasTable('mobile_push_devices')) {
    echo json_encode(['devices_table' => false]).PHP_EOL;
    exit(1);
}
$devices = App\Models\MobilePushDevice::orderByDesc('updated_at')->limit(10)->get();
echo json_encode([
    'configured' => App\Support\MobilePush::configured(),
    'total' => App\Models\MobilePushDevice::count(),
    'devices' => $devices->map(function ($device) {
        $user = App\Models\User::find($device->user_id);
        return ['id' => $device->id, 'user_id' => $device->user_id,
            'name' => $user?->name, 'admin' => $user?->isAdmin(), 'enabled' => $user?->push_obavijesti,
            'updated_at' => $device->updated_at->toIso8601String()];
    }),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL;
if (($argv[1] ?? '') === 'send') {
    $device = App\Models\MobilePushDevice::findOrFail((int) ($argv[2] ?? 0));
    $user = App\Models\User::findOrFail($device->user_id);
    if (! $user->push_obavijesti) throw new RuntimeException('User has disabled push');
    $method = new ReflectionMethod(App\Support\MobilePush::class, 'send');
    $accepted = $method->invoke(null, $device, 'AgroLog · probna obavijest',
        'Ako vidite ovu poruku, push obavijesti na vašem mobitelu rade.', route('dashboard'));
    echo json_encode(['target_device' => $device->id, 'accepted_by_firebase' => $accepted]).PHP_EOL;
    exit($accepted ? 0 : 1);
}
