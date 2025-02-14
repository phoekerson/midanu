<?php
session_start();
require 'database.php';

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$video_id = intval($_GET['id']);

// Vérifier si la vidéo appartient à l'utilisateur connecté
$stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ? AND user_id = ?");
$stmt->execute([$video_id, $user_id]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
    header("Location: user_videos.php");
    exit();
}

$error = "";
$success = "";

// Mise à jour du titre, de la vidéo et/ou de la miniature
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_title = trim($_POST['title']);
    $video_path = $video['video_url']; // Chemin de l'ancienne vidéo
    $thumbnail_path = $video['thumbnail_url']; // Chemin de l'ancienne miniature

    // Vérifier si une nouvelle vidéo est uploadée
    if (!empty($_FILES['new_video']['name'])) {
        $target_dir = "uploads/videos/"; // Dossier de stockage des vidéos
        $new_video_name = $target_dir . basename($_FILES["new_video"]["name"]);
        $videoFileType = strtolower(pathinfo($new_video_name, PATHINFO_EXTENSION));

        // Vérifier le format du fichier
        if (!in_array($videoFileType, ["mp4", "mov", "avi"])) {
            $error = "Seuls les fichiers MP4, MOV et AVI sont autorisés.";
        } else {
            // Supprimer l'ancienne vidéo si elle existe
            if (file_exists($video_path)) {
                unlink($video_path);
            }

            // Déplacer le nouveau fichier uploadé
            if (move_uploaded_file($_FILES["new_video"]["tmp_name"], $new_video_name)) {
                $video_path = $new_video_name;
            } else {
                $error = "Erreur lors de l'upload de la vidéo.";
            }
        }
    }

    // Vérifier si une nouvelle miniature est uploadée
    if (!empty($_FILES['new_thumbnail']['name'])) {
        $target_dir = "uploads/thumbnails/"; // Dossier de stockage des miniatures
        $new_thumbnail_name = $target_dir . basename($_FILES["new_thumbnail"]["name"]);
        $thumbnailFileType = strtolower(pathinfo($new_thumbnail_name, PATHINFO_EXTENSION));

        // Vérifier le format du fichier
        if (!in_array($thumbnailFileType, ["jpg", "jpeg", "png"])) {
            $error = "Seuls les fichiers JPG, JPEG et PNG sont autorisés pour la miniature.";
        } else {
            // Supprimer l'ancienne miniature si elle existe
            if (file_exists($thumbnail_path)) {
                unlink($thumbnail_path);
            }

            // Déplacer la nouvelle miniature uploadée
            if (move_uploaded_file($_FILES["new_thumbnail"]["tmp_name"], $new_thumbnail_name)) {
                $thumbnail_path = $new_thumbnail_name;
            } else {
                $error = "Erreur lors de l'upload de la miniature.";
            }
        }
    }

    // Mise à jour en base de données
    if (empty($error)) {
        $stmt = $pdo->prepare("UPDATE videos SET title = ?, video_url = ?, thumbnail_url = ? WHERE id = ?");
        if ($stmt->execute([$new_title, $video_path, $thumbnail_path, $video_id])) {
            $_SESSION['message'] = "Vidéo mise à jour avec succès.";
            header("Location: user_videos.php");
            exit();
        } else {
            $error = "Erreur lors de la mise à jour.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la vidéo</title>
    <link rel="stylesheet" href="styles/edit_video.css">
</head>
<body>
    <div class="container">
        <h2>✏️ Modifier la Vidéo</h2>

        <?php if (!empty($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
        <?php if (!empty($success)) { echo "<p style='color:green;'>$success</p>"; } ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <label for="title">📝 Nouveau titre :</label>
            <input type="text" name="title" value="<?= htmlspecialchars($video['title']) ?>" required>

            <label for="new_video">📹 Nouvelle vidéo (facultatif) :</label>
            <input type="file" name="new_video" accept="video/*">

            <label for="new_thumbnail">🖼️ Nouvelle miniature (facultatif) :</label>
            <input type="file" name="new_thumbnail" accept="image/*">

            <button type="submit">✅ Modifier</button>
        </form>

        <p><a href="user_videos.php">⬅️ Retour</a></p>
    </div>
</body>
</html>

