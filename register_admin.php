<?php
require 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars($_POST["username"]);
    $email = htmlspecialchars($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $sql = "INSERT INTO admins (username, email, password) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([$username, $email, $password]);
        echo "Inscription réussie ! <a href='login_admin.php'>Se connecter</a>";
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Administrateur</title>
    <link rel="stylesheet" href="styles/register_admin.css"> <!-- Lien vers ton fichier CSS -->
</head>
<body>

    <div class="register-container">
        <h2>Inscription Administrateur</h2>

        <!-- Formulaire d'inscription -->
        <form method="post">
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>

            <label for="email">Email :</label>
            <input type="email" name="email" placeholder="Email" required>

            <label for="password">Mot de passe :</label>
            <input type="password" name="password" placeholder="Mot de passe" required>

            <button type="submit">S'inscrire</button>
        </form>

        <!-- Lien retour vers la connexion -->
        <p>Vous avez déjà un compte ? <a href="login_admin.php">Connectez-vous ici</a></p>
    </div>

</body>
</html>
