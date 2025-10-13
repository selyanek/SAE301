<?php require '../Models/GetFiles.php'?>
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
<table>
        <thead>
            <tr>
                <th scope='col'>Étudiant</th>
                <th scope='col'>Justification</th>
                <th scope='col'>Document</th>
            </tr>
        </thead>

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