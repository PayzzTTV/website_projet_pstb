<?php
session_start();
// Connexion à la base de données
$id = mysqli_connect("localhost", "root", "", "leboncoincoin");

if (!$id) {
    die("<div class='error'>Erreur de connexion à la base de données : " . mysqli_connect_error() . "</div>");
}

if (isset($_POST['submit'])) {
    $mail = $_POST['mail'];
    $mdp = $_POST['mdp'];
    $mdp2 = $_POST['mdp2'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $sexe = $_POST['sexe'];
    $date_naissance = $_POST['date_naissance'];

    // Vérification si l'email existe déjà
    $query = "SELECT * FROM users WHERE mail = ?";
    $stmt = mysqli_prepare($id, $query);
    mysqli_stmt_bind_param($stmt, "s", $mail);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res) > 0) {
        echo "<div class='error'>L'email est déjà utilisé.</div>";
        $ligne = mysqli_fetch_assoc($res);
        $_SESSION['idu'] = $ligne['idu'];
        $_SESSION['prenom'] = $ligne['prenom'];
        $_SESSION['nom'] = $ligne['nom'];
        $_SESSION['role'] = $ligne['role'];
        header("Location: accueil.php");
    } else {
        if ($mdp == $mdp2) {
            // Connexion à la base de données avec PDO
            try {
                $pdo = new PDO('mysql:host=localhost;dbname=leboncoincoin', 'root', '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Préparation de la requête d'insertion
                $stmt = $pdo->prepare("INSERT INTO users (mail, mdp, nom, prenom, sexe, date_naissance) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$mail, password_hash($mdp, PASSWORD_BCRYPT), $nom, $prenom, $sexe, $date_naissance]);

                echo "<div class='success'>Inscription réussie !</div>";
            } catch (PDOException $e) {
                echo "<div class='error'>Erreur : " . $e->getMessage() . "</div>";
            }
        } else {
            echo "<div class='error'>Les mots de passe ne correspondent pas.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'inscription</title>
</head>
<body>
    <h1>Formulaire d'inscription</h1>
    <form action="" method="post">
        <input type="email" name="mail" placeholder="Email" required>
        <input type="password" name="mdp" placeholder="Mot de passe" required>
        <input type="password" name="mdp2" placeholder="Confirmez le mot de passe" required>
        <input type="text" name="nom" placeholder="Nom" required>
        <input type="text" name="prenom" placeholder="Prénom" required>
        <select name="sexe" required>
            <option value="">Sélectionnez votre sexe</option>
            <option value="Homme">Homme</option>
            <option value="Femme">Femme</option>
        </select>
        <input type="date" name="date_naissance" placeholder="Date de naissance" required>
        <input type="submit" value="S'inscrire" name="submit">
    </form>
</body>
</html>