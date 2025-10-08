<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aide</title>
    <!-- Importation de la feuille de style (CSS) -->
    <link href="/CSS/cssDeBase.css" rel="stylesheet">
</head>

<!-- Logo UPHF -->
<div class="uphf">
    <img src="../img/logouphf.png" alt="Logo uphf">
</div>

<body>
    <!-- Logo EduTrack -->
    <div class="logoEdu">
        <img src="../img/logoedutrack.png" alt="Logo EduTrack">
    </div>
    
    <div class="sidebar"></div>
    
    <!-- Contenu de la page d'aide -->
    <section class="text">
        <h1>Aide </h1>
        
        <!-- Présentation d'EduTrack -->
        <p>EduTrack est l'application officielle de l'IUT qui permet aux étudiants de justifier leurs absences en ligne. Plus besoin de passer au secrétariat : tout se fait directement depuis la plateforme.</p>

        <h2>Comment ça marche ?</h2>

        <!-- Étapes pour justifier une absence -->
        <ol>
            <li>Déclare ton absence (Remplis un formulaire)</li>
            <li>Téléverse ton justificatif (certificat, convocation, etc...)</li>
            <li>Attends la validation du responsable</li>
        </ol>

        <!-- Lien vers le règlement intérieur -->
        <a href="https://moodle.uphf.fr/course/view.php?id=3785" target="_blank">Règlement intérieur sur les absences</a>

        <!-- Rappel sur les délais -->
        <p>Pensez à transmettre vos justificatifs dans les délais afin que vos absences soient correctement prises en compte.</p>

        <!-- Bouton retour accueil -->
        <a href="accueil_etudiant.php"><button type="button" class="btn">Retour à l'accueil</button></a>
    </section>

</body>

<!-- Footer avec navigation (liens) -->
<footer class="footer">
    <nav class="footer-nav">
    <a href="/Controllers/accueil_etudiant.php">Accueil</a>
    <span>|</span>
    <a href="../Views/aide.php">Aides</a>
  </nav>
</footer>
</html>