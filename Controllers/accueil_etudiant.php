<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <link href="/CSS/cssDeBase.css" rel="stylesheet">
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
        <li>Accueil</li>
        <li>Gestion des absences</li>
        <li>Historique des absences</li>
        <li>Paramètre</li>
    </ul>
</div>

<header class="text">
<h1> Bonjour, étudiant (faudrait take l'id de l'etu via la bdd) </h1>
<a href="../Views/gererAbsEtu.php"><button type="submit" class="btn">Consulter vos justificatifs</button></a>
</header>
<footer class="footer">
    <nav class="footer-nav">
    <a href="/Controllers/accueil_etudiant.php">Accueil</a>
    <span>|</span>
    <a href="../Views/aide.php">Aides</a>
  </nav>
</footer>
</body>
</html>
