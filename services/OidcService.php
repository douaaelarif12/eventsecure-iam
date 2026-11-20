<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/IamConfig.php';

use Jumbojett\OpenIDConnectClient;

final class OidcService
{
    private OpenIDConnectClient $client;

    public function __construct()
    {
        IamConfig::assertConfigured();

        $this->client = new OpenIDConnectClient(
            IamConfig::issuer(),
            IamConfig::clientId(),
            IamConfig::clientSecret()
        );
        $this->client->setRedirectURL(IamConfig::redirectUri());
        $this->client->setCodeChallengeMethod('S256');
        $this->client->addScope(['profile', 'email']);
        $this->client->setTimeout(10);

        if (str_starts_with(IamConfig::issuer(), 'http://localhost:')
            || str_starts_with(IamConfig::issuer(), 'http://127.0.0.1:')) {
            $this->client->setHttpUpgradeInsecureRequests(false);
        }
    }

    /**
     * Exécute Authorization Code + PKCE et retourne uniquement les claims utiles.
     * Les signatures, issuer, audience, state et nonce sont validés par la bibliothèque.
     *
     * @return array{sub:string,email:string,email_verified:bool,nom:string,prenom:string,role:string,id_token:string}
     */
    public function authenticate(): array
    {
        $this->client->authenticate();

        $subject = trim((string) $this->client->getVerifiedClaims('sub'));
        $email = strtolower(trim((string) $this->client->getVerifiedClaims('email')));
        if ($subject === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Le fournisseur IAM n’a pas transmis une identité exploitable.');
        }

        $givenName = trim((string) $this->client->getVerifiedClaims('given_name'));
        $familyName = trim((string) $this->client->getVerifiedClaims('family_name'));
        $preferredUsername = trim((string) $this->client->getVerifiedClaims('preferred_username'));
        if ($givenName === '') {
            $givenName = $preferredUsername !== '' ? $preferredUsername : strstr($email, '@', true);
        }
        if ($familyName === '') {
            $familyName = 'Utilisateur';
        }

        $roles = $this->extractRoles();
        $idToken = (string) $this->client->getIdToken();
        if ($idToken === '') {
            throw new RuntimeException('Le fournisseur IAM n’a pas retourné de jeton d’identité.');
        }

        return [
            'sub' => $subject,
            'email' => $email,
            'email_verified' => $this->client->getVerifiedClaims('email_verified') === true,
            'nom' => $familyName,
            'prenom' => $givenName,
            'role' => in_array('admin', $roles, true) ? 'admin' : 'user',
            'id_token' => $idToken,
        ];
    }

    public function signOut(string $idToken): void
    {
        $this->client->signOut($idToken, IamConfig::postLogoutRedirectUri());
    }

    /** @return list<string> */
    private function extractRoles(): array
    {
        $roles = [];
        $realmAccess = $this->client->getVerifiedClaims('realm_access');
        if (is_object($realmAccess) && isset($realmAccess->roles) && is_array($realmAccess->roles)) {
            $roles = array_merge($roles, $realmAccess->roles);
        }

        $resourceAccess = $this->client->getVerifiedClaims('resource_access');
        $clientId = IamConfig::clientId();
        if (is_object($resourceAccess)
            && isset($resourceAccess->{$clientId})
            && is_object($resourceAccess->{$clientId})
            && isset($resourceAccess->{$clientId}->roles)
            && is_array($resourceAccess->{$clientId}->roles)) {
            $roles = array_merge($roles, $resourceAccess->{$clientId}->roles);
        }

        return array_values(array_unique(array_filter($roles, 'is_string')));
    }
}
