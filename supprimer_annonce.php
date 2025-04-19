<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Accès refusé");
}

if (isset($_GET['id'])) {
    $annonce_id = intval($_GET['id']);
    
    // Vérifie l'auteur de l'annonce
    $stmt = $conn->prepare("SELECT user_id FROM annonces WHERE id = ?");
    $stmt->bind_param("i", $annonce_id);
    $stmt->execute();
    $stmt->bind_result($auteur_id);
    if ($stmt->fetch()) {
        // Vérifie si c'est le bon utilisateur ou un admin
        if ($auteur_id == $_SESSION['user_id'] || $_SESSION['is_admin'] == 1) {
            $stmt->close();
            $del = $conn->prepare("DELETE FROM annonces WHERE id = ?");
            $del->bind_param("i", $annonce_id);
            $del->execute();
            header("Location: acceuil.html?supprime=ok");
            exit();
        } else {
            echo "Vous n'avez pas le droit de supprimer cette annonce.";
        }
    } else {
        echo "Annonce introuvable.";
    }
    $stmt->close();
}
$conn->close();
?>
