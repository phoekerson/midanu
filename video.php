<?php
session_start();

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Vérifier si l'ID de la vidéo est présent et valide dans l'URL
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("ID de vidéo manquant ou invalide.");
}
$videoId = (int)$_GET['id'];

// Connexion à la base de données
$host = "localhost";
$dbname = "miduna";
$usernameDB = "root";
$passwordDB = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usernameDB, $passwordDB);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les informations de la vidéo
    $query = "SELECT video_title, video_path FROM uploads WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':id', $videoId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $video = $stmt->fetch(PDO::FETCH_ASSOC);
        $title = htmlspecialchars($video['video_title']);
        $videoPath = $video['video_path'];

        // Vérifier si le fichier vidéo existe
        if (!file_exists($videoPath)) {
            die("Le fichier vidéo est introuvable : " . $videoPath);
        }
    } else {
        die("Aucune vidéo trouvée avec cet ID.");
    }

    // Récupérer les commentaires
    $queryComments = "SELECT c.*, u.username 
                      FROM comments c 
                      JOIN users u ON c.user_id = u.id 
                      WHERE c.video_id = :video_id 
                      ORDER BY c.created_at DESC";
    $stmtComments = $pdo->prepare($queryComments);
    $stmtComments->bindValue(':video_id', $videoId, PDO::PARAM_INT);
    $stmtComments->execute();
    $comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

    // Traitement de l'ajout d'un nouveau commentaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
        $commentText = trim($_POST['comment']);
        if (!empty($commentText)) {
            $queryAddComment = "INSERT INTO comments (video_id, user_id, comment_text, created_at) 
                               VALUES (:video_id, :user_id, :comment_text, NOW())";
            $stmtAddComment = $pdo->prepare($queryAddComment);
            $stmtAddComment->execute([
                ':video_id' => $videoId,
                ':user_id' => $_SESSION['user_id'],
                ':comment_text' => $commentText
            ]);
            
            // Rediriger pour éviter la soumission multiple du formulaire
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $videoId);
            exit();
        }
    }

    // Traitement de la mise à jour d'un commentaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_comment'])) {
        $editedCommentText = trim($_POST['edited_comment']);
        $commentId = (int)$_POST['comment_id'];

        if (!empty($editedCommentText)) {
            $queryUpdateComment = "UPDATE comments SET comment_text = :comment_text WHERE id = :id AND user_id = :user_id";
            $stmtUpdateComment = $pdo->prepare($queryUpdateComment);
            $stmtUpdateComment->execute([
                ':comment_text' => $editedCommentText,
                ':id' => $commentId,
                ':user_id' => $_SESSION['user_id']
            ]);

            // Rediriger pour éviter la soumission multiple du formulaire
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $videoId);
            exit();
        }
    }

    // Traitement de la suppression d'un commentaire
    if (isset($_GET['delete_comment']) && ctype_digit($_GET['delete_comment'])) {
        $deleteCommentId = (int)$_GET['delete_comment'];

        $queryDeleteComment = "DELETE FROM comments WHERE id = :id AND user_id = :user_id";
        $stmtDeleteComment = $pdo->prepare($queryDeleteComment);
        $stmtDeleteComment->execute([
            ':id' => $deleteCommentId,
            ':user_id' => $_SESSION['user_id']
        ]);

        // Rediriger pour éviter la suppression multiple
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $videoId);
        exit();
    }
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Vidéo non trouvée'; ?></title>
    <link rel="stylesheet" href="styles/video.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="index.php">
                    <img src="img/log.png" alt="logo" class="logo">
                </a>
            </div>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Accueil</a>
                <a href="publier.php" class="nav-link">Publier une recette</a>
                <p class="username">
                    <a class="nav-link" href="profil.php">
                        <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Invité'; ?>
                    </a>
                </p>
            </div>
        </div>
    </nav>

    <div class="video-player-container">
        <?php if (isset($videoPath) && file_exists($videoPath)) : ?>
            <div class="video-player">
                <video controls width="100%">
                    <source src="<?php echo $videoPath; ?>" type="video/mp4">
                    Votre navigateur ne supporte pas la lecture de vidéos.
                </video>
            </div>
            <div class="video-details">
                <h1><?php echo isset($title) ? $title : 'Titre inconnu'; ?></h1>
                <p><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Invité'; ?></p>
            </div><br>
            <div class="comments-section">
                <h2>Commentaires</h2>
                
                <!-- Formulaire d'ajout de commentaire -->
                <form method="POST" class="comment-form">
                    <textarea name="comment" placeholder="Ajouter un commentaire..." required></textarea>
                    <button type="submit">Publier</button>
                </form>

                <!-- Liste des commentaires -->
                <div class="comments-list">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <div class="comment-header">
                                    <span class="comment-author"><?php echo htmlspecialchars($comment['username']); ?></span>
                                    <span class="comment-date"><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></span>
                                </div>
                                <div class="comment-content">
                                    <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
                                </div>
                                <?php if ($comment['user_id'] === $_SESSION['user_id']): ?>
                                    <div class="comment-actions">
                                        <a href="?id=<?php echo $videoId; ?>&edit_comment=<?php echo $comment['id']; ?>" class="edit-link">Modifier</a>
                                        <a href="?id=<?php echo $videoId; ?>&delete_comment=<?php echo $comment['id']; ?>" class="delete-link" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?');">Supprimer</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun commentaire pour le moment.</p>
                    <?php endif; ?>
                </div>

                <!-- Formulaire de modification de commentaire -->
                <?php if (isset($_GET['edit_comment']) && ctype_digit($_GET['edit_comment'])): ?>
                    <?php
                    $editCommentId = (int)$_GET['edit_comment'];
                    $queryEditComment = "SELECT * FROM comments WHERE id = :id AND user_id = :user_id";
                    $stmtEditComment = $pdo->prepare($queryEditComment);
                    $stmtEditComment->execute([
                        ':id' => $editCommentId,
                        ':user_id' => $_SESSION['user_id']
                    ]);
                    $editComment = $stmtEditComment->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <?php if ($editComment): ?>
                        <form method="POST" class="edit-comment-form">
                            <textarea name="edited_comment"><?php echo htmlspecialchars($editComment['comment_text']); ?></textarea>
                            <input type="hidden" name="comment_id" value="<?php echo $editComment['id']; ?>">
                            <button type="submit" name="update_comment">Mettre à jour</button>
                            <a href="?id=<?php echo $videoId; ?>" class="cancel-link">Annuler</a>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <p>La vidéo demandée n'existe pas ou n'est pas accessible.</p>
        <?php endif; ?>
    </div>
</body>
</html>