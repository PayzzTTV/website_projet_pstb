<?php
session_start();
require 'config.php'; // Connexion BDD

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les annonces de l'utilisateur
$stmt = $conn->prepare("SELECT id, titre, prix, ville, description FROM annonces WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon Profil</title>
  <link rel="stylesheet" href="profil.css">
</head>
<body>
  <header>
    <h1>Mon Profil</h1>
  </header>

  <div class="container">
    <h2>Mes annonces</h2>

    <div class="annonce-liste">
      <?php while($annonce = $result->fetch_assoc()): ?>
        <div class="annonce-card">
          <div class="annonce-info">
            <h3><?= htmlspecialchars($annonce['titre']) ?></h3>
            <p><strong>Prix :</strong> <?= htmlspecialchars($annonce['prix']) ?> €</p>
            <p><strong>Ville :</strong> <?= htmlspecialchars($annonce['ville']) ?></p>
            <p><?= htmlspecialchars($annonce['description']) ?></p>
          </div>
          <div class="annonce-actions">
            <a href="supprimer_annonce.php?id=<?= $annonce['id'] ?>" onclick="return confirm('Supprimer cette annonce ?')">
              <button class="btn-supprimer">Supprimer</button>
            </a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
