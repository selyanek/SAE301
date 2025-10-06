<!-- ACCUEIL_ETUDIANT.php : Menu d'accueil pour les utilisateurs connectés en tant qu'étudiant -->

<?php $message_absent = "Vous n'avez actuellement aucune absence signalée";

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <link href="/Views/style2.css" rel="stylesheet"> 
</head>
<body>

<div class="sidebar">
    <ul>
        <li>Accueil</li>
        <li>Gestion des absences</li>
        <li>Historique des absences</li>
        <li>Paramètre</li>
    </ul>
</div>

<h1> Bonjour, étudiant </h1>
<h2> Vos absences : </h2>
<label><?= $message_absent ?></label>

<form action="sae.php"method="post">

<button type="submit">Soumettre un justificatif</button>
</form>
<a href="../Views/infos_etu.php"><button type="button">Mes informations</button></a>

</body>
</html>
