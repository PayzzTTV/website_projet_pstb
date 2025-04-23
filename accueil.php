<!-- filepath: c:\xampp\htdocs\sitewebproject2K25\acceuil.php -->
<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: connexion.html");
  exit();
}

$is_admin = $_SESSION['is_admin'];
$user_id = $_SESSION['user_id'];

// Récupérer les annonces selon le rôle
if ($is_admin) {
  $result = $conn->query("SELECT * FROM annonces");
} else {
  $stmt = $conn->prepare("SELECT * FROM annonces WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
}

// Inclure le fichier HTML pour l'affichage
include 'acceuil.html';

if (isset($stmt)) $stmt->close();
$conn->close();
?>