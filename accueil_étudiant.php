<!-- ACCUEIL_ETUDIANT.php : Menu d'accueil pour les utilisateurs connectés en tant qu'étudiant -->

<?php $message_absent = "Vous n'avez actuellement aucune absence signalée";

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <link href="style.css" rel="stylesheet">
</head>
<body>

<h1> Bonjour, étudiant </h1>
<h2> Vos absences : </h2>
<label><?= $message_absent ?></label>

<form action="sae.php"method="post">

<button type="submit">Soumettre un justificatif</button>
</form>
<h2> Autres options : </h2>
<a href="infos_etu.php"><button type="button">Mes informations</button></a>
<a href="gestion_absence.php"><button type="button">Gérer mes absences</button></a>
<a href="connexion.php"><button type="button">Se déconnecter</button></a>

</body>
</html>
