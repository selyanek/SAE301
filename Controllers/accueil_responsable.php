<?php 
    // Importation du fichier de gestion des fichiers
    require "../Models/GetFiles.php"; 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    
    <!-- Titre de la page -->
    <title>Accueil</title>
    
    <!-- Importation des feuilles de style (CSS) -->
    <link href="/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/CSS/cssGestionAbsResp.css" rel="stylesheet">
</head>

<!-- Logo (UPHF) en haut de page -->
<div class="uphf">
    <img src="../img/logouphf.png" alt="Logo uphf">
</div>

<body>

<!-- Logo de l'application EduTrack -->
<div class="logoEdu">
    <img src="../img/logoedutrack.png" alt="Logo EduTrack">
</div>

<!-- Menu de navigation latéral (spécifique responsable pédagogique) -->
<div class="sidebar">
    <ul>
        <li><a href="../Controllers/accueil_responsable.php">Accueil</a></li>
        <li><a href="#">Gestion des absences</a></li>
        <li><a href="#">Historique des absences</a></li>
        <li><a href="#">Statistiques</a></li>
    </ul>
</div>

<!-- En-tête avec message de bienvenue personnalisé -->
<header class="text">
    <!-- Message de bienvenue -->
    <h1> Bonjour, Responsable (faudrait take l'id via la bdd) </h1>
</header>

<!-- Actions principales disponibles pour le responsable -->
<div class="text">
    <!-- Bouton d'accès au tableau de bord des absences en attente de traitement -->
    <a href="#"><button type="submit" class="btn">Consulter les absences</button></a>
</div>

<!-- Pied de page avec navigation secondaire -->
<footer class="footer">
    <nav class="footer-nav">
        <a href="/Controllers/accueil_responsable.php">Accueil</a>
        <span>|</span>
        <a href="../Views/aideResp.php">Aides</a>
    </nav>
</footer>

</body>
</html>