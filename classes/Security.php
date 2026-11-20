<?php
declare(strict_types=1);

final class Security
{
    public static function startSession(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    }

    public static function regenerateSession(): void
    {
        self::startSession();
        session_regenerate_id(true);
    }

    public static function establishIdentity(array $user, string $authMethod = 'local', array $context = []): void
    {
        self::regenerateSession();
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['nom'] = (string) $user['nom'];
        $_SESSION['prenom'] = (string) $user['prenom'];
        $_SESSION['email'] = (string) $user['email'];
        $_SESSION['role'] = (string) $user['role'];
        $_SESSION['auth_method'] = $authMethod;
        $_SESSION['authenticated_at'] = time();

        foreach ($context as $key => $value) {
            if (is_string($key) && str_starts_with($key, 'oidc_') && is_string($value)) {
                $_SESSION[$key] = $value;
            }
        }
    }

    public static function destroySession(): void
    {
        self::startSession();
        $_SESSION = [];
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'] ?: '/', '', (bool) $params['secure'], (bool) $params['httponly']);
        session_destroy();
    }

    public static function csrfToken(): string
    {
        self::startSession();
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return (string) $_SESSION['_csrf_token'];
    }

    public static function csrfInput(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function verifyCsrf(): bool
    {
        self::startSession();
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return is_string($token) && isset($_SESSION['_csrf_token']) && hash_equals((string) $_SESSION['_csrf_token'], $token);
    }

    public static function requirePostCsrf(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !self::verifyCsrf()) {
            http_response_code(419);
            exit('Requête expirée ou non autorisée. Veuillez réessayer.');
        }
    }

    public static function safeReferer(string $fallback = '/gestion_evenements/'): string
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (!is_string($referer) || $referer === '') {
            return $fallback;
        }
        $parts = parse_url($referer);
        if ($parts === false) {
            return $fallback;
        }
        $host = $parts['host'] ?? '';
        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
        if ($host !== '' && !hash_equals((string) $currentHost, (string) $host)) {
            return $fallback;
        }
        $path = $parts['path'] ?? $fallback;
        if (!is_string($path) || !str_starts_with($path, '/gestion_evenements/')) {
            return $fallback;
        }
        return $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }
}
