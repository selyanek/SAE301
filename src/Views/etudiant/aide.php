<?php
// Page d'aide pour l'étudiant
session_start();
require '../../Controllers/session_timeout.php'; // Gestion du timeout de session
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Aide</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
</head>
<body>
    <div class="uphf">
        <img src="/public/asset/img/logouphf.png" alt="Logo uphf">
    </div>
    <div class="logoEdu">
        <img src="/public/asset/img/logoedutrack.png" alt="Logo EduTrack">
    </div>
    
    <!-- Barre latérale de navigation -->
    <div class="sidebar">
        <ul>
            <li><a href="/src/Views/etudiant/dashbord.php">Accueil</a></li>
            <li><a href="/src/Controllers/profile.php">Mon profil</a></li>
            <li><a href="/src/Views/etudiant/justificatif.php">Gérer des absences</a></li>
            <li><a href="/src/Views/etudiant/historiqueAbsences.php">Historique des absences</a></li>
        </ul>
    </div>
    
    <section class="text">
        <h1>Aide </h1>
        <p>EduTrack est l’application officielle de l’IUT qui permet aux étudiants de justifier leurs absences en ligne. Plus besoin de passer au secrétariat : tout se fait directement depuis la plateforme.</p>

        <h2>Comment ça marche ?</h2>

        <ol>
            <li>Déclare ton absence (Remplis un formulaire)</li>
            <li>Téléverse ton justificatif (certificat, convocation, etc...)</li>
            <li>Attends la validation du responsable</li>
        </ol>

        <a href="https://moodle.uphf.fr/course/view.php?id=3785" target="_blank">Règlement intérieur sur les absences</a>

        <p>Pensez à transmettre vos justificatifs dans les délais afin que vos absences soient correctement prises en compte.</p>

    <a href="/src/Views/etudiant/dashbord.php"><button type="button" class="btn">Retour à l'accueil</button></a>
    </section>

    <footer class="footer">
        <nav class="footer-nav">
            <a href="/src/Views/etudiant/dashbord.php">Accueil</a>
            <span>|</span>
            <a href="/src/Views/etudiant/aide.php">Aides</a>
        </nav>
    </footer>
</body>
</html>