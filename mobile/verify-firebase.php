<?php

// Dry-run only: validates Firebase authorization without delivering to any device.
$root = $argv[1] ?? '';
if ($root !== '/home/agrolog/agrolog') {
    throw new RuntimeException('Unexpected project root');
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (! App\Support\MobilePush::configured()) {
    throw new RuntimeException('Firebase is not configured');
}
$method = new ReflectionMethod(App\Support\MobilePush::class, 'accessToken');
$token = $method->invoke(null);
$project = config('services.firebase.project_id');
$response = Illuminate\Support\Facades\Http::timeout(15)->withToken($token)->post(
    'https://fcm.googleapis.com/v1/projects/'.$project.'/messages:send', [
        'validate_only' => true,
        'message' => ['topic' => 'agrolog-integration-check', 'data' => ['check' => 'configuration-only']],
    ]);
echo json_encode([
    'configured' => true, 'project' => $project, 'status' => $response->status(),
    'valid' => $response->successful(), 'error_code' => $response->json('error.status'),
    'devices' => App\Models\MobilePushDevice::count(),
], JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($response->successful() ? 0 : 1);
