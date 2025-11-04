<?php
//Inclusion du fichier contenant les fonctions ou données nécessaires (ex : récupération de fichiers)
use src\Models\Redirect;

session_start();
require "../Models/Redirect.php";
$redirect = new Redirect('responsable_pedagogique');
$redirect->redirect();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <!-- Inclusion du css -->
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssGestionAbsResp.css" rel="stylesheet">
</head>
<!-- Affichage du logo de l'université -->
<div class="uphf">
    <img src="../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<body>
<!-- Affichage du logo EduTrack -->
<div class="logoEdu">
    <img src="../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>
<!-- Barre latérale de navigation -->
<div class="sidebar">
    <ul>
        <li><a href="sable.php">Accueil</a></li> <!-- Lien vers la page d'accueil -->
        <li><a href="../Views/gestionAbsResp.php">Gestion des absences</a></li> <!-- Lien vers la gestion des absences -->
        <li><a href="../Views/traitementDesJustificatif.php">Traitement des Justificatifs</a></li> <!-- Lien vers le traitementDesJustificatif -->
        <li><a href="#">Historique des absences</a></li> <!-- Lien vers l'historique (à compléter) -->
        <li><a href="#">Statistiques</a></li> <!-- Lien vers les statistiques (à compléter) -->
    </ul>
</div>
<header class="text">
    <h1> Bonjour, <?php echo $_SESSION['nom'] ?>  </h1>
    <!-- Message d'accueil, à personnaliser avec l'id du responsable -->
</header>
<!-- Bouton pour consulter les absences -->
<div class="text">
    <a href="#">
        <button type="submit" class="btn">Consulter les absences</button>
    </a>
</div>
<!-- Pied de page avec navigation -->
<footer class="footer">
    <nav class="footer-nav">
        <a href="/public/asset/img/Controllerslers/accueil_responsable.php">Accueil</a> <!-- Retour à l'accueil -->
        <span>|</span>
        <a href="../Views/aideResp.php">Aides</a> <!-- Lien vers la page d'aide -->
    </nav>
</footer>
</body>
</html>
