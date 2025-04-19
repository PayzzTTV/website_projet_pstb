<?php
session_start();
require 'config.php';

// Activer les erreurs PHP pour debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['mail'] ?? ''; // Utilise 'mail' au lieu de 'email'
    $mot_de_passe = $_POST['mdp'] ?? ''; // Utilise 'mdp' au lieu de 'mot_de_passe'

    $stmt = $conn->prepare("SELECT id, prenom, nom, mot_de_passe, is_admin FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['is_admin'] = $user['is_admin'];

            header("Location: acceuil.html");
            exit();
        } else {
            echo "<div class='error'>Mot de passe incorrect.</div>";
        }
    } else {
        echo "<div class='error'>Email non reconnu.</div>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<div class='error'>Méthode non autorisée.</div>";
}
?>