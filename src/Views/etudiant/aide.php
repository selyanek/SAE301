<?php
// Page d'aide pour l'étudiant
session_start();
require '../../Controllers/session_timeout.php';
$pageTitle = 'Aide';
$additionalCSS = ['/public/asset/CSS/cssAide.css'];
require '../layout/header.php';
require '../layout/navigation.php';
?>

<section class="aide-container">
    <div class="aide-header">
        <h1>Aide et assistance</h1>
        <p class="aide-subtitle">Tout ce que vous devez savoir sur EduTrack</p>
    </div>

    <div class="aide-content">
        <div class="aide-card intro-card">
            <h2>Qu'est-ce qu'EduTrack ?</h2>
            <p>EduTrack est l'application officielle de l'IUT qui permet aux étudiants de justifier leurs absences en ligne. Plus besoin de passer au secrétariat : tout se fait directement depuis la plateforme.</p>
        </div>

        <div class="aide-card steps-card">
            <h2>Comment ça marche ?</h2>
            <ol class="steps-list">
                <li>
                    <span class="step-number">1</span>
                    <div class="step-content">
                        <strong>Déclare ton absence</strong>
                        <p>Remplis le formulaire avec les dates et le motif</p>
                    </div>
                </li>
                <li>
                    <span class="step-number">2</span>
                    <div class="step-content">
                        <strong>Téléverse ton justificatif</strong>
                        <p>Ajoute ton certificat médical, convocation, etc.</p>
                    </div>
                </li>
                <li>
                    <span class="step-number">3</span>
                    <div class="step-content">
                        <strong>Attends la validation</strong>
                        <p>Le responsable examine ta demande sous 48h</p>
                    </div>
                </li>
            </ol>
        </div>

        <div class="aide-card info-card">
            <h2>Important à savoir</h2>
            <ul class="info-list">
                <li>Pensez à transmettre vos justificatifs <strong>dans les délais</strong> afin que vos absences soient correctement prises en compte.</li>
                <li>Les formats acceptés sont : PDF, JPG, PNG (max 5 Mo)</li>
                <li>Vous pouvez suivre le statut de vos demandes dans l'historique</li>
            </ul>
        </div>

        <div class="aide-card reglement-card">
            <h2>Règlement intérieur</h2>
            <p>Consultez le règlement officiel de l'IUT concernant les absences et les justificatifs.</p>
            <a href="https://moodle.uphf.fr/course/view.php?id=3785" target="_blank" class="btn-reglement">
                <span>Voir le règlement sur Moodle</span>
                <span class="arrow">→</span>
            </a>
        </div>

        <div class="aide-actions">
            <a href="/src/Views/etudiant/dashboard.php" class="btn-retour">
                <span>← Retour à l'accueil</span>
            </a>
            <a href="/src/Views/etudiant/justificatif.php" class="btn-primary">
                <span>Déclarer une absence</span>
            </a>
        </div>
    </div>
</section>

<?php
require '../layout/footer.php';
?>
