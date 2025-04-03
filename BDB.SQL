-- Script SQL pour la base de données le_boncoincoin

CREATE DATABASE IF NOT EXISTS le_boncoincoin;
USE le_boncoincoin;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS Utilisateurs (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom_utilisateur VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe_hash VARCHAR(255) NOT NULL,
    numero_telephone VARCHAR(20),
    role ENUM('consommateur', 'administrateur') DEFAULT 'consommateur',
    jeton_reinitialisation VARCHAR(255),
    expiration_jeton DATETIME,
    cree_le DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des catégories
CREATE TABLE IF NOT EXISTS Categories (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE
);

-- Table des annonces
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
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id_utilisateur),
    FOREIGN KEY (id_categorie) REFERENCES Categories(id_categorie)
);

-- Table des images d'annonces
CREATE TABLE IF NOT EXISTS ImagesAnnonces (
    id_image INT AUTO_INCREMENT PRIMARY KEY,
    id_annonce INT NOT NULL,
    url_image VARCHAR(255) NOT NULL,
    est_principale BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_annonce) REFERENCES Annonces(id_annonce) ON DELETE CASCADE
);

-- Table des messages entre utilisateurs
CREATE TABLE IF NOT EXISTS Messages (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    id_expediteur INT NOT NULL,
    id_destinataire INT NOT NULL,
    id_annonce INT,
    contenu TEXT NOT NULL,
    envoye_le DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_expediteur) REFERENCES Utilisateurs(id_utilisateur),
    FOREIGN KEY (id_destinataire) REFERENCES Utilisateurs(id_utilisateur),
    FOREIGN KEY (id_annonce) REFERENCES Annonces(id_annonce)
);

-- Table des favoris
CREATE TABLE IF NOT EXISTS Favoris (
    id_favori INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_annonce INT NOT NULL,
    ajoute_le DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id_utilisateur),
    FOREIGN KEY (id_annonce) REFERENCES Annonces(id_annonce)
);