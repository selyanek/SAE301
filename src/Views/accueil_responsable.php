<?php
//Inclusion du fichier contenant les fonctions ou données nécessaires (ex : récupération de fichiers)
use src\Controllers\Redirect;

session_start();
require __DIR__ . "/../Controllers/session_timeout.php"; // Gestion du timeout de session
require __DIR__ . "/../Controllers/Redirect.php";
$redirect = new Redirect('responsable_pedagogique');
$redirect->redirect();

// Connexion à la base de données pour récupérer le nombre d'absences en attente
require __DIR__ . '/../Database/Database.php';
require __DIR__ . '/../Models/Absence.php';

$db = new \src\Database\Database();
$pdo = $db->getConnection();
$absenceModel = new \src\Models\Absence($pdo);

// Récupérer le nombre d'absences en attente
$nombreAbsencesEnAttente = $absenceModel->countEnAttente();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <!-- Inclusion du css -->
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssGestionAbsResp.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssAccueilResp.css" rel="stylesheet">
</head>
<body>
<!-- Affichage du logo de l'université -->
<div class="uphf">
    <img src="../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<!-- Affichage du logo EduTrack -->
<div class="logoEdu">
    <img src="../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>
<!-- Barre latérale de navigation -->
<div class="sidebar">
    <ul>
        <li><a href="accueil_responsable.php">Accueil</a></li>
        <li><a href="/src/Controllers/profile.php">Mon profil</a></li>
        <li><a href="/src/Views/gestionAbsResp.php">Gestion des absences</a></li>
        <li><a href="historiqueAbsResp.php">Historique des absences</a></li>
        <li><a href="/src/Views/responsable/statistiques.php">Statistiques</a></li>
    </ul>
</div>
<header class="text fade-in">
    <h1>Bonjour, <?php echo $_SESSION['nom'] ?> !</h1>
    <!-- Message d'accueil, à personnaliser avec l'id du responsable -->
</header>

<!-- Affichage du nombre d'absences en attente -->
<div class="text fade-in">
    <div class="info-box">
        <p class="info-label">Absences en attente de traitement</p>
        <p class="info-value"><?php echo $nombreAbsencesEnAttente; ?></p>
    </div>
</div>

<!-- Bouton pour consulter les absences -->
<div class="text action-section fade-in">
    <a href="/src/Views/gestionAbsResp.php">
        <button type="submit" class="btn">Consulter les absences en cours</button>
    </a>
</div>
<!-- Pied de page avec navigation -->
<footer class="footer">
    <nav class="footer-nav">
        <a href="accueil_responsable.php">Accueil</a>
        <span>|</span>
        <a href="aideResp.php">Aides</a>
    </nav>
</footer>
</body>
</html>