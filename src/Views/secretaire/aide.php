<?php
session_start();
require __DIR__ . "/../../Controllers/session_timeout.php"; // Gestion du timeout de session
require __DIR__ . "/../../Controllers/Redirect.php";

use src\Controllers\Redirect;

$redirect = new Redirect('secretaire');
$redirect->redirect();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aide</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
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
        <li><a href="/src/Views/secretaire/dashboard.php">Accueil</a></li>
        <li><a href="/src/Views/secretaire/envoie_des_absences.php">Envoie des absences</a></li>
        <li><a href="/src/Controllers/profile.php">Mon profil</a></li>
        <li><a href="/src/Views/secretaire/aide.php">Aides</a></li>
    </ul>
</div>

<section class="text">
    <h1>Aide</h1>
    <p>EduTrack est l'application officielle de l'IUT qui permet aux responsables de gérer et valider les absences des étudiants en ligne. En tant que secrétaire, vous pouvez envoyer de nouvelles absences à traiter en important les fichiers .csv correspondant aux absences directement depuis la plateforme.</p>

    <h2>Comment ça marche ?</h2>

    <ol>
        <li>Importez des fichiers .csv générés depuis VT</li>
        <li>Lors de l'import, les absences dans chaque fichier .csv seront automatiquement ajoutées à la base de données d'EduTrack</li>
        <li>Après chaque import réussi, vous pourrez consulter les résultats de cet import (nombre de nouvelles absences, nombre d'élèves, etc...) </li>
    </ol>

    <a href="https://moodle.uphf.fr/course/view.php?id=3785" target="_blank">Règlement intérieur sur les absences</a>

    <p>Pensez à importer régulièrement de nouvelles absences afin de garder à jour la liste des absences.</p>

    <a href="dashboard.php"><button type="button" class="btn">Retour à l'accueil</button></a>
</section>

<!-- Pied de page avec navigation -->
<footer class="footer">
    <nav class="footer-nav">
        <a href="dashboard.php">Accueil</a>
        <span>|</span>
        <a href="aide.php">Aides</a>
    </nav>
</footer>

</body>
</html>