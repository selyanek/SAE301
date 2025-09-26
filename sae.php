<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Justifier une absence</title>
    <link href="style.css" rel="stylesheet">
    <style>
        .error {
            color: red;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
<h1> Justifier une absence </h1>
<h2> Saisissez les informations liées à votre absence </h2>
<form id="absenceForm" action="sae.php" method="post" enctype="multipart/form-data">

    <label>Date et heure de début :</label>
    <input name="date_start" id="date_start" type="datetime-local" />
    <br>

    <label>Date et heure de fin :</label>
    <input name="date_end" id="date_end" type="datetime-local" />
    <br>

    <label id="motif_label">Motif de l'absence :</label>
    <textarea name="motif" id="motif" rows="2"></textarea>
    <div id="motif_error" class="error"></div>
    <br>

    <label for="justification">Justification :</label>
    <p>Veuillez joindre un justificatif (format accepté : .pdf, .jpg, .png | taille max : 5MB)</p>
    <input type="file" id="justification" name="justification" accept=".pdf,.jpg,.png" />
    <br><br>

    <button type="reset">Réinitialiser</button>
    <button type="submit">Valider</button>
    <a href="accueil_etudiant.php"><button type="button">Annuler</button></a>
</form>


</body>
</html>
