<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
$host = 'localhost';
$dbname = 'leboncoincoin';
$username = 'root'; // à adapter
$password = '';     // à adapter
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Vérifier si le formulaire est bien envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sécurisation des données
    $titre = htmlspecialchars($_POST['titre']);
    $categorie = htmlspecialchars($_POST['categorie']);
    $prix = floatval($_POST['prix']);
    $ville = htmlspecialchars($_POST['ville']);
    $description = htmlspecialchars($_POST['description']);

    // Gérer l'image
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = basename($_FILES['image']['name']);
        $targetDir = 'uploads/';
        $targetFile = $targetDir . time() . '_' . $imageName;

        // Créer le dossier si besoin
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        } else {
            die("Erreur lors de l'envoi de l'image.");
        }
    } else {
        die("Image manquante ou invalide.");
    }

    // Insérer dans la base
    $sql = "INSERT INTO annonces (titre, categorie, prix, ville, description, image)
            VALUES (:titre, :categorie, :prix, :ville, :description, :image)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':titre', $titre);
    $stmt->bindParam(':categorie', $categorie);
    $stmt->bindParam(':prix', $prix);
    $stmt->bindParam(':ville', $ville);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':image', $imagePath);

    if ($stmt->execute()) {
        echo "<h2>Annonce ajoutée avec succès !</h2>";
        echo "<a href='acceuil.html'>Retour à l'accueil</a>";
    } else {
        echo "Erreur lors de l'insertion.";
    }
} else {
    echo "Formulaire non soumis.";
}
?>
