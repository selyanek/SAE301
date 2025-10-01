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
<form id="absenceForm" action="upload.php" method="post" enctype="multipart/form-data">

    <label>Date et heure de début :</label>
    <input name="date_start" id="date_start" type="datetime-local" />
    <br>

    <label>Date et heure de fin :</label>
    <input name="date_end" id="date_end" type="datetime-local" />
    <br>

    <label id="motif_label">Motif de l'absence :</label>
    <textarea name="motif" id="motif" rows="2"></textarea>
    <?php if (isset($_POST['motif']) && empty(trim($_POST['motif']))) { ?>
        <div class="error">Le motif est obligatoire.</div>
    <?php } ?>
    <div id="motif_error" class="error"></div>
    <br>

    <label for="justification">Justification :</label>
    <p>Veuillez joindre un justificatif (format accepté : .pdf, .jpg, .png | taille max : 5MB)</p>
    <input type="file" id="justification" name="file" accept=".pdf,.jpg,.png" />
    <br><br>

    <button type="reset">Réinitialiser</button>
    <button type="submit">Valider</button>
    <a href="accueil_etudiant.php"><button type="button">Annuler</button></a>
</form>
</body>
</html>

<?php 
if (isset($_FILES['file'])) {
    $dossier = 'uploads/';
    $nom_fichier = basename($_FILES['file']['name']);
    $fichier = $dossier . $nom_fichier;
    if (move_uploaded_file($_FILES['file']['tmp_name'], $fichier)) {
        echo "Le fichier ".htmlspecialchars($nom_fichier)." a été uploadé avec succès." . $_FILES['file']['size'];
    } else {
        echo "Une erreur est survenue lors de l'upload.";
    }
}
?>