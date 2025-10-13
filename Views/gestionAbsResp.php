<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <link href="/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/CSS/cssGestionAbsResp.css" rel="stylesheet">

</head>
<!-- Affichage des logos -->
<div class="uphf">
    <img src="../img/logouphf.png" alt="Logo uphf">
</div>
<body>
<div class="logoEdu">
    <img src="../img/logoedutrack.png" alt="Logo EduTrack">
</div>
<!-- Barre latérale de navigation -->
<div class="sidebar">
      <ul>
          <li><a href="../Controllers/accueil_responsable.php">Accueil</a></li>
          <li><a href="../Views/gestionAbsResp.php">Gestion des absences</a></li>
          <li><a href="#">Historique des absences</a></li>
          <li><a href="#">Statistiques</a></li>
      </ul>
</div>
<header class="text">
<h1> Bonjour </h1>
</header>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Sélecteur de date</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<!-- Filtrage  -->
<form method="get" style="margin-bottom: 20px;">
    <label for="nom">Nom étudiant :</label>
    <input type="text" name="nom" id="nom" value="">
    <label for="date">Date :</label>
    <input type="date" name="date" id="date" value="">
    <label for="cours">Cours :</label>
    <input type="text" name="cours" id="cours" value=""> 
    <label for="groupe">Cours :</label>
    <input type="text" name="groupe" id="groupe" value="">
    <button type="submit">Filtrer</button>
    <a href="gestionAbsResp.php"><button type="button">Réinitialiser</button></a>
</form>
<!-- Tableau des absences -->
    <table>
        <tbody>
            <?php
                $tmp = new GetFiles();
                $folder = "../uploads/";
                $files = $tmp->get_files($folder, [".txt", ".pdf", ".jpg",".png"], false);
                
                if (count($files) > 0) {
                    foreach ($files as $file) {
                        echo "<tr>";
                        echo "<td>Étudiant " . htmlspecialchars(basename($file, pathinfo($file, PATHINFO_EXTENSION))) . "</td>";
                        echo "<td>Justification d'absence</td>";
                        echo "<td><a href='" . htmlspecialchars($file) . "' target='_blank'>Voir le document</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Aucun document trouvé</td></tr>";
                }
              ?>
        </tbody>
    </table>

<footer class="footer">
    <nav class="footer-nav">
    <a href="/Controllers/accueil_responsable.php">Accueil</a>
    <span>|</span>
    <a href="../Views/aide.php">Aides</a>
  </nav>
</footer>
</body>
</html>