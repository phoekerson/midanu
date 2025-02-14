<?php
session_start();
require 'database.php';

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Suppression d'une vidéo
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Vérifier si la vidéo appartient à l'utilisateur connecté
    $stmt = $pdo->prepare("SELECT video_url FROM videos WHERE id = ? AND user_id = ?");
    $stmt->execute([$delete_id, $user_id]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($video) {
        $video_path = $video['video_url'];

        // Supprimer la vidéo de la base de données
        $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
        if ($stmt->execute([$delete_id])) {
            // Supprimer le fichier vidéo du serveur
            if (file_exists($video_path)) {
                unlink($video_path);
            }
            $_SESSION['message'] = "✅ Vidéo supprimée avec succès.";
        } else {
            $_SESSION['message'] = "❌ Erreur lors de la suppression.";
        }
    }

    header("Location: user_videos.php");
    exit();
}

// Récupérer les vidéos de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM videos WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$user_id]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes vidéos</title>
    <link rel="stylesheet" href="styles/dashboard.css">
</head>
<body>

    <div class="navbar">
        <span class="logo">🎬 Mes Vidéos</span>
        <a class="btn" href="dashboard.php">🏠 Retour</a>
    </div>

    <div class="container">
        <h2>📂 Liste de mes vidéos</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <p class="message"><?= htmlspecialchars($_SESSION['message']) ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="video-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Vidéo</th>
                        <th>Date d'upload</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($videos as $video): ?>
                        <tr>
                            <td><?= htmlspecialchars($video['id']) ?></td>
                            <td><?= htmlspecialchars($video['title']) ?></td>
                            <td>
                                <video width="150" controls>
                                    <source src="<?= htmlspecialchars($video['video_url']) ?>" type="video/mp4">
                                    Votre navigateur ne supporte pas la lecture de vidéos.
                                </video>
                            </td>
                            <td><?= htmlspecialchars($video['uploaded_at']) ?></td>
                            <td>
                                <a class="edit-btn" href="edit_video.php?id=<?= htmlspecialchars($video['id']) ?>">✏️ Modifier</a>
                                <a class="delete-btn" href="user_videos.php?delete_id=<?= htmlspecialchars($video['id']) ?>" 
                                   onclick="return confirm('❗ Voulez-vous vraiment supprimer cette vidéo ?');">
                                    🗑️ Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
