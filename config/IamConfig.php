<?php
declare(strict_types=1);

final class IamConfig
{
    private static function env(string $name, ?string $default = null): ?string
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            $value = $_SERVER[$name] ?? $_ENV[$name] ?? $default;
        }

        return is_string($value) ? trim($value) : $default;
    }

    private static function envBool(string $name, bool $default): bool
    {
        $value = self::env($name);
        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    public static function enabled(): bool
    {
        return self::envBool('EVENTSECURE_IAM_ENABLED', false);
    }

    public static function localLoginEnabled(): bool
    {
        return self::envBool('EVENTSECURE_LOCAL_LOGIN_ENABLED', true);
    }

    public static function issuer(): string
    {
        return rtrim((string) self::env('EVENTSECURE_OIDC_ISSUER', 'http://localhost:8081/realms/eventsecure'), '/');
    }

    public static function clientId(): string
    {
        return (string) self::env('EVENTSECURE_OIDC_CLIENT_ID', 'eventsecure-web');
    }

    public static function clientSecret(): ?string
    {
        return self::env('EVENTSECURE_OIDC_CLIENT_SECRET');
    }

    public static function baseUrl(): string
    {
        return rtrim((string) self::env('EVENTSECURE_BASE_URL', 'http://localhost/gestion_evenements'), '/');
    }

    public static function redirectUri(): string
    {
        return (string) self::env('EVENTSECURE_OIDC_REDIRECT_URI', self::baseUrl() . '/auth/keycloak.php');
    }

    public static function postLogoutRedirectUri(): string
    {
        return (string) self::env('EVENTSECURE_OIDC_POST_LOGOUT_URI', self::baseUrl() . '/');
    }

    public static function assertConfigured(): void
    {
        if (!self::enabled()) {
            throw new RuntimeException('Le fournisseur IAM n’est pas activé.');
        }

        if (!filter_var(self::issuer(), FILTER_VALIDATE_URL)
            || !filter_var(self::redirectUri(), FILTER_VALIDATE_URL)
            || self::clientId() === '') {
            throw new RuntimeException('La configuration OIDC est incomplète.');
        }
    }
}
