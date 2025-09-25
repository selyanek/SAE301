<!-- SAE.php : Saisir une Absence et l'Enregistrer -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>sae</title>
    <link href="style.css" rel="stylesheet">
</head>
<body>

<h1> Justifier une absence </h1>
<h2> Saisissez les informations liées à votre absence </h2>

<form action="sae.php" method="post">

    <label>Date et heure de début :</label>
    <input name="date_start" id="date_start" type="datetime-local" />
    <br>

    <label>Date et heure de fin :</label>
    <input name="date_end" id="date_end" type="datetime-local" />
    <br>

    <label id = "motif_label">Motif de l'absence :</label>
    <textarea name="motif" id="motif" rows="2"></textarea>
    <br>

    <label for="justification">Justification :</label>
    <text>Veuillez joindre un justificatif (format accepté : .pdf, .jpg, .png | taille max : 5MB)</text>
    <br>
    <input type="file" id="justification" name="justification" accept=".pdf,.jpg,.png" maxsize="5MB" />
    <br>


    <button type="reset">Annuler</button>
    <button type="submit">Valider</button>
</form>
<?php
$connections = "host=localhost port=5435 dbname=sae user=sae password=1zevkN&49b&&a*Pi97C";
$connection = pg_connect($connections);
if (!$connection) {
    echo "Une erreur s'est produite.\n";
    exit;
} else {
    echo "Connexion réussie.\n";
}

?>
</body>
</html>