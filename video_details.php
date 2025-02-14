<?php
session_start();
require 'database.php';

// Vérifier si l'ID de la vidéo est passé en paramètre
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Aucune vidéo spécifiée.");
}

$video_id = $_GET['id'];
$error = "";

// Ajouter un commentaire si soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    if (!empty($_POST['comment'])) {
        $comment = trim($_POST['comment']);
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, video_id, comment, posted_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$_SESSION['user_id'], $video_id, $comment]);
        header("Location: video_details.php?id=" . $video_id);
        exit();
    } else {
        $error = "⚠️ Le commentaire ne peut pas être vide.";
    }
}

// Modifier un commentaire
if (isset($_POST['edit_comment'])) {
    $comment_id = $_POST['comment_id'];
    $new_comment = trim($_POST['new_comment']);

    if (!empty($new_comment)) {
        $stmt = $pdo->prepare("UPDATE comments SET comment = ?, posted_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->execute([$new_comment, $comment_id, $_SESSION['user_id']]);
        header("Location: video_details.php?id=" . $video_id);
        exit();
    }
}

// Supprimer un commentaire
if (isset($_POST['delete_comment'])) {
    $comment_id = $_POST['comment_id'];
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    $stmt->execute([$comment_id, $_SESSION['user_id']]);
    header("Location: video_details.php?id=" . $video_id);
    exit();
}

// Récupérer les détails de la vidéo
$stmt_video = $pdo->prepare("SELECT videos.title, videos.video_url, videos.thumbnail_url, videos.uploaded_at, users.username 
                             FROM videos 
                             JOIN users ON videos.user_id = users.id 
                             WHERE videos.id = ?");
$stmt_video->execute([$video_id]);
$video = $stmt_video->fetch();

// Si la vidéo n'existe pas
if (!$video) {
    die("Vidéo introuvable.");
}

// Récupérer les commentaires de la vidéo
$stmt_comments = $pdo->prepare("SELECT comments.id, comments.comment, comments.posted_at, users.username 
                                FROM comments 
                                JOIN users ON comments.user_id = users.id 
                                WHERE comments.video_id = ? 
                                ORDER BY comments.posted_at DESC");
$stmt_comments->execute([$video_id]);
$comments = $stmt_comments->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($video['title']); ?></title>
    <link rel="stylesheet" href="styles/video.css">
</head>
<body>

    <div class="navbar">
        <span class="logo"> <a href="all_videos.php">🔥 Miduna</span></a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="user-info">Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a class="btn" href="logout.php">Déconnexion</a>
        <?php else: ?>
            <a class="btn" href="login.php">Se connecter</a>
        <?php endif; ?>
    </div>

    <div class="container">
        <h1><?php echo htmlspecialchars($video['title']); ?></h1>

        <div class="video-container">
            <video controls poster="<?php echo htmlspecialchars($video['thumbnail_url']); ?>">
                <source src="<?php echo htmlspecialchars($video['video_url']); ?>" type="video/mp4">
                Votre navigateur ne supporte pas la lecture de vidéos.
            </video>
        </div>

        <p class="uploaded-by">Publié par <strong><?php echo htmlspecialchars($video['username']); ?></strong> le <?php echo $video['uploaded_at']; ?></p>

        <div id="comments-section">
            <h2>💬 Commentaires</h2>
            <?php if (empty($comments)): ?>
                <p>Aucun commentaire pour cette vidéo.</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <p class="comment-author"><?php echo htmlspecialchars($comment['username']); ?> :</p>
                        <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                        <p class="comment-date"><?php echo $comment['posted_at']; ?></p>

                        <?php if ($comment['username'] == $_SESSION['username']): ?>
                            <!-- Formulaire de modification du commentaire -->
                            <form method="POST" action="" class="comment-form">
                                <textarea name="new_comment" placeholder="Modifier votre commentaire..." required></textarea>
                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                <button type="submit" name="edit_comment">Modifier</button>
                            </form>

                            <!-- Bouton de suppression du commentaire -->
                            <form method="POST" action="" class="delete-form">
                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                <button type="submit" name="delete_comment" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')">Supprimer</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Formulaire pour ajouter un commentaire -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" action="" class="comment-form">
                    <textarea name="comment" placeholder="💬 Ajoutez un commentaire..." required></textarea>
                    <button type="submit">Envoyer</button>
                </form>
                <?php if (!empty($error)): ?>
                    <p class="error-message"><?php echo $error; ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p class="login-prompt">🔒 Vous devez être connecté pour commenter. <a href="login.php">Se connecter</a></p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
