<?php
session_start();
// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=le_boncoincoin', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: connexion.html');
    exit;
}

$id_utilisateur = $_SESSION['id_utilisateur'];

// Récupérer les conversations de l'utilisateur (groupées par annonce et interlocuteur)
$stmt = $pdo->prepare("
    SELECT 
        m.id_annonce,
        a.titre AS titre_annonce,
        CASE 
            WHEN m.id_expediteur = ? THEN m.id_destinataire
            ELSE m.id_expediteur
        END AS id_interlocuteur,
        u.nom_utilisateur AS nom_interlocuteur,
        MAX(m.envoye_le) AS dernier_message,
        a.prix
    FROM 
        Messages m
    JOIN 
        Annonces a ON m.id_annonce = a.id_annonce
    JOIN 
        Utilisateurs u ON (
            CASE 
                WHEN m.id_expediteur = ? THEN m.id_destinataire
                ELSE m.id_expediteur
            END = u.id_utilisateur
        )
    WHERE 
        m.id_expediteur = ? OR m.id_destinataire = ?
    GROUP BY 
        m.id_annonce, id_interlocuteur
    ORDER BY 
        dernier_message DESC
");
$stmt->execute([$id_utilisateur, $id_utilisateur, $id_utilisateur, $id_utilisateur]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si on demande de voir une conversation spécifique
$conversation_active = null;
$messages = [];

if (isset($_GET['annonce']) && is_numeric($_GET['annonce']) && isset($_GET['utilisateur']) && is_numeric($_GET['utilisateur'])) {
    $id_annonce = $_GET['annonce'];
    $id_interlocuteur = $_GET['utilisateur'];
    
    // Récupérer les détails de l'annonce
    $stmt = $pdo->prepare("SELECT id_annonce, titre FROM Annonces WHERE id_annonce = ?");
    $stmt->execute([$id_annonce]);
    $conversation_active = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Récupérer les informations de l'interlocuteur
    $stmt = $pdo->prepare("SELECT id_utilisateur, nom_utilisateur FROM Utilisateurs WHERE id_utilisateur = ?");
    $stmt->execute([$id_interlocuteur]);
    $interlocuteur = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($conversation_active && $interlocuteur) {
        $conversation_active['interlocuteur'] = $interlocuteur;
        
        // Récupérer les messages de cette conversation
        $stmt = $pdo->prepare("
            SELECT 
                m.*,
                u.nom_utilisateur
            FROM 
                Messages m
            JOIN 
                Utilisateurs u ON m.id_expediteur = u.id_utilisateur
            WHERE 
                m.id_annonce = ?
                AND (
                    (m.id_expediteur = ? AND m.id_destinataire = ?)
                    OR
                    (m.id_expediteur = ? AND m.id_destinataire = ?)
                )
            ORDER BY 
                m.envoye_le ASC
        ");
        $stmt->execute([$id_annonce, $id_utilisateur, $id_interlocuteur, $id_interlocuteur, $id_utilisateur]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Si un nouveau message est envoyé
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nouveau_message'])) {
        $contenu = trim($_POST['contenu']);
        
        if (!empty($contenu)) {
            // Insérer le nouveau message
            $stmt = $pdo->prepare("
                INSERT INTO Messages 
                    (id_expediteur, id_destinataire, id_annonce, contenu) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$id_utilisateur, $id_interlocuteur, $id_annonce, $contenu]);
            
            // Rediriger pour éviter la soumission multiple du formulaire
            header("Location: messages.php?annonce=$id_annonce&utilisateur=$id_interlocuteur");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Messages - leboncoincoin</title>
    <link rel="stylesheet" href="message.css">   
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
        <h1>Mes Messages</h1>
        
        <div class="messages-container">
            <div class="conversations-list">
                <?php if (empty($conversations)): ?>
                    <p>Vous n'avez pas encore de messages.</p>
                <?php else: ?>
                    <h2>Mes conversations</h2>
                    <ul class="list-group">
                        <?php foreach ($conversations as $conv): ?>
                            <li class="list-group-item <?php echo (isset($_GET['annonce']) && $_GET['annonce'] == $conv['id_annonce'] && isset($_GET['utilisateur']) && $_GET['utilisateur'] == $conv['id_interlocuteur']) ? 'active' : ''; ?>">
                                <a href="messages.php?annonce=<?php echo $conv['id_annonce']; ?>&utilisateur=<?php echo $conv['id_interlocuteur']; ?>">
                                    <?php 
                                    // Récupérer l'image principale de l'annonce
                                    $stmt = $pdo->prepare("SELECT url_image FROM ImagesAnnonces WHERE id_annonce = ? AND est_principale = 1 LIMIT 1");
                                    $stmt->execute([$conv['id_annonce']]);
                                    $image = $stmt->fetch(PDO::FETCH_ASSOC);
                                    
                                    if ($image): ?>
                                        <img src="<?php echo htmlspecialchars($image['url_image']); ?>" alt="<?php echo htmlspecialchars($conv['titre_annonce']); ?>" class="conversation-thumbnail">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i>🖼️</i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="conversation-info">
                                        <strong><?php echo htmlspecialchars($conv['titre_annonce']); ?></strong>
                                        <span><?php echo htmlspecialchars($conv['nom_interlocuteur']); ?></span>
                                        <span class="price"><?php echo number_format($conv['prix'], 2, ',', ' '); ?> €</span>
                                        <small><?php echo date('d/m/Y H:i', strtotime($conv['dernier_message'])); ?></small>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
            <div class="conversation-messages">
                <?php if ($conversation_active): ?>
                    <div class="conversation-header">
                        <h2>
                            <span><?php echo htmlspecialchars($conversation_active['titre']); ?></span>
                            <small>avec <?php echo htmlspecialchars($conversation_active['interlocuteur']['nom_utilisateur']); ?></small>
                        </h2>
                        <a href="detail_annonce.php?id=<?php echo $conversation_active['id_annonce']; ?>" class="btn btn-secondary">
                            Voir l'annonce
                        </a>
                    </div>
                    
                    <div class="messages-list">
                        <?php if (empty($messages)): ?>
                            <p>Aucun message dans cette conversation.</p>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <div class="message <?php echo ($msg['id_expediteur'] == $id_utilisateur) ? 'message-sent' : 'message-received'; ?>">
                                    <div class="message-header">
                                        <span class="message-sender"><?php echo ($msg['id_expediteur'] == $id_utilisateur) ? 'Vous' : htmlspecialchars($msg['nom_utilisateur']); ?></span>
                                        <span class="message-date"><?php echo date('d/m/Y H:i', strtotime($msg['envoye_le'])); ?></span>
                                    </div>
                                    <div class="message-content">
                                        <?php echo nl2br(htmlspecialchars($msg['contenu'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" class="reply-form">
                        <textarea name="contenu" class="form-control" rows="3" placeholder="Votre réponse..." required></textarea>
                        <button type="submit" name="nouveau_message" class="btn">Envoyer</button>
                    </form>
                <?php else: ?>
                    <div class="no-conversation-selected">
                        <p>Sélectionnez une conversation pour afficher les messages</p>
                    </div>
                <?php endif; ?>
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
</body>
</html>