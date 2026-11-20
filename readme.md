#  Gestion des Événements — Application Web (PHP/MySQL)

Application web de **gestion d’événements** permettant de publier, consulter et suivre des événements (conférences, réunions, ateliers, etc.).  
Elle propose deux rôles (**Utilisateur** / **Admin**) et un **chatbot d’assistance** pour améliorer l’expérience de navigation.

**Auteure :** elarif douaa

---

## ✅ Fonctionnalités

### 👤 Espace Utilisateur
- Authentification : **inscription / connexion / déconnexion**
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

- **Backend :** PHP (POO), PDO, sessions
- **Base de données :** MySQL
- **Frontend :** HTML5, CSS3, **Bootstrap 5**, JavaScript
- **Calendrier :** FullCalendar
- **Carte :** Leaflet + OpenStreetMap
- **PDF + QR Code :** génération d’invitations (PDF) + QR Code
- **Notifications :** système + compteur non-lus (badge)
- **Sécurité :** password_hash, requêtes préparées, htmlspecialchars, contrôle d’accès par rôles

---

## 📦 Installation (XAMPP)

### 1) Pré-requis
- XAMPP (Apache + MySQL)
- PHP ≥ 8.x recommandé

### 2) Installer le projet
- Copiez le dossier du projet dans `C:\xampp\htdocs\gestion-evenements`.
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

## 🔐 Publication sûre

- Les mots de passe sont hachés avec `password_hash`.
- Les identifiants MySQL sont fournis uniquement par variables d'environnement.
- `vendor/`, les billets PDF, le stockage privé et les uploads dynamiques sont exclus de Git.
- Le fichier SQL public contient uniquement des catégories et événements de démonstration.

## 🧪 Vérification

```powershell
composer validate --strict
php tests/security_smoke.php
```

La CI GitHub vérifie également la syntaxe de tous les fichiers PHP.

## 📄 Licence

Ce projet est distribué sous licence [MIT](LICENSE).
