<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];
$contains = static function (string $file, string $needle) use (&$failures, $root): void {
    $contents = file_get_contents($root . DIRECTORY_SEPARATOR . $file);
    if ($contents === false || strpos($contents, $needle) === false) {
        $failures[] = "$file does not contain expected protection: $needle";
    }
};
$notContains = static function (string $file, string $needle) use (&$failures, $root): void {
    $contents = file_get_contents($root . DIRECTORY_SEPARATOR . $file);
    if ($contents !== false && strpos($contents, $needle) !== false) {
        $failures[] = "$file still contains unsafe pattern: $needle";
    }
};
$contains('classes/Security.php', 'hash_equals');
$contains('classes/Security.php', "'httponly' => true");
$contains('classes/Security.php', "'samesite' => 'Lax'");
$contains('config/database.php', 'EVENTSECURE_DB_USER');
$contains('classes/ImageUpload.php', 'FILEINFO_MIME_TYPE');
$contains('assets/chatbot.js', 'textContent');
$notContains('assets/chatbot.js', 'innerHTML +=');
$contains('admin/supprimer_evenement.php', 'requirePostCsrf');
$contains('user/mes_inscriptions.php', 'requirePostCsrf');
$diagnosticUpgrade = $root . DIRECTORY_SEPARATOR . 'diagnostic/db_upgrade_tickets.php';
if (is_file($diagnosticUpgrade)) {
    $contains('diagnostic/db_upgrade_tickets.php', "PHP_SAPI !== 'cli'");
}
$contains('scripts/migrate_oidc.php', "PHP_SAPI !== 'cli'");
$contains('scripts/generate_keycloak_env.php', "PHP_SAPI !== 'cli'");
$contains('services/OidcService.php', "setCodeChallengeMethod('S256')");
$contains('classes/User.php', "empty(\$identity['email_verified'])");
$contains('auth/keycloak.php', 'Security::establishIdentity');
if ($failures) { fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL); exit(1); }
echo "Security smoke tests passed\n";
