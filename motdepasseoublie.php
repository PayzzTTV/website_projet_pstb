<!-- filepath: c:\xampp\htdocs\sitewebproject2K25\motdepasseoublie.php -->
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Vérification de l'email (exemple basique)
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Simuler l'envoi d'un email (à remplacer par une vraie logique d'envoi)
        $resetLink = "http://example.com/resetpassword.php?token=" . md5($email . time());
        echo "<p style='color: green;'>Un lien de réinitialisation a été envoyé à votre adresse e-mail.</p>";
        // Ici, vous pouvez utiliser mail() ou une bibliothèque comme PHPMailer pour envoyer l'email
    } else {
        echo "<p style='color: red;'>Veuillez entrer une adresse e-mail valide.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Mot de passe oublié</h1>
        <form method="POST" action="">
            <label for="email">Adresse e-mail :</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Envoyer</button>
        </form>
    </div>
</body>
</html>