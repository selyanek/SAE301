<?php require __DIR__ . '/../Controllers/GetFiles.php'?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <link href="/public/asset/CSS/cssTraitementDesJustificatifs.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssGestionAbsResp.css" rel="stylesheet">
</head>
<div class="uphf">
    <img src="../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<body>
<div class="logoEdu">
    <img src="../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>
<div class="sidebar">
      <ul>
        <li><a href="accueil_responsable.php">Accueil</a></li> <!-- Lien vers la page d'accueil -->
        <li><a href="gestionAbsResp.php">Gestion des absences</a></li> <!-- Lien vers la gestion des absences -->
        <li><a href="traitementDesJustificatif.php">Traitement des Justificatifs</a></li> <!-- Lien vers le traitementDesJustificatif -->
        <li><a href="#">Historique des absences</a></li> <!-- Lien vers l'historique (à compléter) -->
        <li><a href="#">Statistiques</a></li> <!-- Lien vers les statistiques (à compléter) -->
      </ul>
</div>

<header class="titre">
<h1> Détails du justificatif des absences </h1>
</header>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Sélecteur de date</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
    
<div class="trait">
</div>

<div class="encadre1">   
    <div class="encadre2">
        <h3>Date de début de l'absence</h3>
        <div class="encadre3">
        <p>09/12/2022</p>
        </div>
    </div>
    <div class="encadre2">
        <h3>Date de fin de l'absence</h3>
        <div class="encadre3">
            <p>12/12/2022</p>
        </div>
    </div>
</div>

<div class="encadre1">   
    <div class="encadre2">
        <h3>Date de soumission</h3>
        <div class="encadre3">
            <p>09/12/2022</p>
        </div>
    </div>
    <div class="encadre2">
        <h3>Cours concerné</h3>
        <div class="encadre3">
            <p>dev réseau</p>
        </div>
    </div>
</div>

<div class="trait">
</div>

<div class="motif">
    <h3>Motif de l'absence</h3>
</div>

<div class="boutons">
    <button>Valider</button>
    <button>Refuser</button>
    <button>Justificatifs supplémentaires</button>
</div>
<footer class="footer">
    <nav class="footer-nav">
    <a href="accueil_responsable.php">Accueil</a>
    <span>|</span>
    <a href="">Aides</a>
  </nav>
</footer>
</body>
</html>