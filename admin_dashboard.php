<?php
session_start();
require 'database.php';

// Vérification de l'authentification de l'admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

// Suppression de vidéo si un ID est fourni
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Récupérer le chemin de la vidéo avant suppression
    $stmt = $pdo->prepare("SELECT video_url FROM videos WHERE id = ?");
    $stmt->execute([$delete_id]);
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
            $_SESSION['message'] = "Vidéo supprimée avec succès.";
        } else {
            $_SESSION['message'] = "Erreur lors de la suppression.";
        }
    }

    header("Location: admin_dashboard.php");
    exit();
}

// Récupération de toutes les vidéos
$stmt = $pdo->prepare("SELECT videos.id, videos.title, videos.video_url, videos.uploaded_at, users.username 
                       FROM videos 
                       JOIN users ON videos.user_id = users.id 
                       ORDER BY videos.uploaded_at DESC");
$stmt->execute();
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Administrateur</title>
    <link rel="stylesheet" href="styles/admin_dashboard.css">
</head>
<body>

    <h2>Tableau de bord administrateur</h2>

    <!-- Affichage du message de succès ou d'erreur -->
    <?php
    if (isset($_SESSION['message'])) {
        echo "<p>" . htmlspecialchars($_SESSION['message']) . "</p>";
        unset($_SESSION['message']);
    }
    ?>

    <!-- Tableau des vidéos -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Publié par</th>
                <th>Vidéo</th>
                <th>Date d'upload</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($videos as $video): ?>
                <tr>
                    <td><?= htmlspecialchars($video['id']) ?></td>
                    <td><?= htmlspecialchars($video['title']) ?></td>
                    <td><?= htmlspecialchars($video['username']) ?></td>
                    <td>
                        <video controls>
                            <source src="<?= htmlspecialchars($video['video_url']) ?>" type="video/mp4">
                            Votre navigateur ne supporte pas la lecture de vidéos.
                        </video>
                    </td>
                    <td><?= htmlspecialchars($video['uploaded_at']) ?></td>
                    <td>
                        <a href="admin_dashboard.php?delete_id=<?= htmlspecialchars($video['id']) ?>" 
                           onclick="return confirm('Voulez-vous vraiment supprimer cette vidéo ?');">
                            Supprimer
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Lien de déconnexion -->
    <p><a href="logout.php">Déconnexion</a></p>

</body>
</html>
