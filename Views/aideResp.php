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
        <h1> Aide </h1>
        
        <!-- Présentation d'EduTrack pour les responsables -->
        <p>EduTrack est l'application officielle de l'IUT qui permet aux responsables de gérer et valider les absences des étudiants en ligne. Vous pouvez consulter les demandes, vérifier les justificatifs et suivre le traitement des absences directement depuis la plateforme.</p>

        <h2>Comment ça marche ?</h2>

        <!-- Étapes de traitement des absences -->
        <ol>
            <li>Consultez les absences déclarées par les étudiants</li>
            <li>Vérifiez les justificatifs transmis (certificat, convocation, etc...)</li>
            <li>Validez ou refusez la demande selon les documents fournis</li>
        </ol>

        <!-- Lien vers le règlement intérieur -->
        <a href="https://moodle.uphf.fr/course/view.php?id=3785" target="_blank">Règlement intérieur sur les absences</a>

        <!-- Rappel sur les délais de traitement -->
        <p>Pensez à traiter les demandes dans les délais afin d'assurer le bon suivi des absences et la conformité avec le règlement.</p>

        <!-- Bouton retour accueil -->
        <a href="/Controllers/accueil_responsable.php"><button type="button" class="btn">Retour à l'accueil</button></a>
    </section>

</body>

<footer class="footer">
    <nav class="footer-nav">
    <a href="/Controllers/accueil_responsable.php">Accueil</a>
    <span>|</span>
    <a href="../Views/aideResp.php">Aides</a>
  </nav>
</footer>
</html>