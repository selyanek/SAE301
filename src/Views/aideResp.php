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
        <li><a href="accueil_responsable.php">Accueil</a></li>
        <li><a href="/src/Controllers/profile.php">Mon profil</a></li>
        <li><a href="/src/Views/gestionAbsResp.php">Gestion des absences</a></li>
        <li><a href="historiqueAbsResp.php">Historique des absences</a></li>
        <li><a href="/src/Views/responsable/statistiques.php">Statistiques</a></li>
    </ul>
</div>

<section class="text">
    <h1>Aide</h1>
    <p>EduTrack est l'application officielle de l'IUT qui permet aux responsables de gérer et valider les absences des étudiants en ligne. Vous pouvez consulter les demandes, vérifier les justificatifs et suivre le traitement des absences directement depuis la plateforme.</p>

    <h2>Comment ça marche ?</h2>

    <ol>
        <li>Consultez les absences déclarées par les étudiants</li>
        <li>Vérifiez les justificatifs transmis (certificat, convocation, etc...)</li>
        <li>Validez ou refusez la demande selon les documents fournis</li>
    </ol>

    <a href="https://moodle.uphf.fr/course/view.php?id=3785" target="_blank">Règlement intérieur sur les absences</a>

    <p>Pensez à traiter les demandes dans les délais afin d'assurer le bon suivi des absences et la conformité avec le règlement.</p>

    <a href="accueil_responsable.php"><button type="button" class="btn">Retour à l'accueil</button></a>
</section>

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
