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

    if (!$comment) {
        echo "Vous n'avez pas l'autorisation de modifier ce commentaire.";
        exit();
    }

    // Traitement de la modification
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_comment = $_POST['comment'];

        $stmt = $pdo->prepare("UPDATE comments SET comment = ? WHERE id = ?");
        $stmt->execute([$new_comment, $comment_id]);

        header("Location: all_videos.php");
        exit();
    }
} else {
    echo "Aucun commentaire à modifier.";
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Commentaire</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h2>Modifier le Commentaire</h2>

    <form action="edit_comment.php?comment_id=<?php echo $comment_id; ?>" method="POST">
        <textarea name="comment" required><?php echo htmlspecialchars($comment['comment']); ?></textarea>
        <button type="submit">Modifier</button>
    </form>

</body>
</html>
