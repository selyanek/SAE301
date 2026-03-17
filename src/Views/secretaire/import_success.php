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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import terminé</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/secretaire.css" rel="stylesheet">
    <link href="/public/asset/CSS/import_success.css" rel="stylesheet">
</head>
<body>
<!-- US-26 : Bouton hamburger pour mobile -->
<button class="hamburger" id="hamburgerBtn" aria-label="Menu de navigation" onclick="toggleMenu()">☰</button>

<script>
function toggleMenu() {
    var sidebar = document.querySelector('.sidebar');
    var btn = document.getElementById('hamburgerBtn');
    sidebar.classList.toggle('open');
    btn.textContent = sidebar.classList.contains('open') ? '✕' : '☰';
}
document.addEventListener('DOMContentLoaded', function() {
    var links = document.querySelectorAll('.sidebar a');
    for (var i = 0; i < links.length; i++) {
        links[i].addEventListener('click', function() {
            var sidebar = document.querySelector('.sidebar');
            sidebar.classList.remove('open');
            document.getElementById('hamburgerBtn').textContent = '☰';
        });
    }
    document.addEventListener('click', function(e) {
        var sidebar = document.querySelector('.sidebar');
        var btn = document.getElementById('hamburgerBtn');
        if (sidebar && sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== btn) {
            sidebar.classList.remove('open');
            btn.textContent = '☰';
        }
    });
});
</script>
<div class="uphf">
    <img src="../../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<div class="logoEdu">
    <img src="../../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>
<div class="sidebar">
    <ul>
        <li><a href="dashboard.php">Accueil</a></li>
        <li><a href="envoie_des_absences.php">Envoie des absences</a></li>
        <li><a href="/src/Controllers/profile.php">Mon profil</a></li>
        <li><a href="/src/Views/secretaire/aide.php">Aides</a></li>
    </ul>
</div>

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

<footer class="footer">
    <nav class="footer-nav">
        <a href="dashboard.php">Accueil</a>
        <span>|</span>
        <a href="/src/Views/secretaire/aide.php">Aide</a>
    </nav>
</footer>

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
</body>
</html>