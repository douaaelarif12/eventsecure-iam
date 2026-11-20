# EventSecure IAM — Gestion sécurisée des événements

Application web de **gestion d’événements** permettant de publier, consulter et suivre des événements (conférences, réunions, ateliers, etc.).  
Elle propose deux rôles (**Utilisateur** / **Admin**), une authentification locale de transition et une authentification centralisée **Keycloak / OpenID Connect** avec PKCE et MFA TOTP.

**Auteure :** Douaa EL ARIF

---

## ✅ Fonctionnalités

### 👤 Espace Utilisateur
- Authentification locale ou fédérée avec **Keycloak / OIDC**
- MFA TOTP obligatoire pour les nouveaux comptes Keycloak
- Consulter les événements : **recherche**, **filtre par catégorie**, **tri par date**
- Voir **détails** d’un événement + **partage**
- Visualiser les événements sur :
  - **Carte** (localisation)
  - **Calendrier** (FullCalendar) + ouverture des détails via popup
- S’inscrire à un événement (avec contrôle de places)
- Gérer **mes inscriptions** :
  - liste des inscriptions
  - **annulation**
  - téléchargement de l’**invitation PDF + QR Code**
- **Notifications** : badge de non-lus + panneau de lecture (marquer comme lue)
- Options : **mode sombre** + **changement de langue (FR/EN)**

### 🛠️ Espace Admin
- Gestion des événements (**CRUD**)
  - ajout (image, catégorie, localisation via carte)
  - modification (notifier les inscrits en cas de changement)
  - suppression/annulation (notifier les inscrits)
  - listing
- Gestion des catégories (**CRUD**)
- Gestion participants / inscriptions : voir les inscrits par événement

### 🤖 Chatbot
- Assistant pour :
  - répondre aux questions (événements / inscriptions)
  - aider à la navigation (carte / calendrier / détails)

---

## 🧰 Technologies utilisées

- **Backend :** PHP 8.2 (POO), PDO, sessions sécurisées
- **Base de données :** MySQL
- **IAM :** Keycloak 26.8.2, OpenID Connect Authorization Code + PKCE S256, RBAC et TOTP
- **Conteneurisation :** Docker Compose pour le fournisseur d’identité
- **Frontend :** HTML5, CSS3, **Bootstrap 5**, JavaScript
- **Calendrier :** FullCalendar
- **Carte :** Leaflet + OpenStreetMap
- **PDF + QR Code :** génération d’invitations (PDF) + QR Code
- **Notifications :** système + compteur non-lus (badge)
- **Sécurité :** validation des jetons OIDC, state/nonce, liaison d’identité, password_hash, CSRF, requêtes préparées, échappement HTML et contrôle d’accès par rôles

---

## 📦 Installation (XAMPP)

### 1) Pré-requis
- XAMPP (Apache + MySQL)
- PHP ≥ 8.x recommandé

### 2) Installer le projet
- Copiez le dossier du projet dans `C:\xampp\htdocs\gestion_evenements`.
- Lancez Apache et MySQL depuis le panneau XAMPP.
- Configurez ensuite la base de données dans `config/database.php`.

### 3) Installer les dépendances

```powershell
composer install
```

Activez également l'extension GD dans `C:\xampp\php\php.ini` pour la génération des QR Codes :

```ini
extension=gd
```

Redémarrez ensuite Apache.

### 4) Configurer la base de données

Importez `database.sql`, puis configurez les variables d'environnement décrites dans
`config/.env.example`. Ne publiez jamais les identifiants réels de la base de données.

L'application ne fournit volontairement aucun compte administrateur par défaut. Créez un
compte depuis l'interface, puis accordez-lui le rôle administrateur depuis MySQL :

```sql
UPDATE users SET role = 'admin' WHERE email = 'votre-email@example.com';
```

Application locale : `http://127.0.0.1/gestion_evenements/`

---

## 🔐 Activer Keycloak / OIDC

### 1) Préparer l’administrateur Keycloak

Générez un fichier local contenant un mot de passe d’administration aléatoire :

```powershell
C:\xampp\php\php.exe scripts\generate_keycloak_env.php
```

