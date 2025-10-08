<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer mes absences</title>
    <!-- Importation des feuilles de style (CSS) -->
    <link href="/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/CSS/cssGererAbsEtu.css" rel="stylesheet">
</head>

<!-- Logo UPHF -->
<div class="uphf">
    <img src="../img/logouphf.png" alt="Logo uphf">
</div>

<body>
<!-- Logo EduTrack -->
<div class="logoEdu">
    <img src="../img/logoedutrack.png" alt="Logo EduTrack">
</div>

<!-- Titre et description de la page -->
<header class="text">
    <h1>Gérer mes absences </h1>
    <p>Cette page vous donne accès aux informations et réponses liées à vos absences justifiées.</p>
    <a href="/Controllers/upload.php"><button type="button" class="btn">Soumettre un nouveau justificatif</button></a>
</header>

<!-- Menu de navigation latéral -->
<div class="sidebar">
    <ul>
        <li><a href="../Controllers/accueil_etudiant.php">Accueil</a></li>
        <li><a href="../Views/gererAbsEtu.php">Gérer des absences</a></li>
        <li><a href="#">Historique des absences</a></li>
        <li><a href="../Views/aide.php">Aides</a></li>
    </ul>
</div>

<!-- Tableau : Liste des absences de l'étudiant -->
<table class="liste-absences"> 
    <!-- En-tête du tableau -->
    <tr>
        <th>Date de début</th>
        <th>Date de fin</th>
        <th>Motif</th>
        <th>Justificatif</th>
        <th>Actions</th>
    </tr>
    
    <!-- Ligne 1 : Absence pour rendez-vous médical -->
    <tr>
        <td>2024-01-15 09:00</td>
        <td>2024-01-15 12:00</td>
        <td>Rendez-vous médical</td>
        <td><a href="justificatif1.pdf" target="_blank">Voir le justificatif</a></td>
        <td>
            <button type="button">Modifier</button>
            <button type="button">Supprimer</button>
        </td>
    </tr>
    
    <!-- Ligne 2 : Absence pour problème familial -->
    <tr>
        <td>2024-02-10 14:00</td>
        <td>2024-02-10 16:00</td>
        <td>Problème familial</td>
        <td><a href="justificatif2.jpg" target="_blank">Voir le justificatif</a></td>
        <td>
            <button type="button">Modifier</button>
            <button type="button">Supprimer</button>
        </td>
    </tr>
</table>

<br>

<!-- Bouton retour à l'accueil -->
<div class="text">
    <a href="../Controllers/accueil_etudiant.php"><button type="button" class="btn">Retour à l'accueil</button></a>
</div>

</body>

<footer class="footer">
    <nav class="footer-nav">
    <a href="../Controllers/accueil_etudiant.php">Accueil</a>
    <span>|</span>
    <a href="../Views/aide.php">Aides</a>
  </nav>
</footer>
</html>