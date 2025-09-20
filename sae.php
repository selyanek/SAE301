<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>sae</title>
    <link href = "style.css" rel= "stylesheet">
</head>
<body>

<h1> Absence </h1>
<h2> Modifiez les informations liées à votre absence </h2>

<form action="sae.php" method="post">

    <label>Date et heure de début :</label>
    <input name="date_start" id="date_start" type="datetime-local" />
    <p></p>

    <label>Date et heure de fin :</label>
    <input name="date_end" id="date_end" type="datetime-local" />
    <p></p>

    <label id = "motif_label">Motif :</label>
    <textarea name="motif" id="motif" rows="2"></textarea>
    <p></p>

    <label for="justification">Justification :</label>
    <input type="file" id="justification" name="justification" accept=".pdf,.jpg,.png" maxsize="5MB" />
    <p></p>

    <button type="reset">Annuler</button>
    <button type="submit">Valider</button>
</form>

</body>
</html>
