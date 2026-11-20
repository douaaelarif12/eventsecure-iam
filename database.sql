CREATE DATABASE IF NOT EXISTS gestion_evenements
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE gestion_evenements;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  mot_de_passe VARCHAR(255) NOT NULL,
  telephone VARCHAR(30) DEFAULT NULL,
  adresse VARCHAR(255) DEFAULT NULL,
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  oidc_subject VARCHAR(255) DEFAULT NULL,
  identity_provider VARCHAR(50) DEFAULT NULL,
  last_login_at DATETIME DEFAULT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_oidc_identity (identity_provider, oidc_subject)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL UNIQUE,
  description TEXT DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS evenements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  date_evenement DATE NOT NULL,
  heure_debut TIME NOT NULL,
  heure_fin TIME DEFAULT NULL,
  lieu VARCHAR(255) NOT NULL,
  contact_email VARCHAR(190) DEFAULT NULL,
  contact_phone VARCHAR(30) DEFAULT NULL,
  categorie_id INT UNSIGNED DEFAULT NULL,
  nb_max_participants INT UNSIGNED NOT NULL DEFAULT 50,
  image VARCHAR(255) DEFAULT NULL,
  latitude DECIMAL(10,7) DEFAULT NULL,
  longitude DECIMAL(10,7) DEFAULT NULL,
  statut ENUM('actif', 'annule', 'termine') NOT NULL DEFAULT 'actif',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_evenements_categorie
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inscriptions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  evenement_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  nom_participant VARCHAR(200) NOT NULL,
  email_participant VARCHAR(190) NOT NULL,
  telephone VARCHAR(30) DEFAULT NULL,
  statut ENUM('confirme', 'annule') NOT NULL DEFAULT 'confirme',
  ticket_token VARCHAR(64) DEFAULT NULL UNIQUE,
  ticket_pdf_path VARCHAR(255) DEFAULT NULL,
  ticket_created_at DATETIME DEFAULT NULL,
  date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_inscription_evenement_user (evenement_id, user_id),
  CONSTRAINT fk_inscriptions_evenement
    FOREIGN KEY (evenement_id) REFERENCES evenements(id) ON DELETE CASCADE,
  CONSTRAINT fk_inscriptions_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type VARCHAR(50) DEFAULT NULL,
  message TEXT NOT NULL,
  lien VARCHAR(255) DEFAULT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (nom, description) VALUES
  ('Conférence', 'Conférences et présentations'),
  ('Atelier', 'Ateliers pratiques'),
  ('Réunion', 'Réunions et rencontres'),
  ('Autre', 'Autres événements');

INSERT INTO evenements (titre, description, date_evenement, heure_debut, heure_fin, lieu, contact_email, contact_phone, categorie_id, nb_max_participants, latitude, longitude, statut)
SELECT 'Forum des innovations 2026', 'Conference sur les innovations numeriques et les projets etudiants.', '2026-11-12', '09:30:00', '12:30:00', 'ENSA El Jadida', 'events@example.test', '+212600000000', 1, 120, 33.2299000, -8.5000000, 'actif'
WHERE NOT EXISTS (SELECT 1 FROM evenements WHERE titre = 'Forum des innovations 2026');

INSERT INTO evenements (titre, description, date_evenement, heure_debut, heure_fin, lieu, contact_email, contact_phone, categorie_id, nb_max_participants, latitude, longitude, statut)
SELECT 'Atelier developpement web', 'Atelier pratique HTML, CSS et PHP pour debutants.', '2026-11-20', '14:00:00', '17:00:00', 'Salle informatique ENSA', 'events@example.test', '+212600000000', 2, 30, 33.2299000, -8.5000000, 'actif'
WHERE NOT EXISTS (SELECT 1 FROM evenements WHERE titre = 'Atelier developpement web');

INSERT INTO evenements (titre, description, date_evenement, heure_debut, heure_fin, lieu, contact_email, contact_phone, categorie_id, nb_max_participants, latitude, longitude, statut)
SELECT 'Rencontre associative', 'Rencontre entre les associations et les nouveaux etudiants.', '2026-12-05', '10:00:00', '12:00:00', 'Amphitheatre principal', 'events@example.test', '+212600000000', 3, 80, 33.2299000, -8.5000000, 'actif'
WHERE NOT EXISTS (SELECT 1 FROM evenements WHERE titre = 'Rencontre associative');

UPDATE evenements SET image = '/gestion_evenements/assets/uploads/forum-innovations-2026.png'
WHERE titre = 'Forum des innovations 2026';
UPDATE evenements SET image = '/gestion_evenements/assets/uploads/atelier-developpement-web.png'
WHERE titre = 'Atelier developpement web';
UPDATE evenements SET image = '/gestion_evenements/assets/uploads/rencontre-associative.png'
WHERE titre = 'Rencontre associative';
