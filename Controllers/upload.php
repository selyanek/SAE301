<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Justifier une absence</title>
    <link href="../Views/cssDeBase.css" rel="stylesheet">
    <link href="../Views/cssUpload.css" rel="stylesheet">
</head>
<div class="uphf">
    <img src="../img/logouphf.png" alt="Logo uphf">
</div>
<body>
  <section class="container">
    <div class="logoEdu">
        <img src="../img/logoedutrack.png" alt="Logo EduTrack">
    </div>
    <div class="sidebar">
        <ul>
            <li>Accueil</li>
            <li>Gérer des absences</li>
            <li>Historique des absences</li>
            <li>Paramètre</li>
        </ul>
    </div>
    <header class="text">
      <h1 class="title">Justifier une absence</h1>
      <h3 class="subtitle">Saisissez les informations liées à votre absence</h3>
    </header>
    <form id="absenceForm" class="absence-form" action="upload.php" method="post" enctype="multipart/form-data">

      <div class="form-group">
        <label class="label">Date et heure de début :</label>
        <input class="input" name="date_start" id="date_start" type="datetime-local" />
      </div>

      <div class="form-group">
        <label class="label">Date et heure de fin :</label>
        <input class="input" name="date_end" id="date_end" type="datetime-local" />
      </div>
      <div class="select">
        <label for="pet-select">Cours concerné(s):</label>
        <select name="cours" id="cours-select">
          <option value="">Choisissez un cours</option>
          <option value="R1.01">R1.01</option>
          <option value="R1.01">R1.02</option>
          <option value="R1.03">R1.03</option>
          <option value="R1.04">R1.04</option>
          <option value="R1.05">R1.05</option>
          <option value="R1.06">R1.06</option>
          <option value="R1.07">R1.07</option>
          <option value="R1.08">R1.08</option>
          <option value="R1.09">R1.09</option>
          <option value="R1.10">R1.10</option>
          <option value="R1.11">R1.11</option>
          <option value="R1.12">R1.12</option>
        </select>
      </div>

      <div class="form-group">
        <label class="label" id="motif_label">Motif de l'absence :</label>
        <textarea class="textarea" name="motif" id="motif" rows="2"></textarea>
        <?php if (isset($_POST['motif']) && empty(trim($_POST['motif']))) { ?>
          <div class="error">Le motif est obligatoire.</div>
        <?php } ?>
        <div id="motif_error" class="error"></div>
      </div>

      <div class="form-group">
        <label class="label" for="justification">Justification :</label>
        <p class="info">Veuillez joindre un justificatif (format accepté : .pdf, .jpg, .png | taille max : 5MB)</p>
        <input class="file-input" type="file" id="justification" name="file" accept=".pdf,.jpg,.png" />
      </div>

      <div class="buttons">
        <button type="reset" class="btn">Réinitialiser</button>
        <button type="submit" class="btn">Valider</button>
        <a href="accueil_etudiant.php"><button type="button" class="btn">Annuler</button></a>
      </div>

    </form>
  </section>
</body>
<footer class="footer">
    <nav class="footer-nav">
    <a href="#">Accueil</a>
    <span>|</span>
    <a href="../Views/aide.php">Aides</a>
  </nav>
</footer>

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