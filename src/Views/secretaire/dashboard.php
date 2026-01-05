<?php
session_start();
require __DIR__ . "/../../Controllers/session_timeout.php"; // Gestion du timeout de session
require __DIR__ . "/../../Controllers/Redirect.php";

use src\Controllers\Redirect;

$redirect = new Redirect('secretaire');
$redirect->redirect();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil Secrétaire</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/secretaire.css" rel="stylesheet">
</head>
<div class="uphf">
    <img src="../../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<body>
<div class="logoEdu">
    <img src="../../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>
<div class="sidebar">
    <ul>
        <li><a href="dashboard.php">Accueil</a></li>
        <li><a href="envoie_des_absences.php">Envoie des absences</a></li>
        <li><a href="/src/Controllers/profile.php">Mon profil</a></li>
        <li><a href="/src/Views/secretaire/aideSecr.php">Aides</a></li>
    </ul>
</div>

<header class="text">
    <h1> Bonjour, <?php echo isset($_SESSION['nom']) ? $_SESSION['nom'] : 'Secrétaire' ?> </h1>
    <a href="envoie_des_absences.php">
        <button type="submit" class="btn">Envoie des absences</button>
    </a>
</header>
<footer class="footer">
    <nav class="footer-nav">
        <a href="dashboard.php">Accueil</a>
        <span>|</span>
        <a href="/src/Views/secretaire/aideSecr.php">Aides</a>
    </nav>
</footer>
</body>
</html>