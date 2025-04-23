-- Création de la base de données
CREATE DATABASE IF NOT EXISTS leboncoincoin;
USE leboncoincoin;

-- Table des utilisateurs (version consolidée)
DROP TABLE IF EXISTS Utilisateurs;
CREATE TABLE IF NOT EXISTS Utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Utilisation de "id" comme clé primaire
    prenom VARCHAR(100) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    nom_utilisateur VARCHAR(50) UNIQUE, -- Ajout du champ "nom_utilisateur"
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    numero_telephone VARCHAR(20),
    date_naissance DATE,
    role ENUM('consommateur', 'administrateur') DEFAULT 'consommateur',
    jeton_reinitialisation VARCHAR(255),
    expiration_jeton DATETIME,
    cree_le DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_admin TINYINT(1) DEFAULT 0
);

-- Insertion de comptes de test
INSERT INTO Utilisateurs (prenom, nom, email, date_naissance, mot_de_passe, is_admin)
VALUES ('Jean', 'Client', 'user@test.com', '2000-01-01', 
        '$2y$10$FE6xSNgZr92pZslBQuI.vOJbR7JKYjxV21gD/CUnq9XQOlF/6IbzK', 0);

INSERT INTO Utilisateurs (prenom, nom, email, date_naissance, mot_de_passe, is_admin)
VALUES ('Admin', 'Principal', 'admin@test.com', '1990-01-01', 
        '$2y$10$yjEB7rcqu4aQqPj6O7UvCeOcRrKiFbAhXb0Q4CE2y8bc6M08s0Dxq', 1);

-- Table des catégories
DROP TABLE IF EXISTS Categories;
CREATE TABLE IF NOT EXISTS Categories (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE
);

-- Table des annonces
DROP TABLE IF EXISTS Annonces;
CREATE TABLE IF NOT EXISTS Annonces (
    id_annonce INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_categorie INT NOT NULL,
    titre VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    localisation VARCHAR(100),
    nombre_vues INT DEFAULT 0,
    cree_le DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_categorie) REFERENCES Categories(id_categorie)
);

-- Table des images d'annonces
DROP TABLE IF EXISTS ImagesAnnonces;
CREATE TABLE IF NOT EXISTS ImagesAnnonces (
    id_image INT AUTO_INCREMENT PRIMARY KEY,
    id_annonce INT NOT NULL,
    url_image VARCHAR(255) NOT NULL,
    est_principale BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_annonce) REFERENCES Annonces(id_annonce) ON DELETE CASCADE
);

-- Table des messages entre utilisateurs
DROP TABLE IF EXISTS Messages;
CREATE TABLE IF NOT EXISTS Messages (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    id_expediteur INT NOT NULL,
    id_destinataire INT NOT NULL,
    id_annonce INT,
    contenu TEXT NOT NULL,
    envoye_le DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_expediteur) REFERENCES Utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destinataire) REFERENCES Utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_annonce) REFERENCES Annonces(id_annonce) ON DELETE CASCADE
);

-- Table des favoris
DROP TABLE IF EXISTS Favoris;
CREATE TABLE IF NOT EXISTS Favoris (
    id_favori INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_annonce INT NOT NULL,
    ajoute_le DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_annonce) REFERENCES Annonces(id_annonce) ON DELETE CASCADE
);