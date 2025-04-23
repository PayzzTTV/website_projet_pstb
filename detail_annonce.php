<?php
session_start();
// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=le_boncoincoin', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vérifier si l'ID de l'annonce est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: acceuil.html');
    exit;
}

$id_annonce = $_GET['id'];

// Récupérer les détails de l'annonce
$stmt = $pdo->prepare("
    SELECT 
        a.*,
        c.nom AS nom_categorie,
        u.nom_utilisateur,
        u.id_utilisateur
    FROM 
        Annonces a 
    JOIN 
        Categories c ON a.id_categorie = c.id_categorie
    JOIN 
        Utilisateurs u ON a.id_utilisateur = u.id_utilisateur
    WHERE 
        a.id_annonce = ?
");
$stmt->execute([$id_annonce]);
$annonce = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$annonce) {
    header('Location: acceuil.html');
    exit;
}

// Récupérer les images de l'annonce
$stmt = $pdo->prepare("
    SELECT 
        url_image, est_principale 
    FROM 
        ImagesAnnonces 
    WHERE 
        id_annonce = ?
    ORDER BY 
        est_principale DESC
");
$stmt->execute([$id_annonce]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Incrémenter le nombre de vues
$stmt = $pdo->prepare("
    UPDATE Annonces 
    SET nombre_vues = nombre_vues + 1 
    WHERE id_annonce = ?
");
$stmt->execute([$id_annonce]);

// Vérifier si l'annonce est dans les favoris de l'utilisateur connecté
$in_favoris = false;
if (isset($_SESSION['id_utilisateur'])) {
    $stmt = $pdo->prepare("
        SELECT id_favori 
        FROM Favoris 
        WHERE id_utilisateur = ? AND id_annonce = ?
    ");
    $stmt->execute([$_SESSION['id_utilisateur'], $id_annonce]);
    $in_favoris = $stmt->fetch() ? true : false;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($annonce['titre']); ?> - leboncoincoin</title>
    <link rel="stylesheet" href="accueil.css">
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
        <div class="breadcrumb">
            <a href="acceuil.html">Accueil</a> &gt; 
            <a href="categories.php?id=<?php echo $annonce['id_categorie']; ?>"><?php echo htmlspecialchars($annonce['nom_categorie']); ?></a> &gt; 
            <span><?php echo htmlspecialchars($annonce['titre']); ?></span>
        </div>
        
        <div class="annonce-detail">
            <h1><?php echo htmlspecialchars($annonce['titre']); ?></h1>
            
            <div class="annonce-meta">
                <span class="price"><?php echo number_format($annonce['prix'], 2, ',', ' '); ?> €</span>
                <span class="location"><?php echo htmlspecialchars($annonce['localisation'] ?: 'Non précisée'); ?></span>
                <span class="date">Publiée le <?php echo date('d/m/Y à H:i', strtotime($annonce['cree_le'])); ?></span>
                <span class="views"><?php echo $annonce['nombre_vues']; ?> vues</span>
            </div>
            
            <div class="annonce-content">
                <div class="annonce-images">
                    <?php if (empty($images)): ?>
                        <div class="main-image no-image">
                            <span>Pas d'image disponible</span>
                        </div>
                    <?php else: ?>
                        <div class="main-image">
                            <img src="<?php echo htmlspecialchars($images[0]['url_image']); ?>" alt="<?php echo htmlspecialchars($annonce['titre']); ?>">
                        </div>
                        
                        <?php if (count($images) > 1): ?>
                            <div class="thumbnails">
                                <?php foreach ($images as $index => $image): ?>
                                    <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" data-img="<?php echo htmlspecialchars($image['url_image']); ?>">
                                        <img src="<?php echo htmlspecialchars($image['url_image']); ?>" alt="Miniature">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div class="annonce-info">
                    <div class="description">
                        <h2>Description</h2>
                        <div class="description-content">
                            <?php echo nl2br(htmlspecialchars($annonce['description'])); ?>
                        </div>
                    </div>
                    
                    <div class="vendeur-info">
                        <h2>Informations sur le vendeur</h2>
                        <p>
                            <strong>Nom:</strong> <?php echo htmlspecialchars($annonce['nom_utilisateur']); ?><br>
                            <strong>Membre depuis:</strong> <?php 
                                // Récupérer la date d'inscription du vendeur
                                $stmt = $pdo->prepare("SELECT cree_le FROM Utilisateurs WHERE id_utilisateur = ?");
                                $stmt->execute([$annonce['id_utilisateur']]);
                                $date_inscription = $stmt->fetchColumn();
                                echo date('F Y', strtotime($date_inscription)); 
                            ?>
                        </p>
                    </div>
                    
                    <div class="annonce-actions">
                        <?php if (isset($_SESSION['id_utilisateur'])): ?>
                            <?php if ($in_favoris): ?>
                                <a href="favoris.php?action=remove&id_annonce=<?php echo $annonce['id_annonce']; ?>" class="btn btn-warning">
                                    Retirer des favoris
                                </a>
                            <?php else: ?>
                                <a href="favoris.php?action=add&id_annonce=<?php echo $annonce['id_annonce']; ?>" class="btn btn-favorite">
                                    Ajouter aux favoris
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($_SESSION['id_utilisateur'] != $annonce['id_utilisateur']): ?>
                                <a href="contact_vendeur.php?id=<?php echo $annonce['id_annonce']; ?>" class="btn btn-primary">
                                    Contacter le vendeur
                                </a>
                            <?php else: ?>
                                <a href="modifier_annonce.php?id=<?php echo $annonce['id_annonce']; ?>" class="btn btn-secondary">
                                    Modifier mon annonce
                                </a>
                                <a href="supprimer_annonce.php?id=<?php echo $annonce['id_annonce']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?')">
                                    Supprimer
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="connexion.html?redirect=<?php echo urlencode('detail_annonce.php?id=' . $annonce['id_annonce']); ?>" class="btn btn-secondary">
                                Connectez-vous pour interagir
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="similar-annonces">
            <h2>Annonces similaires</h2>
            <div class="similar-list">
                <?php
                // Récupérer quelques annonces similaires (même catégorie, sauf l'annonce actuelle)
                $stmt = $pdo->prepare("
                    SELECT 
                        a.id_annonce, 
                        a.titre, 
                        a.prix,
                        a.localisation
                    FROM 
                        Annonces a
                    WHERE 
                        a.id_categorie = ? 
                        AND a.id_annonce != ?
                    ORDER BY 
                        a.cree_le DESC
                    LIMIT 4
                ");
                $stmt->execute([$annonce['id_categorie'], $id_annonce]);
                $similar = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($similar as $sim):
                    // Récupérer l'image principale pour cette annonce
                    $stmt = $pdo->prepare("
                        SELECT url_image 
                        FROM ImagesAnnonces 
                        WHERE id_annonce = ? 
                        AND est_principale = 1 
                        LIMIT 1
                    ");
                    $stmt->execute([$sim['id_annonce']]);
                    $sim_image = $stmt->fetchColumn();
                ?>
                <div class="card">
                    <?php if ($sim_image): ?>
                        <img src="<?php echo htmlspecialchars($sim_image); ?>" alt="<?php echo htmlspecialchars($sim['titre']); ?>">
                    <?php else: ?>
                        <div class="no-image"></div>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($sim['titre']); ?></h3>
                    <p><?php echo number_format($sim['prix'], 2, ',', ' '); ?> € - <?php echo htmlspecialchars($sim['localisation'] ?: 'Non précisée'); ?></p>
                    <a href="detail_annonce.php?id=<?php echo $sim['id_annonce']; ?>" class="btn btn-sm">Voir l'annonce</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
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

    <script>
        // Script pour changer l'image principale lorsqu'on clique sur une miniature
        document.addEventListener('DOMContentLoaded', function() {
            const thumbnails = document.querySelectorAll('.thumbnail');
            const mainImage = document.querySelector('.main-image img');
            
            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    // Mettre à jour l'image principale
                    mainImage.src = this.getAttribute('data-img');
                    
                    // Mettre à jour la classe active
                    thumbnails.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>