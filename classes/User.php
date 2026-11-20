<?php
// /gestion_evenements/classes/User.php

require_once __DIR__ . '/Database.php';

class User extends Database
{
    /* ==========================
       AUTH
    ========================== */

    public function register($nom, $prenom, $email, $password)
    {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (nom, prenom, email, mot_de_passe, role, date_creation, created_at)
                    VALUES (:nom, :prenom, :email, :password, 'user', NOW(), NOW())";

            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute([
                ':nom'      => $nom,
                ':prenom'   => $prenom,
                ':email'    => $email,
                ':password' => $hashedPassword
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function login($email, $password)
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            return $user;
        }
        return false;
    }

    /**
     * Lie une identité OIDC vérifiée à un compte local ou provisionne ce compte.
     * Une adresse non vérifiée ne peut jamais prendre possession d'un compte local existant.
     *
     * @param array{sub:string,email:string,email_verified:bool,nom:string,prenom:string,role:string} $identity
     */
    public function loginWithOidc(array $identity): array
    {
        $subject = trim((string) ($identity['sub'] ?? ''));
        $email = strtolower(trim((string) ($identity['email'] ?? '')));
        $provider = 'keycloak';
        $role = ($identity['role'] ?? 'user') === 'admin' ? 'admin' : 'user';

        if ($subject === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Identité OIDC invalide.');
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM users WHERE identity_provider = :provider AND oidc_subject = :subject LIMIT 1 FOR UPDATE'
            );
            $stmt->execute([':provider' => $provider, ':subject' => $subject]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1 FOR UPDATE');
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    if (!empty($user['oidc_subject']) && !hash_equals((string) $user['oidc_subject'], $subject)) {
                        throw new RuntimeException('Cette adresse est déjà liée à une autre identité.');
                    }
                    if (empty($user['oidc_subject']) && empty($identity['email_verified'])) {
                        throw new RuntimeException('L’adresse doit être vérifiée avant de lier un compte existant.');
                    }

                    $stmt = $this->pdo->prepare(
                        'UPDATE users
                         SET oidc_subject = :subject, identity_provider = :provider, role = :role, last_login_at = NOW()
                         WHERE id = :id'
                    );
                    $stmt->execute([
                        ':subject' => $subject,
                        ':provider' => $provider,
                        ':role' => $role,
                        ':id' => (int) $user['id'],
                    ]);
                } else {
                    $unusablePassword = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
                    $stmt = $this->pdo->prepare(
                        'INSERT INTO users
                         (nom, prenom, email, mot_de_passe, role, oidc_subject, identity_provider, last_login_at, date_creation, created_at)
                         VALUES (:nom, :prenom, :email, :password, :role, :subject, :provider, NOW(), NOW(), NOW())'
                    );
                    $stmt->execute([
                        ':nom' => (string) ($identity['nom'] ?? 'Utilisateur'),
                        ':prenom' => (string) ($identity['prenom'] ?? 'IAM'),
                        ':email' => $email,
                        ':password' => $unusablePassword,
                        ':role' => $role,
                        ':subject' => $subject,
                        ':provider' => $provider,
                    ]);
                    $user = ['id' => (int) $this->pdo->lastInsertId()];
                }
            } else {
                $stmt = $this->pdo->prepare(
                    'UPDATE users SET role = :role, email = :email, last_login_at = NOW() WHERE id = :id'
                );
                $stmt->execute([':role' => $role, ':email' => $email, ':id' => (int) $user['id']]);
            }

            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => (int) $user['id']]);
            $syncedUser = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$syncedUser) {
                throw new RuntimeException('Le compte local associé est introuvable.');
            }

            $this->pdo->commit();
            return $syncedUser;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /* ==========================
       READ
    ========================== */

    public function getUserById($id)
    {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProfileById(int $id): ?array
    {
        $sql = "SELECT id, nom, prenom, email, telephone, adresse, role, date_creation, created_at
                FROM users
                WHERE id = :id
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        return $u ?: null;
    }

    public function getAllUsers()
    {
        $sql = "SELECT id, nom, prenom, email, role, date_creation, telephone, adresse, created_at
                FROM users
                ORDER BY created_at DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isAdmin($userId)
    {
        $user = $this->getUserById($userId);
        return $user && ($user['role'] === 'admin');
    }

    public function deleteUser($id)
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => (int)$id]);
    }

    /* ==========================
       PROFIL
    ========================== */

    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $params = [':email' => $email];

        if ($excludeId > 0) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    public function updateProfile(
        int $id,
        string $nom,
        string $prenom,
        string $email,
        ?string $telephone,
        ?string $adresse
    ): bool {
        $sql = "UPDATE users
                SET nom = :nom,
                    prenom = :prenom,
                    email = :email,
                    telephone = :telephone,
                    adresse = :adresse
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nom'       => $nom,
            ':prenom'    => $prenom,
            ':email'     => $email,
            ':telephone' => $telephone ?: null,
            ':adresse'   => $adresse ?: null,
            ':id'        => $id
        ]);
    }

    public function verifyPassword(int $id, string $plain): bool
    {
        $stmt = $this->pdo->prepare("SELECT mot_de_passe FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $hash = $stmt->fetchColumn();
        if (!$hash) return false;
        return password_verify($plain, $hash);
    }

    public function updatePassword(int $id, string $newPlainPassword): bool
    {
        $hash = password_hash($newPlainPassword, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET mot_de_passe = :p WHERE id = :id");
        return $stmt->execute([':p' => $hash, ':id' => $id]);
    }
}
