<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');

final class DatabaseConfig
{
    private static function env(string $name, ?string $default = null): ?string
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            $value = $_SERVER[$name] ?? $_ENV[$name] ?? $default;
        }
        return is_string($value) ? $value : $default;
    }

    public static function getConnection(): PDO
    {
        $host = self::env('EVENTSECURE_DB_HOST', '127.0.0.1');
        $name = self::env('EVENTSECURE_DB_NAME', 'gestion_evenements');
        $user = self::env('EVENTSECURE_DB_USER');
        $password = self::env('EVENTSECURE_DB_PASSWORD');

        if ($user === null || $user === '' || $password === null) {
            error_log('EventSecure: variables de connexion MySQL absentes.');
            throw new RuntimeException('Le service de données est temporairement indisponible.');
        }

        try {
            return new PDO(
                "mysql:host={$host};dbname={$name};charset=utf8mb4",
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('EventSecure: échec de connexion MySQL: ' . $e->getMessage());
            throw new RuntimeException('Le service de données est temporairement indisponible.');
        }
    }
}
