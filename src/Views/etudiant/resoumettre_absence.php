<?php
// Page de ressoumission d'une absence refusée
session_start();
require '../../Controllers/session_timeout.php';

require_once __DIR__ . '/../../Database/Database.php';
require_once __DIR__ . '/../../Models/Absence.php';

use src\Database\Database;
use src\Models\Absence;

// Vérifier que l'utilisateur est connecté et est un étudiant
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'etudiant' && $_SESSION['role'] !== 'etudiante')) {
    header('Location: /public/index.php');
    exit();
}

$db = new Database();
$absenceModel = new Absence($db->getConnection());
$idAbsence = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$idAbsence) {
    header('Location: justificatif.php');
    exit();
}

// Récupérer l'absence
$absence = $absenceModel->getById($idAbsence);

// Vérifier que l'absence existe, appartient à l'étudiant et peut être ressoumise
$identifiantEtu = $_SESSION['identifiantEtu'] ?? $_SESSION['login'];
if (!$absence || $absence['identifiantetu'] !== $identifiantEtu) {
    $_SESSION['errors'] = ['Cette absence ne vous appartient pas.'];
    header('Location: justificatif.php');
    exit();
}

if ($absence['type_refus'] !== 'ressoumission' || $absence['justifie'] !== false && $absence['justifie'] !== 'f' && $absence['justifie'] !== '0') {
    $_SESSION['errors'] = ['Cette absence ne peut pas être ressoumise.'];
    header('Location: justificatif.php');
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveauMotif = trim($_POST['motif'] ?? '');
    $nouvelleUriJustificatif = null;
    
    // Gérer l'upload de fichier
    if (isset($_FILES['justificatif']) && $_FILES['justificatif']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['justificatif']['name']);
        $uploadFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['justificatif']['tmp_name'], $uploadFile)) {
            $nouvelleUriJustificatif = json_encode([$fileName]);
        }
    } else {
        // Garder l'ancien justificatif si aucun nouveau n'est fourni
        $nouvelleUriJustificatif = $absence['urijustificatif'];
    }
    
    if (empty($nouveauMotif)) {
        $erreur = 'Le motif est obligatoire.';
    } else {
        // Resoumettre l'absence
        $result = $absenceModel->resoumettre($idAbsence, $nouveauMotif, $nouvelleUriJustificatif);
        
        if ($result) {
            $_SESSION['success'] = 'Votre absence a été resoumise avec succès ! Elle est maintenant en attente de validation.';
            header('Location: justificatif.php');
            exit();
        } else {
            $erreur = 'Erreur lors de la ressoumission. Veuillez réessayer.';
        }
    }
}

$pageTitle = 'Modifier et resoumettre une absence';
$additionalCSS = ['../../../public/asset/CSS/cssGererAbsEtu.css', '../../../public/asset/CSS/cssRessoumission.css'];
require '../layout/header.php';
require '../layout/navigation.php';
?>

<header class="text">
    <h1>Modifier et resoumettre votre absence</h1>
    <p>Votre absence a été refusée. Vous pouvez la modifier et la resoumettre.</p>
</header>

<?php if (isset($erreur)): ?>
<div class="error-messages">
    <p class="error"><?php echo htmlspecialchars($erreur); ?></p>
</div>
<?php endif; ?>

<div class="ressoumission-container">
    <div class="info-absence">
        <h3>Informations de l'absence</h3>
        <p><strong>Cours :</strong> <?php echo htmlspecialchars($absence['ressource_nom'] ?? $absence['cours_type'] ?? 'N/A'); ?></p>
        <p><strong>Début :</strong> <?php echo htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_debut']))); ?></p>
        <p><strong>Fin :</strong> <?php echo htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_fin']))); ?></p>
        <div class="raison-refus-box">
            <strong>Raison du refus :</strong>
            <p><?php echo htmlspecialchars($absence['raison_refus'] ?? 'Non précisée'); ?></p>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" class="form-ressoumission">
        <div class="form-group">
            <label for="motif"><strong>Nouveau motif * :</strong></label>
            <textarea name="motif" id="motif" rows="5" required placeholder="Expliquez votre absence..."><?php echo isset($_POST['motif']) ? htmlspecialchars($_POST['motif']) : htmlspecialchars($absence['motif'] ?? ''); ?></textarea>
            <small>Veuillez fournir un motif clair et détaillé pour votre absence.</small>
        </div>

        <div class="form-group">
            <label for="justificatif"><strong>Nouveau justificatif (optionnel) :</strong></label>
            <input type="file" name="justificatif" id="justificatif" accept=".pdf,.jpg,.jpeg,.png">
            <small>Si vous ne téléchargez pas de nouveau fichier, l'ancien justificatif sera conservé.</small>
            <?php if (!empty($absence['urijustificatif'])): ?>
                <p style="margin-top: 10px;"><strong>Justificatif actuel :</strong> 
                    <?php
                    $fichiers = json_decode($absence['urijustificatif'], true);
                    if (is_array($fichiers) && count($fichiers) > 0) {
                        foreach ($fichiers as $fichier) {
                            echo htmlspecialchars($fichier) . ' ';
                        }
                    }
                    ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">Resoumettre l'absence</button>
            <a href="justificatif.php"><button type="button" class="btn-cancel">Annuler</button></a>
        </div>
    </form>
</div>

<?php
require '../layout/footer.php';
?>
