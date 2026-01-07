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

<link href="/public/asset/CSS/cssAccueilResp.css" rel="stylesheet">

<header class="text fade-in">
    <h1>
        <span style="color: #29acc8;">Bonjour,</span> 
        <?php echo htmlspecialchars($_SESSION['nom']); ?> !
    </h1>
</header>

<div class="text fade-in">
    <div class="info-box">
        <p class="info-label">Absences en attente de traitement</p>
        <p class="info-value"><?php echo $nombreAbsencesEnAttente; ?></p>
    </div>
</div>

<div class="text action-section fade-in">
    <a href="gestionAbsResp.php">
        <button type="submit" class="btn">Consulter les absences en cours</button>
    </a>
</div>

<?php
require __DIR__ . '/../layout/footer.php';
?>
