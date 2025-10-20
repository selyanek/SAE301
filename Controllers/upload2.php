<?php
session_start();

// Vérifier que c'est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Views/depotJustif.php?error=invalid_request');
    exit;
}

// Récupération et nettoyage des données
$date_start = trim($_POST['date_start'] ?? '');
$date_end = trim($_POST['date_end'] ?? '');
$motif = trim($_POST['motif'] ?? '');

// Validation du motif
if (empty($motif)) {
    header('Location: ../Views/depotJustif.php?error=motif&date_start=' . urlencode($date_start) . '&date_end=' . urlencode($date_end));
    exit;
}

// Validation des dates
if (empty($date_start) || empty($date_end)) {
    header('Location: ../Views/depotJustif.php?error=dates&motif=' . urlencode($motif));
    exit;
}

if (strtotime($date_end) < strtotime($date_start)) {
    header('Location: ../Views/depotJustif.php?error=dates_invalides&motif=' . urlencode($motif) . '&date_start=' . urlencode($date_start) . '&date_end=' . urlencode($date_end));
    exit;
}

// Validation du fichier
if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
    header('Location: ../Views/depotJustif.php?error=file_required&motif=' . urlencode($motif) . '&date_start=' . urlencode($date_start) . '&date_end=' . urlencode($date_end));
    exit;
}

$file = $_FILES['file'];

// Vérifier les erreurs d'upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    header('Location: ../Views/depotJustif.php?error=upload_failed&motif=' . urlencode($motif) . '&date_start=' . urlencode($date_start) . '&date_end=' . urlencode($date_end));
    exit;
}

// Vérifier la taille (5MB max)
$maxSize = 5 * 1024 * 1024; // 5MB en octets
if ($file['size'] > $maxSize) {
    header('Location: ../Views/depotJustif.php?error=file_size&motif=' . urlencode($motif) . '&date_start=' . urlencode($date_start) . '&date_end=' . urlencode($date_end));
    exit;
}

// Vérifier le type de fichier
$allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    header('Location: ../Views/depotJustif.php?error=file_type&motif=' . urlencode($motif) . '&date_start=' . urlencode($date_start) . '&date_end=' . urlencode($date_end));
    exit;
}

// Créer le dossier uploads s'il n'existe pas
$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Générer un nom de fichier unique et sécurisé
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$nomFichierUnique = uniqid('justif_', true) . '_' . time() . '.' . $extension;
$destination = $uploadDir . $nomFichierUnique;

// Déplacer le fichier uploadé
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    header('Location: ../Views/depotJustif.php?error=upload_failed&motif=' . urlencode($motif) . '&date_start=' . urlencode($date_start) . '&date_end=' . urlencode($date_end));
    exit;
}

// Enregistrer dans le CSV des uploads
$csvUploads = '../data/uploads.csv';
$dirData = dirname($csvUploads);
if (!is_dir($dirData)) {
    mkdir($dirData, 0755, true);
}

$fichierUploads = fopen($csvUploads, 'a');
if ($fichierUploads) {
    fputcsv($fichierUploads, [
        date('Y-m-d H:i:s'),
        $date_start,
        $date_end,
        $motif,
        $destination,
        $file['name']
    ], ';', '"', '');
    fclose($fichierUploads);
}

// Enregistrer les informations principales dans utilisateurs.csv
$csvUtilisateurs = '../data/utilisateurs.csv';
$fichierUtilisateurs = fopen($csvUtilisateurs, 'a');
if ($fichierUtilisateurs) {
    fputcsv($fichierUtilisateurs, [
        date('Y-m-d H:i:s'),
        $date_start,
        $date_end,
        $motif,
        $nomFichierUnique
    ], ';', '"', '');
    fclose($fichierUtilisateurs);
}

// Redirection vers la page avec message de succès
header('Location: ../Views/depotJustif.php?success=1');
exit;
?>