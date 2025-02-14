<?php
$host = "localhost"; // Hôte de la base de données (ex: localhost)
$dbname = "midanu"; // Remplace par le nom de ta base de données
$username = "root"; // Ton nom d'utilisateur MySQL (par défaut "root" sous XAMPP)
$password = ""; // Mot de passe MySQL (laisser vide sous XAMPP)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}
?>
