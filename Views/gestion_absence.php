<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer mes absences</title>
    <link href="/Views/style2.css" rel="stylesheet">
</head>
<header class="header">
    <img src="../img/logouphf.png" alt="Logo uphf">
</header>
<body>
<div class="logoEdu">
    <img src="../img/logoedutrack.png" alt="Logo EduTrack">
</div>
<div class="separator"></div>
<h1> Gérer mes absences </h1>
<p> Cette page permettra à l'étudiant de voir, modifier ou supprimer ses absences justifiées. </p>
<a href="accueil_étudiant.php"><button type="button">Retour à l'accueil</button></a>
<br>
<a href="sae.php"><button type="button">Soumettre un nouveau justificatif</button></a>
<br>
<div class="sidebar">
    <ul>
        <li>Accueil</li>
        <li>Gestion des absences</li>
        <li>Historique des absences</li>
        <li>Paramètre</li>
    </ul>
</div>
<table> 
    <tr>
        <th>Date de début</th>
        <th>Date de fin</th>
        <th>Motif</th>
        <th>Justificatif</th>
        <th>Actions</th>
    </tr>
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
    <!-- D'autres absences peuvent être listées ici -->
</table>

</body>
</html>