<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Justifier une absence</title>
    <link href="../CSS/cssDeBase.css" rel="stylesheet">
    <link href="../CSS/cssUpload.css" rel="stylesheet">
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
          <li><a href="../Controllers/accueil_etudiant.php">Accueil</a></li>
          <li><a href="../Views/gererAbsEtu.php">Gérer des absences</a></li>
          <li><a href="#">Historique des absences</a></li>
          <li><a href="../Views/aide.php">Aides</a></li>
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
      <div class="input">
        <label for="cours-select">Cours concerné(s):</label>
        <select name="cours" id="cours-select"></select>
          <script>
            const select = document.getElementById('cours-select');
            const options = [
              "", "R1.01", "R1.02", "R1.03", "R1.04", "R1.05", "R1.06",
              "R1.07", "R1.08", "R1.09", "R1.10", "R1.11", "R1.12"
            ];
            select.innerHTML = options.map(val =>
              val === "" ?
                `<option value="">Choisissez un cours</option>` :
                `<option value="${val}">${val}</option>`
            ).join('');
          </script>
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
        <div id="file_error" class="error"></div>
      </div>
        <input class="file-input" type="file" id="justification" name="file" accept=".pdf,.jpg,.png" />
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
    <a href="/Controllers/accueil_etudiant.php">Accueil</a>
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