<?php
session_start();
// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=le_boncoincoin', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: connexion.html?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Vérifier si l'ID de l'annonce est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: acceuil.html');
    exit;
}

$id_annonce = $_GET['id'];

// Récupérer les détails de l'annonce
$stmt = $pdo->prepare("SELECT a.*, u.id_utilisateur as id_vendeur, u.nom_utilisateur as nom_vendeur 
                     FROM Annonces a 
                     JOIN Utilisateurs u ON a.id_utilisateur = u.id_utilisateur
                     WHERE a.id_annonce = ?");
$stmt->execute([$id_annonce]);
$annonce = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$annonce) {
    header('Location: acceuil.html');
    exit;
}

// Traitement du formulaire d'envoi de message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer_message'])) {
    $contenu = trim($_POST['contenu']);
    $id_expediteur = $_SESSION['id_utilisateur'];
    $id_destinataire = $annonce['id_vendeur'];
    
    // Validation du message
    if (empty($contenu)) {
        $error = "Le message ne peut pas être vide.";
    } else {
        // Insertion du message dans la base de données
        $stmt = $pdo->prepare("INSERT INTO Messages (id_expediteur, id_destinataire, id_annonce, contenu) 
                             VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$id_expediteur, $id_destinataire, $id_annonce, $contenu])) {
            $success = "Votre message a été envoyé avec succès.";
        } else {
            $error = "Une erreur est survenue lors de l'envoi du message.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacter le vendeur - leboncoincoin</title>
    <link rel="stylesheet" href="detail_annonce.css">
</head>
<body>
    <header>
        <h1>leboncoincoin</h1>
        <div class="duck">
            <img src="leboncoincoin v1 ducky.png" alt="Duck Logo" style="height: 50px;">
        </div>
    </header>

    <div class="top-bar">
        <div class="search-bar">
            <input type="text" placeholder="Rechercher" />
        </div>
        <div class="top-icons">
            <a href="messages.php">
                <button title="Messages">
                    <img src="message.png" alt="Messages" style="height: 24px;">
                </button>
            </a>
            <a href="mes_favoris.php">
                <button class="icon-button" title="Favoris">
                    <img src="favori.jpg" alt="Favoris" style="height: 24px;">
                </button>
            </a>
            <div class="dropdown">
                <button class="dropbtn" title="Compte">
                    <img src="compte.png" alt="Compte" style="height: 24px;">
                </button>
                <div class="dropdown-content">
                    <?php if(isset($_SESSION['id_utilisateur'])): ?>
                        <a href="profil.php">Mon profil</a>
                        <a href="deconnexion.php">Déconnexion</a>
                    <?php else: ?>
                        <a href="connexion.html">Connexion</a>
                        <a href="inscription.html">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <h1>Contacter le vendeur à propos de: <?php echo htmlspecialchars($annonce['titre']); ?></h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="annonce-resume">
            <h2><?php echo htmlspecialchars($annonce['titre']); ?></h2>
            <p><strong>Prix:</strong> <?php echo htmlspecialchars($annonce['prix']); ?> €</p>
            <p><strong>Vendeur:</strong> <?php echo htmlspecialchars($annonce['nom_vendeur']); ?></p>
            
            <?php 
            // Récupérer l'image principale de l'annonce
            $stmt = $pdo->prepare("SELECT url_image FROM ImagesAnnonces WHERE id_annonce = ? AND est_principale = 1 LIMIT 1");
            $stmt->execute([$id_annonce]);
            $image = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($image): ?>
                <img src="<?php echo htmlspecialchars($image['url_image']); ?>" alt="<?php echo htmlspecialchars($annonce['titre']); ?>">
            <?php endif; ?>
        </div>
        
        <form method="POST" class="message-form">
            <div class="form-group">
                <label for="contenu">Votre message:</label>
                <textarea name="contenu" id="contenu" class="form-control" rows="5" required><?php echo isset($_POST['contenu']) ? htmlspecialchars($_POST['contenu']) : ''; ?></textarea>
            </div>
            <div class="buttons">
                <button type="submit" name="envoyer_message" class="btn">Envoyer le message</button>
                <a href="detail_annonce.php?id=<?php echo $id_annonce; ?>" class="btn btn-secondary">Retour à l'annonce</a>
            </div>
        </form>
    </div>
    
    <footer>
        <div class="footer-links">
            <a href="#">À propos de nous</a>
            <a href="#">Infos contact</a>
            <a href="#">FAQ</a>
        </div>
        <div class="socials">
            <a href="#" title="Facebook">
                <img src="facebook.png" alt="Facebook" style="height: 24px;">
            </a>
            <a href="#" title="Instagram">
                <img src="instagram.png" alt="Instagram" style="height: 24px;">
            </a>
            <a href="#" title="TikTok">
                <img src="tiktok.png" alt="TikTok" style="height: 24px;">
            </a>
        </div>
    </footer>
</body>
</html>