<?php
session_start();
require 'database.php';

// Récupérer l'utilisateur connecté
$username = '';
if (isset($_SESSION['user_id'])) {
    // Récupérer le nom d'utilisateur depuis la base de données
    $stmt_user = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt_user->execute([$_SESSION['user_id']]);
    $user = $stmt_user->fetch();
    $username = $user['username'];
}

// Vérifier si une recherche a été effectuée
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Préparer la requête SQL avec une recherche dynamique
if (!empty($searchQuery)) {
    $stmt = $pdo->prepare("SELECT videos.id AS video_id, videos.title, videos.thumbnail_url, videos.uploaded_at, users.username 
                           FROM videos 
                           JOIN users ON videos.user_id = users.id 
                           WHERE videos.title LIKE :searchQuery
                           ORDER BY videos.uploaded_at DESC");
    $stmt->execute(['searchQuery' => "%$searchQuery%"]);
} else {
    $stmt = $pdo->query("SELECT videos.id AS video_id, videos.title, videos.thumbnail_url, videos.uploaded_at, users.username 
                         FROM videos 
                         JOIN users ON videos.user_id = users.id 
                         ORDER BY videos.uploaded_at DESC");
}

$videos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toutes les Vidéos</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

    <!-- Barre de navigation avec le nom d'utilisateur et le bouton de déconnexion -->
    <div class="navbar">
        <?php if (!empty($username)): ?>
            <span>Bienvenue, <?php echo htmlspecialchars($username); ?></span>
            <a href="upload.php">Publier une vidéo</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Déconnexion</a>
        <?php else: ?>
            <a href="login.php">Se connecter</a>
        <?php endif; ?>
    </div>

    <h1>Toutes les Vidéos</h1>

    <!-- Formulaire de recherche -->
    <form method="GET" action="all_videos.php">
        <input type="text" name="search" placeholder="Rechercher une vidéo..." value="<?php echo htmlspecialchars($searchQuery); ?>" oninput="this.form.submit()">
    </form>

    <!-- Conteneur des vidéos -->
    <div id="video-container">
        <?php if (empty($videos)): ?>
            <p>Aucune vidéo trouvée.</p>
        <?php else: ?>
            <?php foreach ($videos as $video): ?>
                <div class="video">
                    <!-- Lien vers la page de détails de la vidéo -->
                    <a href="video_details.php?id=<?php echo $video['video_id']; ?>">
                        <h2><?php echo htmlspecialchars($video['title']); ?></h2>
                        <!-- Affichage de la miniature -->
                        <?php if (!empty($video['thumbnail_url'])): ?>
                            <img src="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" alt="Miniature" width="150">
                        <?php else: ?>
                            <p>Aucune miniature disponible.</p>
                        <?php endif; ?>
                    </a>
                    <p>Publié par : <?php echo htmlspecialchars($video['username']); ?> le <?php echo $video['uploaded_at']; ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>
</html>
