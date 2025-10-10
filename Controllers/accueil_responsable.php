<?php require "../Models/GetFiles.php" ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <link href="/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/CSS/cssGestionAbsResp.css" rel="stylesheet">
</head>
<div class="uphf">
    <img src="../img/logouphf.png" alt="Logo uphf">
</div>
<body>
<div class="logoEdu">
    <img src="../img/logoedutrack.png" alt="Logo EduTrack">
</div>
<div class="sidebar">
      <ul>
          <li><a href="../Controllers/accueil_responsable.php">Accueil</a></li>
          <li><a href="#">Gestion des absences</a></li>
          <li><a href="#">Historique des absences</a></li>
          <li><a href="#">Statistiques</a></li>
      </ul>
</div>

<header class="text">
<h1> Bonjour, Responsable (faudrait take l'id via la bdd) </h1>
</header>
<div class="text">
        <a href="#"><button type="submit" class="btn">Consulter les absences</button></a>
</div>
<footer class="footer">
    <nav class="footer-nav">
    <a href="/Controllers/accueil_responsable.php">Accueil</a>
    <span>|</span>
    <a href="../Views/aideResp.php">Aides</a>
  </nav>
</footer>
</body>
</html>