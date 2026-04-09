<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Gestion des Absences'; ?></title>
    <link rel="stylesheet" href="../../../public/asset/CSS/cssDeBase.css">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($css); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
<?php if (!isset($showHamburger) || $showHamburger !== false): ?>
<!-- US-26 : Bouton hamburger pour mobile -->
<button class="hamburger" id="hamburgerBtn" aria-label="Menu de navigation" onclick="toggleMenu()">☰</button>
<?php endif; ?>

<div class="uphf">
    <a href="https://www.uphf.fr/iut"><img src="../../../public/asset/img/logouphf.png" alt="Logo uphf"></a>
</div>
<div class="logoEdu">
    <img src="../../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>

<?php if (!isset($showHamburger) || $showHamburger !== false): ?>
<!-- US-26 : Script du menu hamburger -->
<script>
function toggleMenu() {
    var sidebar = document.querySelector('.sidebar');
    var btn = document.getElementById('hamburgerBtn');
    sidebar.classList.toggle('open');
    btn.textContent = sidebar.classList.contains('open') ? '✕' : '☰';
}

document.addEventListener('DOMContentLoaded', function() {
    // Fermer le menu quand on clique sur un lien
    var links = document.querySelectorAll('.sidebar a');
    for (var i = 0; i < links.length; i++) {
        links[i].addEventListener('click', function() {
            var sidebar = document.querySelector('.sidebar');
            sidebar.classList.remove('open');
            document.getElementById('hamburgerBtn').textContent = '☰';
        });
    }

    // Fermer le menu quand on clique en dehors
    document.addEventListener('click', function(e) {
        var sidebar = document.querySelector('.sidebar');
        var btn = document.getElementById('hamburgerBtn');
        if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== btn) {
            sidebar.classList.remove('open');
            btn.textContent = '☰';
        }
    });
});
</script>
<?php endif; ?>