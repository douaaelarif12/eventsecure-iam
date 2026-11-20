<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$target = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env.keycloak';
if (is_file($target)) {
    fwrite(STDERR, ".env.keycloak already exists; it was not overwritten.\n");
    exit(1);
}

$password = rtrim(strtr(base64_encode(random_bytes(36)), '+/', '-_'), '=');
$content = "KEYCLOAK_ADMIN_USERNAME=eventsecure-admin\n"
    . "KEYCLOAK_ADMIN_PASSWORD={$password}\n";

if (file_put_contents($target, $content, LOCK_EX) === false) {
    throw new RuntimeException('Unable to create .env.keycloak.');
}

echo ".env.keycloak created with a random administrator password.\n";
