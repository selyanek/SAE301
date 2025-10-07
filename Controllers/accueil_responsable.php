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
<!--<section class="pipicaca">
      <div class="calendar">
    <?php
      $year = date("Y");
      $month = date("n");
      $day = date("j");

      $date = strtotime('today');
        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE
        );
        echo $formatter->format($date);
      $first_day = date("w", mktime(0, 0, 0, $month, 1, $year));
      $days_in_month = date("t", mktime(0, 0, 0, $month, 1, $year));

      if ($first_day == 0) $first_day = 7;
    ?>
    <table>
      <tr>
        <th>Lu</th><th>Ma</th><th>Me</th><th>Je</th><th>Ve</th><th>Sa</th><th>Di</th>
      </tr>
      <tr>
      <?php
        for ($i = 1; $i < $first_day; $i++) {
          echo "<td></td>";
        }
        for ($d = 1; $d <= $days_in_month; $d++) {
          $current_day = date("w", mktime(0, 0, 0, $month, $d, $year));
          if ($current_day == 0) $current_day = 7;
          $class = ($d == $day) ? "today" : "";
          echo "<td class='$class'>$d</td>";
          if ($current_day == 7 && $d != $days_in_month) {
            echo "</tr><tr>";
          }
        }
      ?>
      </tr>
    </table>
  </div>--> 
</section>
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