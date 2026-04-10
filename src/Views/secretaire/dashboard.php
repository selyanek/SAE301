<?php
session_start();
require __DIR__ . "/../../Controllers/session_timeout.php"; // Gestion du timeout de session
require __DIR__ . "/../../Controllers/Redirect.php";

use src\Controllers\Redirect;

$redirect = new Redirect('secretaire');
$redirect->redirect();

$pageTitle = 'Accueil Secretaire';
$additionalCSS = [
    '/public/asset/CSS/secretaire.css',
];

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/navigation.php';
?>
<header class="text">
    <h1> Bonjour, <?php echo isset($_SESSION['nom']) ? htmlspecialchars($_SESSION['nom']) : 'Secretaire'; ?> </h1>
    <a href="/src/Views/secretaire/envoie_des_absences.php">
        <button type="submit" class="btn">Envoie des absences</button>
    </a>
</header>

<?php
require __DIR__ . '/../layout/footer.php';
?>