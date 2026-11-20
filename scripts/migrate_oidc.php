<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$migrationUser = getenv('EVENTSECURE_MIGRATION_DB_USER');
if (is_string($migrationUser) && $migrationUser !== '') {
    $host = (string) (getenv('EVENTSECURE_DB_HOST') ?: '127.0.0.1');
    $name = (string) (getenv('EVENTSECURE_DB_NAME') ?: 'gestion_evenements');
    $password = (string) (getenv('EVENTSECURE_MIGRATION_DB_PASSWORD') ?: '');
    $pdo = new PDO(
        "mysql:host={$host};dbname={$name};charset=utf8mb4",
        $migrationUser,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
    );
} else {
    $pdo = DatabaseConfig::getConnection();
}
$schema = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();

$columns = [
    'oidc_subject' => 'ALTER TABLE users ADD COLUMN oidc_subject VARCHAR(255) DEFAULT NULL AFTER role',
    'identity_provider' => 'ALTER TABLE users ADD COLUMN identity_provider VARCHAR(50) DEFAULT NULL AFTER oidc_subject',
    'last_login_at' => 'ALTER TABLE users ADD COLUMN last_login_at DATETIME DEFAULT NULL AFTER identity_provider',
];

$columnQuery = $pdo->prepare(
    'SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table AND COLUMN_NAME = :column'
);
foreach ($columns as $column => $sql) {
    $columnQuery->execute([':schema' => $schema, ':table' => 'users', ':column' => $column]);
    if ((int) $columnQuery->fetchColumn() === 0) {
        $pdo->exec($sql);
        echo "Added users.$column\n";
    }
}

$indexQuery = $pdo->prepare(
    'SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table AND INDEX_NAME = :index'
);
$indexQuery->execute([':schema' => $schema, ':table' => 'users', ':index' => 'uq_users_oidc_identity']);
if ((int) $indexQuery->fetchColumn() === 0) {
    $pdo->exec('CREATE UNIQUE INDEX uq_users_oidc_identity ON users (identity_provider, oidc_subject)');
    echo "Added uq_users_oidc_identity\n";
}

echo "OIDC migration complete\n";
