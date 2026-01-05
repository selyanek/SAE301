<?php
session_start();
require __DIR__ . "/../Controllers/session_timeout.php"; // Gestion du timeout de session
require __DIR__ . "/../Controllers/Redirect.php";

use src\Controllers\Redirect;

$redirect = new Redirect('professeur');
$redirect->redirect();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Aide</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
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
        <li><a href="accueil_prof.php">Accueil</a></li>
        <li><a href="rattrapage_prof.php">Rattrapages</a></li>
        <li><a href="/src/Controllers/profile.php">Mon profil</a></li>
        <li><a href="aide.php">Aides</a></li>
    </ul>
</div>

<section class="text">
    <h1>Aide</h1>
    <p>EduTrack est l'application officielle de l'IUT qui permet aux professeurs de gérer les rattrapages pour les étudiants absents lors des évaluations. Vous pouvez consulter les absences justifiées et planifier les sessions de rattrapage directement depuis la plateforme.</p>

    <h2>Comment ça marche ?</h2>

    <ol>
        <li>Consultez les absences justifiées lors de vos évaluations</li>
        <li>Planifiez une date et une salle de rattrapage</li>
        <li>Ajoutez des remarques si nécessaire</li>
        <li>Suivez l'état des rattrapages (en attente, validé, effectué)</li>
    </ol>

    <a href="https://moodle.uphf.fr/course/view.php?id=3785" target="_blank">Règlement intérieur sur les absences</a>

    <p>Pensez à planifier les rattrapages dans les délais afin d'assurer un suivi optimal des évaluations manquées.</p>

    <a href="accueil_prof.php"><button type="button" class="btn">Retour à l'accueil</button></a>
</section>

<!-- Pied de page avec navigation -->
<footer class="footer">
    <nav class="footer-nav">
        <a href="accueil_prof.php">Accueil</a>
        <span>|</span>
        <a href="aide.php">Aides</a>
    </nav>
</footer>

</body>
</html>
