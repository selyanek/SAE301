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

$pageTitle = 'Aide et assistance';
$additionalCSS = ['/public/asset/CSS/cssAide.css'];

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/navigation.php';
?>

<section class="aide-container">
    <div class="aide-header">
        <h1>Aide et assistance</h1>
        <p class="aide-subtitle">Tout ce que vous devez savoir sur EduTrack</p>
    </div>

    <div class="aide-content">
        <div class="aide-card intro-card">
            <h2>Qu'est-ce qu'EduTrack ?</h2>
            <p>EduTrack est l'application officielle de l'IUT qui permet aux responsables pédagogiques de consulter, vérifier et valider les absences des étudiants en ligne. La plateforme centralise les demandes pour faciliter le suivi administratif et le traitement des justificatifs.</p>
        </div>

        <div class="aide-card steps-card">
            <h2>Comment ça marche ?</h2>
            <ol class="steps-list">
                <li>
                    <span class="step-number">1</span>
                    <div class="step-content">
                        <strong>Consulte les absences</strong>
                        <p>Accède à la liste des absences déclarées par les étudiants.</p>
                    </div>
                </li>
                <li>
                    <span class="step-number">2</span>
                    <div class="step-content">
                        <strong>Vérifie les justificatifs</strong>
                        <p>Contrôle les documents transmis comme les certificats ou convocations.</p>
                    </div>
                </li>
                <li>
                    <span class="step-number">3</span>
                    <div class="step-content">
                        <strong>Valide ou refuse la demande</strong>
                        <p>Prends ta décision selon les pièces fournies et le règlement intérieur.</p>
                    </div>
                </li>
            </ol>
        </div>

        <div class="aide-card info-card">
            <h2>Important à savoir</h2>
            <ul class="info-list">
                <li>Les demandes doivent être traitées dans les délais pour assurer un suivi correct des absences.</li>
                <li>Les justificatifs doivent être lisibles et conformes aux règles de l'établissement.</li>
                <li>Le suivi des traitements reste disponible dans l'historique des absences.</li>
            </ul>
        </div>

        <div class="aide-card reglement-card">
            <h2>Règlement intérieur</h2>
            <p>Consulte le règlement officiel de l'IUT concernant les absences et les justificatifs.</p>
            <a href="https://moodle.uphf.fr/course/view.php?id=3785" target="_blank" class="btn-reglement">
                <span>Voir le règlement sur Moodle</span>
                <span class="arrow">→</span>
            </a>
        </div>

        <div class="aide-actions">
            <a href="/src/Views/responsable/dashboard.php" class="btn-retour">
                <span>← Retour à l'accueil</span>
            </a>
            <a href="/src/Views/responsable/historiqueAbsResp.php" class="btn-primary">
                <span>Consulter l'historique</span>
            </a>
        </div>
    </div>
</section>

<?php
require __DIR__ . '/../layout/footer.php';
?>
