<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <!-- Importation de la feuille de style principale (CSS) -->
    <link href="/CSS/cssDeBase.css" rel="stylesheet">
</head>

<!-- Logo de l'établissement (UPHF) en haut de page -->
<div class="uphf">
    <img src="../img/logouphf.png" alt="Logo uphf">
</div>

<body>

<!-- Logo de l'application EduTrack -->
<div class="logoEdu">
    <img src="../img/logoedutrack.png" alt="Logo EduTrack">
</div>

<!-- Menu de navigation latéral -->
<div class="sidebar">
    <ul>
        <li><a href="../Controllers/accueil_etudiant.php">Accueil</a></li>
        <li><a href="../Views/gererAbsEtu.php">Gérer des absences</a></li>
        <li><a href="#">Historique des absences</a></li>
        <li><a href="../Views/aide.php">Aides</a></li>
    </ul>
</div>

<!-- Contenu principal de la page -->
<header class="text">
    <!-- Message de bienvenue personnalisé -->
    <h1> Bonjour, étudiant (faudrait take l'id de l'etu via la bdd) </h1>
    
    <!-- Bouton principal : accès aux justificatifs -->
    <a href="../Views/gererAbsEtu.php">
        <button type="submit" class="btn">Consulter vos justificatifs</button>
    </a>
</header>

<!-- Pied de page avec navigation secondaire -->
<footer class="footer">
    <nav class="footer-nav">
        <a href="/Controllers/accueil_etudiant.php">Accueil</a>
        <span>|</span>
        <a href="../Views/aide.php">Aides</a>
    </nav>
</footer>

</body>
</html>