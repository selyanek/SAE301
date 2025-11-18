<?php
// Page de traitement des justificatifs pour les responsables pédagogiques
require '../Models/GetFiles.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Accueil</title>
  <link href="/CSS/cssTraitementDesJustificatifs.css" rel="stylesheet">
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
        <li><a href="../Controllers/accueil_responsable.php">Accueil</a></li> <!-- Lien vers la page d'accueil -->
        <li><a href="../Views/gestionAbsResp.php">Gestion des absences</a></li> <!-- Lien vers la gestion des absences -->
        <li><a href="../Views/traitementDesJustificatif.php">Traitement des Justificatifs</a></li> <!-- Lien vers le traitementDesJustificatif -->
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
        <p><?php echo date('d/m/Y', strtotime($absence['date_start'])); ?></p>
        </div>
    </div>
    <div class="encadre2">
        <h3>Date de fin de l'absence</h3>
        <div class="encadre3">
            <p><?php echo $absence['date_end']; ?></p>
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
<form method="POST" action="../Controllers/traitementDesJustif.php">
    <div class="boutons">
        <button value="valider">Valider</button>
        <button value="refuser">Refuser</button>
        <button value="attente">Demander des justificatifs supplémentaires</button>
    </div>
</form>

<footer class="footer">
    <nav class="footer-nav">
    <a href="/Controllers/accueil_responsable.php">Accueil</a>
    <span>|</span>
    <a href="../Views/aide.php">Aides</a>
  </nav>
</footer>
</body>
</html>
