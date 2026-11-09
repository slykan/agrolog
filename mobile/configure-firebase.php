<?php

// Server-side only, run by the targeted deploy script. Never outputs private JSON.
$root = $argv[1] ?? '';
if ($root !== '/home/agrolog/agrolog') {
    throw new RuntimeException('Unexpected project root');
}
$key = json_decode(file_get_contents($root.'/storage/app/private/firebase-service-account.json'), true, flags: JSON_THROW_ON_ERROR);
if (($key['type'] ?? '') !== 'service_account' || ($key['project_id'] ?? '') !== 'agrolog-4e3e7' || empty($key['private_key'])) {
    throw new RuntimeException('Invalid Firebase configuration');
}
// The server's .env is protected. config/services.php reads the public project ID
// from this credential file when FIREBASE_PROJECT_ID is not explicitly set.
