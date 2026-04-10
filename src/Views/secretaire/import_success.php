<?php
session_start();
require __DIR__ . "/../../Controllers/session_timeout.php";
require __DIR__ . "/../../Controllers/Redirect.php";

use src\Controllers\Redirect;

$redirect = new Redirect('secretaire');
$redirect->redirect();

// Récupérer le message de session
$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? 'success';

// Nettoyer la session
unset($_SESSION['message']);
unset($_SESSION['message_type']);

$pageTitle = 'Import termine';
$additionalCSS = [
    '/public/asset/CSS/secretaire.css',
    '/public/asset/CSS/import_success.css',
];

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/navigation.php';
?>
<header class="text">
    <h1>Import terminé avec succès</h1>
</header>

<main class="content">
    <div class="result-container">
        <div class="stats-box">
            <h2>Résultat de l'import</h2>
            <?php echo $message; ?>
        </div>
        
        <div class="redirect-message">
            Redirection automatique dans <span class="countdown" id="countdown">10</span> secondes
            <div class="spinner-small"></div>
        </div>
    </div>
</main>

<?php
require __DIR__ . '/../layout/footer.php';
?>

<script>
    let secondsLeft = 10;
    const countdownElement = document.getElementById('countdown');
    
    const interval = setInterval(() => {
        secondsLeft--;
        countdownElement.textContent = secondsLeft;
        
        if (secondsLeft <= 0) {
            clearInterval(interval);
            window.location.href = '/src/Views/secretaire/dashboard.php';
        }
    }, 1000);
    
    // Permettre le clic pour rediriger immédiatement
    document.addEventListener('click', () => {
        clearInterval(interval);
        window.location.href = '/src/Views/secretaire/dashboard.php';
    });
</script>