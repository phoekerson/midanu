<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des vidéos</title>
    <link rel="stylesheet" href="styles/styl.css">
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
                <a href="index.php" class="nav-link">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Accueil
                </a>
                <a href="publier.php" class="nav-link">Publier une recette</a>
                <p class="username"><a class="nav-link" href="profil.php"><?php echo htmlspecialchars($username); ?></a></p>
            </div>
        </div>
    </nav>

    <div class="search-container">
        <form method="GET" action="">
            <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Rechercher une recette..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit">Rechercher</button>
            </div>
        </form>
    </div>

    <div class="row">
        <?php
        $host = "localhost";
        $dbname = "miduna";
        $usernameDB = "root";
        $passwordDB = "";

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usernameDB, $passwordDB);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $limit = 12; // Nombre de vidéos par page
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $limit;

            $query = "SELECT id, video_title, video_path, thumbnail_path FROM uploads";
            if (!empty($search)) {
                $query .= " WHERE video_title LIKE :search";
            }
            $query .= " ORDER BY upload_date DESC LIMIT :limit OFFSET :offset";

            $stmt = $pdo->prepare($query);
            if (!empty($search)) {
                $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $videoId = $row['id'];
                    $title = htmlspecialchars($row['video_title']);
                    $videoPath = htmlspecialchars($row['video_path']);
                    $thumbnailPath = htmlspecialchars($row['thumbnail_path']);
                    echo '<a href="video.php?id=' . $videoId . '" class="video-link">';
                    echo '<div class="video-card">';
                    echo '<div class="thumbnail">';
                    echo '<img src="' . $thumbnailPath . '" alt="Miniature vidéo">';
                    echo '</div>';
                    echo '<div class="video-info">';
                    echo '<h3 class="video-title">' . $title . '</h3>';
                    echo '<div class="video-metadata">';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</a>';
                }
            } else {
                echo '<p>Aucune vidéo trouvée.</p>';
            }

            // Pagination
            $countQuery = "SELECT COUNT(*) FROM uploads";
            if (!empty($search)) {
                $countQuery .= " WHERE video_title LIKE :search";
            }
            $countStmt = $pdo->prepare($countQuery);
            if (!empty($search)) {
                $countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            }
            $countStmt->execute();
            $totalResults = $countStmt->fetchColumn();
            $totalPages = ceil($totalResults / $limit);

            echo '<div class="pagination">';
            for ($i = 1; $i <= $totalPages; $i++) {
                echo '<a href="?page=' . $i . '&search=' . urlencode($search) . '"' . ($i == $page ? ' class="active"' : '') . '>' . $i . '</a> ';
            }
            echo '</div>';
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
        ?>
    </div>
</body>
</html>