<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gérer mes absences</title>
    <link href="style.css" rel="stylesheet">
</head>
<body>
<h1> Gérer mes absences </h1>
<p> Cette page permettra à l'étudiant de voir, modifier ou supprimer ses absences justifiées. </p>
<a href="accueil_étudiant.php"><button type="button">Retour à l'accueil</button></a>
<br>
<a href="sae.php"><button type="button">Soumettre un nouveau justificatif</button></a>
<br>
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