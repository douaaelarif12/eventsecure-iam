<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];
$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        $failures[] = $message;
    }
};

require_once $root . '/config/IamConfig.php';
putenv('EVENTSECURE_IAM_ENABLED=true');
putenv('EVENTSECURE_LOCAL_LOGIN_ENABLED=false');
putenv('EVENTSECURE_BASE_URL=http://localhost/gestion_evenements');
putenv('EVENTSECURE_OIDC_ISSUER=http://localhost:8081/realms/eventsecure');
putenv('EVENTSECURE_OIDC_CLIENT_ID=eventsecure-web');

$assert(IamConfig::enabled(), 'IAM should be enabled from the environment.');
$assert(!IamConfig::localLoginEnabled(), 'Local login toggle should be disabled.');
$assert(IamConfig::redirectUri() === 'http://localhost/gestion_evenements/auth/keycloak.php', 'Unexpected redirect URI.');

$realmPath = $root . '/docker/keycloak/eventsecure-realm.json';
$realm = json_decode((string) file_get_contents($realmPath), true);
$assert(is_array($realm), 'Keycloak realm JSON is invalid.');
$assert(($realm['realm'] ?? '') === 'eventsecure', 'Unexpected Keycloak realm name.');
$assert(($realm['bruteForceProtected'] ?? false) === true, 'Brute-force protection is not enabled.');

$client = $realm['clients'][0] ?? [];
$assert(($client['clientId'] ?? '') === 'eventsecure-web', 'OIDC client is missing.');
$assert(($client['publicClient'] ?? false) === true, 'Local OIDC client should use public-client PKCE mode.');
$assert(($client['directAccessGrantsEnabled'] ?? true) === false, 'Password grant must stay disabled.');
$assert(($client['attributes']['pkce.code.challenge.method'] ?? '') === 'S256', 'PKCE S256 is not enforced.');

$totpActions = array_values(array_filter(
    $realm['requiredActions'] ?? [],
    static fn (array $action): bool => ($action['alias'] ?? '') === 'CONFIGURE_TOTP'
));
$assert(isset($totpActions[0]) && ($totpActions[0]['enabled'] ?? false) === true, 'TOTP is not enabled.');
$assert(isset($totpActions[0]) && ($totpActions[0]['defaultAction'] ?? false) === true, 'TOTP is not required for new users.');

if ($failures) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

echo "IAM smoke tests passed\n";
