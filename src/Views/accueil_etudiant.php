<?php
session_start();
require "../Controllers/session_timeout.php"; // Gestion du timeout de session
require "../Controllers/Redirect.php";


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
</head>
<div class="uphf">
    <img src="../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<body>
<div class="logoEdu">
    <img src="../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>
<div class="sidebar">
    <ul>
        <li><a href="nt.php">Accueil</a></li>
        <li><a href="gererAbsEtu.php">Gérer des absences</a></li>
        <li><a href="#">Historique des absences</a></li>
        <li><a href="aide.php">Aides</a></li>
    </ul>
</div>

<header class="text">
    <h1> Bonjour, <?php echo $_SESSION['nom'] ?> </h1>
    <a href="gererAbsEtu.php">
        <button type="submit" class="btn">Consulter vos justificatifs</button>
    </a>
</header>
<footer class="footer">
    <nav class="footer-nav">
        <a href="/public/asset/img/Controllerslers/accueil_etudiant.php">Accueil</a>
        <span>|</span>
        <a href="aide.php">Aides</a>
    </nav>
</footer>
</body>
</html>
