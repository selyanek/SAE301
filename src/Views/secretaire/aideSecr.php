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
    <img src="/public/asset/img/logouphf.png" alt="Logo uphf">
</div>

<!-- Affichage du logo EduTrack -->
<div class="logoEdu">
    <img src="/public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>

<!-- Barre latérale de navigation -->
<div class="sidebar">
    <ul>
        <li><a href="/src/Views/secretaire/dashboard.php">Accueil</a></li>
        <li><a href="/src/Controllers/profile.php">Mon profil</a></li>
    </ul>
</div>

<section class="text">
    <h1>Aide</h1>
    <p>EduTrack est l'application officielle de l'IUT qui permet aux responsables de gérer et valider les absences des étudiants en ligne. En tant que secrétaire, vous pouvez envoyer de nouvelles absences à traiter en important les fichiers .csv correspondant aux absences directement depuis la plateforme.</p>

    <h2>Comment ça marche ?</h2>

    <ol>
        <li>Importez des fichiers .csv générés depuis VT</li>
        <li>Lors de l'import, les absences dans chaque fichier .csv seront automatiquement ajoutées à la base de données d'EduTrack</li>
        <li>Après chaque import réussi, vous pourrez consulter les résultats de cet import (nombre de nouvelles absences, nombre d'élèves, etc...) </li>
    </ol>

    <a href="https://moodle.uphf.fr/course/view.php?id=3785" target="_blank">Règlement intérieur sur les absences</a>

    <p>Pensez à importer régulièrement de nouvelles absences afin de garder à jour la liste des absences.</p>

    <a href="accueil_responsable.php"><button type="button" class="btn">Retour à l'accueil</button></a>
</section>

<!-- Pied de page avec navigation -->
<footer class="footer">
    <nav class="footer-nav">
        <a href="dashboard.php">Accueil</a>
        <span>|</span>
        <a href="aideSecr.php">Aides</a>
    </nav>
</footer>

</body>
</html>
