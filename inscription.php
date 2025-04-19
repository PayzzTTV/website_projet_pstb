<?php
session_start();
require 'config.php';

// Activer les erreurs pour le debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $date_naissance = $_POST['date_naissance'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $confirm = $_POST['confirmer_mot_de_passe'];

    if ($mot_de_passe !== $confirm) {
        die("Les mots de passe ne correspondent pas.");
    }

    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO utilisateurs (prenom, nom, email, date_naissance, mot_de_passe, is_admin) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("sssss", $prenom, $nom, $email, $date_naissance, $mot_de_passe_hash);

    if ($stmt->execute()) {
        header("Location: connexion.html?inscription=ok");
        exit();
    } else {
        echo "Erreur : " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Méthode non autorisée.";
}
?>
