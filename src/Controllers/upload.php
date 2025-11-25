<?php
session_start();

use src\Models\Absence;
use src\Database\Database;

require_once '../../vendor/autoload.php';

// Réponse JSON par défaut
header('Content-Type: application/json');


// Vérification champs obligatoires
if (
    empty($_POST['date_start']) ||
    empty($_POST['date_end']) ||
    empty($_POST['motif']) ||
    empty($_FILES['files'])
) {
    echo json_encode([
        "success" => false,
        "message" => "Champs manquants."
    ]);
    exit;
}

$dateStart = $_POST['date_start'];
$dateEnd   = $_POST['date_end'];
$motif     = trim($_POST['motif']);

// Vérifications simples
if ($dateEnd <= $dateStart) {
    echo json_encode([
        "success" => false,
        "message" => "La date de fin doit être après la date de début."
    ]);
    exit;
}

// Configuration upload
$uploadDir = '../../uploads/';
$maxFileSize = 5 * 1024 * 1024;
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
$allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

function sanitizeFileName($filename) {
    return preg_replace("/[^a-zA-Z0-9._-]/", "", $filename);
}

function generateUniqueFileName($uploadDir, $filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $base = pathinfo($filename, PATHINFO_FILENAME);
    $new = $filename;
    $count = 1;

    while (file_exists($uploadDir . $new)) {
        $new = $base . "_" . $count . "." . $ext;
        $count++;
    }
    return $new;
}


// --- TRAITEMENT DE TOUS LES FICHIERS ENVOYÉS ---
$fileNamesSaved = [];

foreach ($_FILES['files']['name'] as $index => $originalName) {

    $tmpName = $_FILES['files']['tmp_name'][$index];
    $size    = $_FILES['files']['size'][$index];
    $type    = $_FILES['files']['type'][$index];
    $error   = $_FILES['files']['error'][$index];

    if ($error !== UPLOAD_ERR_OK) {
        echo json_encode(["success" => false, "message" => "Erreur lors de l'upload d'un fichier."]);
        exit;
    }

    if ($size > $maxFileSize) {
        echo json_encode(["success" => false, "message" => "Un fichier dépasse 5MB."]);
        exit;
    }

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExtensions) || !in_array($type, $allowedTypes)) {
        echo json_encode(["success" => false, "message" => "Format non accepté."]);
        exit;
    }

    $cleanName = sanitizeFileName($originalName);
    $uniqueName = generateUniqueFileName($uploadDir, $cleanName);

    if (!move_uploaded_file($tmpName, $uploadDir . $uniqueName)) {
        echo json_encode(["success" => false, "message" => "Échec du déplacement d'un fichier."]);
        exit;
    }

    $fileNamesSaved[] = $uniqueName;
}


// -------- SAUVEGARDE EN BDD --------

$bd = new Database();
$pdo = $bd->getConnection();
$absence = new Absence($pdo);

$idEtudiant = $_SESSION['idEtudiant'] ?? null;
$idCours = $_POST['idCours'] ?? 1;

// On stocke les noms des fichiers en JSON (ou autre format selon ta BDD)
$absence->setDateDebut($dateStart);
$absence->setDateFin($dateEnd);
$absence->setMotif($motif);
$absence->setJustifie(false);
$absence->setIdEtudiant($idEtudiant);
$absence->setIdCours($idCours);
$absence->setUriJustificatif(json_encode($fileNamesSaved));

$idAbsence = $absence->ajouterAbsence();

if ($idAbsence) {
    echo json_encode([
        "success" => true,
        "message" => "Justificatif envoyé avec succès !"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de l'enregistrement en BDD."
    ]);
}