Vous pouvez aussi copier `.env.keycloak.example` vers `.env.keycloak` et choisir vous-même un mot de passe long. Le fichier final est ignoré par Git et n’est jamais écrasé par le script.

### 2) Démarrer Keycloak

```powershell
docker compose --env-file .env.keycloak up -d
```

Le realm `eventsecure`, le client public `eventsecure-web`, les rôles `user` / `admin`, PKCE S256 et l’action MFA TOTP sont importés automatiquement. La console d’administration est disponible sur `http://localhost:8081/admin/`.

### 3) Migrer la table des utilisateurs

La migration nécessite temporairement un compte propriétaire du schéma disposant du droit `ALTER`. N’accordez pas ce droit au compte utilisé par l’application :

```powershell
$env:EVENTSECURE_MIGRATION_DB_USER='root'
# Définissez EVENTSECURE_MIGRATION_DB_PASSWORD si votre compte MySQL en possède un.
C:\xampp\php\php.exe scripts\migrate_oidc.php
Remove-Item Env:EVENTSECURE_MIGRATION_DB_USER -ErrorAction SilentlyContinue
Remove-Item Env:EVENTSECURE_MIGRATION_DB_PASSWORD -ErrorAction SilentlyContinue
```

La migration ajoute une identité OIDC unique et la date de dernière connexion sans supprimer les comptes locaux.

### 4) Activer l’IAM dans Apache

Ajoutez les variables de `config/.env.example` à l’environnement Apache/XAMPP, notamment :

```text
EVENTSECURE_IAM_ENABLED=true
EVENTSECURE_LOCAL_LOGIN_ENABLED=true
EVENTSECURE_BASE_URL=http://localhost/gestion_evenements
EVENTSECURE_OIDC_ISSUER=http://localhost:8081/realms/eventsecure
EVENTSECURE_OIDC_CLIENT_ID=eventsecure-web
EVENTSECURE_OIDC_REDIRECT_URI=http://localhost/gestion_evenements/auth/keycloak.php
EVENTSECURE_OIDC_POST_LOGOUT_URI=http://localhost/gestion_evenements/
```

Redémarrez Apache après la modification. Le bouton **Continuer avec Keycloak** apparaît sur la page de connexion.

### 5) Rôles et migration progressive

- Les nouveaux comptes Keycloak obtiennent le rôle `user` et doivent configurer un code TOTP à leur première connexion.
- Le rôle `admin` se donne depuis Keycloak, dans **Realm roles**.
- Un compte local existant n’est lié automatiquement que si Keycloak fournit la même adresse email comme vérifiée. Cette règle évite une prise de contrôle par simple réutilisation d’adresse.
- `EVENTSECURE_LOCAL_LOGIN_ENABLED=false` désactive le formulaire local lorsque la migration est terminée.
- Aucun utilisateur ni mot de passe de démonstration n’est versionné.

Le client public avec PKCE est destiné au développement local. En production, utilisez HTTPS, un client confidentiel avec secret injecté par l’environnement et une base persistante dédiée à Keycloak.

---

## 🔐 Publication sûre

- Les mots de passe sont hachés avec `password_hash`.
- Les identifiants MySQL sont fournis uniquement par variables d'environnement.
- `vendor/`, les billets PDF, le stockage privé et les uploads dynamiques sont exclus de Git.
- Le fichier SQL public contient uniquement des catégories et événements de démonstration.
- `.env.keycloak` et les secrets OIDC sont exclus de Git.
- Le Password Grant OIDC est désactivé ; l’application utilise Authorization Code + PKCE.

## 🧪 Vérification

```powershell
composer validate --strict
php tests/security_smoke.php
php tests/iam_smoke.php
```

La CI GitHub vérifie également la syntaxe de tous les fichiers PHP et la configuration IAM versionnée.

Un test d’intégration MySQL optionnel provisionne un compte temporaire, vérifie la synchronisation du rôle puis le supprime immédiatement :

```powershell
$env:EVENTSECURE_RUN_DB_INTEGRATION='1'
C:\xampp\php\php.exe tests\iam_database_integration.php
Remove-Item Env:EVENTSECURE_RUN_DB_INTEGRATION
```

## 📄 Licence

Ce projet est distribué sous licence [MIT](LICENSE).
