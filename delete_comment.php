<?php
session_start();
require 'database.php';

// Vérification si l'utilisateur est connecté et s'il est bien celui qui a posté le commentaire
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['comment_id'])) {
    $comment_id = $_GET['comment_id'];

    // Vérifier si le commentaire appartient à l'utilisateur connecté
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ? AND user_id = ?");
    $stmt->execute([$comment_id, $_SESSION['user_id']]);
    $comment = $stmt->fetch();

    if ($comment) {
        // Supprimer le commentaire
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);

        header("Location: all_videos.php");
        exit();
    } else {
        echo "Vous n'avez pas l'autorisation de supprimer ce commentaire.";
        exit();
    }
} else {
    echo "Aucun commentaire à supprimer.";
    exit();
}
