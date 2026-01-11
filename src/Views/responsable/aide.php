<?php
session_start();
require __DIR__ . '/../../Controllers/session_timeout.php';
require __DIR__ . '/../../Controllers/Redirect.php';
require __DIR__ . '/../../Database/Database.php';
require __DIR__ . '/../../Models/Absence.php';

use src\Controllers\Redirect;

$redirect = new Redirect('responsable_pedagogique');
$redirect->redirect();

// Connexion à la base de données
$db = new \src\Database\Database();
$pdo = $db->getConnection();
$absenceModel = new \src\Models\Absence($pdo);

// Récupérer le nombre d'absences en attente
$nombreAbsencesEnAttente = $absenceModel->countEnAttente();

// Inclure le header
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/navigation.php';
?>
<link href="/public/asset/CSS/cssAide.css" rel="stylesheet">

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

    <a href="dashboard.php"><button type="button" class="btn">Retour à l'accueil</button></a>
</section>

<!-- Pied de page avec navigation -->
<footer class="footer">
    <nav class="footer-nav">
        <a href="accueil_responsable.php">Accueil</a>
        <span>|</span>
        <a href="aide.php">Aides</a>
    </nav>
</footer>

</body>
</html>
