<?php
declare(strict_types=1);

require_once __DIR__ . '/classes/Security.php';
require_once __DIR__ . '/config/IamConfig.php';

Security::startSession();
Security::requirePostCsrf();

$authMethod = (string) ($_SESSION['auth_method'] ?? 'local');
$idToken = (string) ($_SESSION['oidc_id_token'] ?? '');
Security::destroySession();

if ($authMethod === 'oidc' && $idToken !== '' && IamConfig::enabled()) {
    try {
        require_once __DIR__ . '/services/OidcService.php';
        (new OidcService())->signOut($idToken);
    } catch (Throwable $e) {
        error_log('EventSecure OIDC logout: ' . $e->getMessage());
    }
}

header('Location: /gestion_evenements/');
exit;
