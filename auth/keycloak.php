<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Security.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../services/OidcService.php';

Security::startSession();

if (isset($_SESSION['user_id'])) {
    header('Location: /gestion_evenements/');
    exit;
}

try {
    $identity = (new OidcService())->authenticate();
    $user = (new User())->loginWithOidc($identity);

    Security::establishIdentity($user, 'oidc', [
        'oidc_id_token' => $identity['id_token'],
        'oidc_subject' => $identity['sub'],
    ]);

    header('Location: /gestion_evenements/' . ($user['role'] === 'admin' ? 'admin/' : 'user/'));
    exit;
} catch (Throwable $e) {
    error_log('EventSecure OIDC login: ' . $e->getMessage());
    header('Location: /gestion_evenements/login.php?iam_error=1');
    exit;
}
