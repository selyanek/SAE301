<?php
// Vue pour la page d'accueil du professeur.
// La logique a été déplacée dans AccueilProfController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../Controllers/session_timeout.php';
require_once __DIR__ . '/../../Controllers/Redirect.php';
require_once __DIR__ . '/../../Database/Database.php';
require_once __DIR__ . '/../../Models/Rattrapage.php';

$redirect = new \src\Controllers\Redirect('professeur');
$redirect->redirect();

$nomProfesseur = $_SESSION['nom'] ?? 'Professeur';
$pageTitle = 'Accueil Professeur';

$additionalCSS = ['/public/asset/CSS/cssAccueilResp.css'];

$idProfesseur = $_SESSION['idCompte'] ?? null;
$rattrapageModel = new \src\Models\Rattrapage();
$absencesEvaluations = $idProfesseur ? $rattrapageModel->getAbsencesEvaluationsPourProfesseur($idProfesseur) : [];
$nombreAbsencesEvaluations = count($absencesEvaluations);

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/navigation.php';
?>

<header class="text fade-in">
    <h1>
        <span style="color: #29acc8;">Bonjour,</span>
        <?php echo htmlspecialchars($nomProfesseur, ENT_QUOTES, 'UTF-8'); ?> !
    </h1>
</header>

<div class="text fade-in">
    <div class="info-box">
        <p class="info-label">Absences liées à vos évaluations</p>
        <p class="info-value"><?php echo $nombreAbsencesEvaluations; ?></p>
    </div>
</div>

<div class="text action-section fade-in">
    <a href="/public/professeur/rattrapage_prof.php">
        <button type="button" class="btn">Gérer les rattrapages</button>
    </a>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>