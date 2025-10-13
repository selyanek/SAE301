<?php 
//Inclusion du fichier contenant les fonctions ou données nécessaires (ex : récupération de fichiers)
require "../Models/GetFiles.php"; //Classe pour la gestion des fichiers
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <!-- Inclusion du css -->
    <link href="/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/CSS/cssGestionAbsResp.css" rel="stylesheet">
</head>

<!-- Affichage du logo de l'université -->
<div class="uphf">
    <img src="../img/logouphf.png" alt="Logo uphf">
</div>
<body>
<!-- Affichage du logo EduTrack -->
<div class="logoEdu">
    <img src="../img/logoedutrack.png" alt="Logo EduTrack">
</div>

<!-- Barre latérale de navigation -->
<div class="sidebar">
      <ul>
          <li><a href="../Controllers/accueil_responsable.php">Accueil</a></li> <!-- Lien vers la page d'accueil -->
          <li><a href="../Views/gestionAbsResp.php">Gestion des absences</a></li> <!-- Lien vers la gestion des absences -->
          <li><a href="#">Historique des absences</a></li> <!-- Lien vers l'historique (à compléter) -->
          <li><a href="#">Statistiques</a></li> <!-- Lien vers les statistiques (à compléter) -->
      </ul>
</div>

<!-- En-tête principal -->
<header class="text">
<h1> Bonjour, Responsable (faudrait take l'id via la bdd) </h1> <!-- Message d'accueil, à personnaliser avec l'id du responsable -->
</header>

<!-- Bouton pour consulter les absences -->
<div class="text">
        <a href="#"><button type="submit" class="btn">Consulter les absences</button></a>
</div>

<!-- Pied de page avec navigation -->
<footer class="footer">
    <nav class="footer-nav">
    <a href="/Controllers/accueil_responsable.php">Accueil</a> <!-- Retour à l'accueil -->
    <span>|</span>
    <a href="../Views/aideResp.php">Aides</a> <!-- Lien vers la page d'aide -->
  </nav>
</footer>
</body>
</html>