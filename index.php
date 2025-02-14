<?php
session_start();

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['user_id']);
$username = $is_logged_in ? $_SESSION['username'] : "Se connecter";
$profile_link = $is_logged_in ? "profil.php" : "login.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MidanuApp</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/stylee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <nav class="navbar" style="background-color: #F8F8F8;">
        <div class="navbar-container">
            <div class="navbar-brand">
               <a href="index.php"> <img src="img/log.png" alt="logo" class="logo"></a>
            </div>
            <div class="nav-links">
                <div class="nav-item">
                    <a href="#" class="nav-link">Recettes
                    <ul class="dropdown">
                        <li><a href="all_videos.php">Voir les recettes</a></li>
                        <?php if ($is_logged_in) : ?>
                            <li><a href="upload.php">Publier une recette</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php if ($is_logged_in) : ?>
                    <a href="dashboard.php" class="nav-link">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <circle cx="12" cy="7" r="4"></circle>
                            <path d="M12 14c-5 0-8 2-8 4v1h16v-1c0-2-3-4-8-4z"></path>
                        </svg>
                        Dashboard
                    </a>
                <?php endif; ?>
                
                <p class="username">
                    <a class="nav-link" href="<?= $profile_link; ?>"><?= htmlspecialchars($username); ?></a>
                </p>
            </div>
        </div>
    </nav><br>

    <div class="search-container">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Faites des recherches pour commencer...">
        </div>
    </div><br><br>

    <div class="police">
        <h2>Bienvenue sur Midanu ! Quelle recette désirez-vous partager ou apprendre aujourd’hui ? 🤷‍♂️</h2>
    </div><br><br>

    <div class="main-title">
        <h1>Explorez les repas par continent</h1>
    </div><br><br>

    <div class="container">
        <div class="card"></div>
        <div class="card"></div>
    </div><br><br>

    <div class="container">
        <?php if (!$is_logged_in) : ?>
            <button class="btn" onclick="window.location.href='all_videos.php';">Regarder les vidéos</button>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="footer-link">
            <a href="#">À propos</a><br><br>
            <a href="admin_login.php">Admin</a>
        </div>
        <div class="footer-content">
            &copy; Midanu App. Tous droits réservés.
        </div>
        <div class="footer-social">
            <p>Suivez-nous sur 👉</p>
            <a href="https://www.facebook.com/" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="https://x.com/" target="_blank"><i class="fab fa-x"></i></a>
            <a href="https://www.instagram.com/" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://www.tiktok.com/" target="_blank"><i class="fab fa-tiktok"></i></a>
        </div>
    </footer>
</body>
</html>
