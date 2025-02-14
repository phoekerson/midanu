<?php
session_start();
require 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $user_id = $_SESSION['user_id'];
    $error = "";

    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $video_tmp = $_FILES['video']['tmp_name'];
        $video_ext = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
        $video_path = 'uploads/videos/' . uniqid() . '.' . $video_ext;

        if (move_uploaded_file($video_tmp, $video_path)) {
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
                $thumbnail_tmp = $_FILES['thumbnail']['tmp_name'];
                $thumbnail_ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
                $thumbnail_path = 'uploads/thumbnails/' . uniqid() . '.' . $thumbnail_ext;

                if (in_array($thumbnail_ext, ['jpg', 'jpeg', 'png'])) {
                    if (move_uploaded_file($thumbnail_tmp, $thumbnail_path)) {
                        $stmt = $pdo->prepare("INSERT INTO videos (title, video_url, thumbnail_url, user_id, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmt->execute([$title, $video_path, $thumbnail_path, $user_id]);
                        header("Location: all_videos.php");
                        exit();
                    } else {
                        $error = "❌ Erreur lors du téléchargement de la miniature.";
                    }
                } else {
                    $error = "❌ Seules les images JPG, JPEG et PNG sont autorisées.";
                }
            } else {
                $error = "❌ Veuillez télécharger une miniature.";
            }
        } else {
            $error = "❌ Erreur lors du téléchargement de la vidéo.";
        }
    } else {
        $error = "❌ Veuillez télécharger une vidéo.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Télécharger une vidéo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #0D0D0D;
        }
        .glow {
            box-shadow: 0 0 10px rgba(255, 102, 0, 0.7);
        }
        .input-glow:focus {
            border-color: #FF6600;
            box-shadow: 0 0 10px rgba(255, 102, 0, 0.8);
        }
    </style>
</head>
<body class="flex justify-center items-center min-h-screen">

    <div class="w-full max-w-md bg-gray-900 text-white shadow-lg rounded-lg p-6 glow">
        <h2 class="text-2xl font-semibold text-center text-orange-500">📤 Télécharger une vidéo</h2>

        <?php if (isset($error)) : ?>
            <div class="bg-red-600 text-white px-4 py-2 rounded mt-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="upload.php" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300">🎬 Titre de la vidéo</label>
                <input type="text" name="title" required class="mt-1 block w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-md input-glow focus:ring-orange-500 focus:border-orange-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300">📹 Choisir la vidéo</label>
                <input type="file" name="video" accept="video/*" required class="mt-1 block w-full text-gray-300">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300">🖼️ Choisir la miniature</label>
                <input type="file" name="thumbnail" accept="image/jpeg, image/png, image/jpg" required class="mt-1 block w-full text-gray-300">
            </div>

            <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2 rounded-md font-semibold text-lg transition duration-300">Télécharger</button>
        </form>
    </div>

</body>
</html>
