<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli' || getenv('EVENTSECURE_RUN_DB_INTEGRATION') !== '1') {
    fwrite(STDERR, "Set EVENTSECURE_RUN_DB_INTEGRATION=1 to run this local database test.\n");
    exit(2);
}

require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../config/database.php';

$subject = 'integration-' . bin2hex(random_bytes(12));
$email = 'iam.integration.' . bin2hex(random_bytes(6)) . '@example.test';
$pdo = DatabaseConfig::getConnection();

try {
    $created = (new User())->loginWithOidc([
        'sub' => $subject,
        'email' => $email,
        'email_verified' => true,
        'nom' => 'Integration',
        'prenom' => 'IAM',
        'role' => 'user',
    ]);

    if (($created['email'] ?? '') !== $email
        || ($created['oidc_subject'] ?? '') !== $subject
        || ($created['identity_provider'] ?? '') !== 'keycloak'
        || ($created['role'] ?? '') !== 'user') {
        throw new RuntimeException('The provisioned OIDC account does not match the verified identity.');
    }

    $reloaded = (new User())->loginWithOidc([
        'sub' => $subject,
        'email' => $email,
        'email_verified' => true,
        'nom' => 'Integration',
        'prenom' => 'IAM',
        'role' => 'admin',
    ]);
    if (($reloaded['id'] ?? null) !== ($created['id'] ?? null) || ($reloaded['role'] ?? '') !== 'admin') {
        throw new RuntimeException('OIDC identity reuse or role synchronization failed.');
    }

    echo "IAM database integration passed\n";
} finally {
    $cleanup = $pdo->prepare(
        "DELETE FROM users
         WHERE identity_provider = 'keycloak'
           AND oidc_subject = :subject
           AND email = :email
           AND email LIKE 'iam.integration.%@example.test'"
    );
    $cleanup->execute([':subject' => $subject, ':email' => $email]);
}
