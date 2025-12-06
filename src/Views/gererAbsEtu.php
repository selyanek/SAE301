<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer mes absences</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssGererAbsEtu.css" rel="stylesheet">

</head>
<div class="uphf">
    <img src="../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<body>
<div class="logoEdu">
    <img src="../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>
<header class="text">
    <h1>Gérer mes absences </h1>
    <p>Cette page vous donne accès aux informations et réponses liées à vos absences justifiées.</p>
    <?php
    if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
        echo '<div class="error-messages">';
        foreach ($_SESSION['errors'] as $error) {
            echo '<p class="error">' . htmlspecialchars($error) . '</p>';
        }
        echo '</div>';
        unset($_SESSION['errors']);
    }
    ?>
    <a href="/src/Views/depotJustif.php"><button type="button" class="btn">Soumettre un nouveau justificatif</button></a>
</header>
<div class="sidebar">
    <ul>
        <li><a href="/src/Views/etudiant/dashbord.php">Accueil</a></li>
        <li><a href="/src/Views/gererAbsEtu.php">Gérer mes absences</a></li>
        <li><a href="/src/Views/etudiant/historiqueAbsences.php">Historique des absences</a></li>
        <li><a href="/src/Views/etudiant/aide.php">Aides</a></li>
    </ul>
</div>
<br>
    <div class="text">
    <a href="/src/Views/etudiant/dashbord.php"><button type="button" class="btn">Retour à l'accueil</button></a>
</div>
</body>
<footer class="footer">
        <nav class="footer-nav">
        <a href="/src/Views/etudiant/dashbord.php">Accueil</a>
        <span>|</span>
        <a href="/src/Views/etudiant/aide.php">Aides</a>
    </nav>
</footer>
</html>