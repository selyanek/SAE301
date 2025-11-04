<!-- Page de dépôt de justificatif d'absence pour les étudiants -->
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Depot Justificatif</title>
    <link rel="stylesheet" href="../CSS/cssDeBase.css">
    <link href="../CSS/cssUpload.css" rel="stylesheet">
</head>
<body>
<div class="uphf">
    <img src="../img/logouphf.png" alt="Logo uphf">
</div>
<body>
<div class="logoEdu">
    <img src="../img/logoedutrack.png" alt="Logo EduTrack">
</div>
<div class="sidebar">
    <ul>
        <li><a href="../Controllers/accueil_etudiant.php">Accueil</a></li>
        <li><a href="../Views/gererAbsEtu.php">Gérer des absences</a></li>
        <li><a href="#">Historique des absences</a></li>
        <li><a href="../Views/aide.php">Aides</a></li>
    </ul>
</div>
<?php
if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo '<div class="success-message">Votre justificatif a été envoyé avec succès !</div>';
}
if (isset($_GET['error'])) {
    $error_messages = [
            'motif' => 'Le motif est obligatoire.',
            'dates' => 'Veuillez renseigner les dates de début et de fin.',
            'dates_invalides' => 'La date de fin doit être après la date de début.',
            'file_required' => 'Veuillez joindre un justificatif.',
            'file_type' => 'Format de fichier non accepté. Utilisez .pdf, .jpg ou .png',
            'file_size' => 'Le fichier est trop volumineux (max 5MB).',
            'upload_failed' => 'Erreur lors de l\'upload du fichier.',
            'delai_depasse' => 'Le délai de 48 heures pour déposer un justificatif est dépassé.'
    ];
    $error = $_GET['error'];
    if (isset($error_messages[$error])) {
        echo '<div class="error-message">' . $error_messages[$error] . '</div>';
    }
}
?>

<form id="absenceForm" class="absence-form" action="../Controllers/upload2.php" method="post" enctype="multipart/form-data">

    <div class="form-group">
        <label class="label">Date et heure de début :</label>
        <input class="input" name="date_start" id="date_start" type="datetime-local" required
               value="<?php echo htmlspecialchars($_GET['date_start'] ?? ''); ?>" />
    </div>

    <div class="form-group">
        <label class="label">Date et heure de fin :</label>
        <input class="input" name="date_end" id="date_end" type="datetime-local" required
               value="<?php echo htmlspecialchars($_GET['date_end'] ?? ''); ?>" />
    </div>

    <div class="form-group">
        <label class="label" id="motif_label">Motif de l'absence :</label>
        <textarea class="textarea" name="motif" id="motif" rows="2" required><?php echo htmlspecialchars($_GET['motif'] ?? ''); ?></textarea>
    </div>

    <div class="form-group">
        <label class="label" for="justification">Justification :</label>
        <p class="info">Veuillez joindre un justificatif (format accepté : .pdf, .jpg, .png | taille max : 5MB)</p>
        <input class="file-input" type="file" id="justification" name="file" accept=".pdf,.jpg,.jpeg,.png" required />
    </div>

    <div class="buttons">
        <button type="reset" class="btn">Réinitialiser</button>
        <button type="submit" class="btn">Valider</button>
        <a href="accueil_etudiant.php"><button type="button" class="btn">Annuler</button></a>
    </div>

</form>
</body>
<footer class="footer">
    <nav class="footer-nav">
        <a href="../Controllers/accueil_etudiant.php">Accueil</a>
        <span>|</span>
        <a href="../Views/aide.php">Aides</a>
    </nav>
</footer>
</html>