<?php require '../vendor/autoload.php';
require '../Models/Database.php'; ?>

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
        <input class="file-input" type="file" id="justification" name="file" accept=".pdf,.jpg,.png" />
        <?php if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) { ?>
          <div class="error">Il faut obligatoirement joindre votre justificatif.</div>
        <?php } ?>
        <div id="file_error" class="error"></div>
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
    <a href="/Controllers/accueil_etudiant.php">Accueil</a>
    <span>|</span>
    <a href="../Views/aide.php">Aides</a>
  </nav>
</footer>

<?php
session_start();
require '../vendor/autoload.php';
require '../Models/Database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Vérification de l'authentification
if (!isset($_SESSION['idCompte'])) {
    header('Location: ../Controllers/login.php');
    exit;
}

// Vérification que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: gererAbsEtu.php');
    exit;
}

$errors = [];

// Validation des données
$date_start = $_POST['date_start'] ?? '';
$date_end = $_POST['date_end'] ?? '';
$cours = $_POST['cours'] ?? '';
$motif = trim($_POST['motif'] ?? '');

if (empty($motif)) {
    $errors[] = "Le motif est obligatoire.";
}

if (empty($cours)) {
    $errors[] = "Veuillez sélectionner un cours.";
}

// Validation du fichier
if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors[] = "Le justificatif est obligatoire.";
} elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = "Erreur lors de l'upload du fichier.";
} else {
    $file = $_FILES['file'];
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $allowed_types)) {
        $errors[] = "Format de fichier non accepté.";
    }
    
    if ($file['size'] > $max_size) {
        $errors[] = "Le fichier est trop volumineux (max 5MB).";
    }
}

// Si erreurs, rediriger avec message
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header('Location: gererAbsEtu.php');
    exit;
}

// Traitement de l'upload
$dossier = '../uploads/';
if (!is_dir($dossier)) {
    mkdir($dossier, 0755, true);
}

$extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
$nom_fichier = uniqid('justif_') . '.' . $extension;
$fichier = $dossier . $nom_fichier;

if (!move_uploaded_file($_FILES['file']['tmp_name'], $fichier)) {
    $_SESSION['errors'] = ["Erreur lors de l'enregistrement du fichier."];
    header('Location: gererAbsEtu.php');
    exit;
}

// Insertion en base de données
try {
    $pdo = new Database();
    $db = $pdo->getConnection();
    
    $stmt = $db->prepare(
        'INSERT INTO Absence (idCompte, date_debut, date_fin, cours, motif, justificatif) 
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $_SESSION['idCompte'],
        $date_start,
        $date_end,
        $cours,
        $motif,
        $nom_fichier
    ]);
    
    // Envoi du mail
    $stmt = $db->prepare('SELECT nom, prenom FROM Compte WHERE idCompte = ?');
    $stmt->execute([$_SESSION['idCompte']]);
    $user = $stmt->fetch();
    
    if ($user) {
        $dest_email = strtolower($user['prenom']) . '.' . strtolower($user['nom']) . '@uphf.fr';
        
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USERNAME'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        
        $mail->setFrom($_ENV['SMTP_FROM'], 'Gestion des Absences');
        $mail->addAddress($dest_email);
        $mail->Subject = '[GESTION-ABS] Confirmation de dépôt';
        $mail->Body = "Votre justificatif d'absence a été déposé et pris en compte.";
        
        $mail->send();
    }
    
    $_SESSION['success'] = "Votre absence a été justifiée avec succès.";
    header('Location: ../Controllers/accueil_etudiant.php');
    exit;
    
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['errors'] = ["Une erreur est survenue. Veuillez réessayer."];
    header('Location: gererAbsEtu.php');
    exit;
}