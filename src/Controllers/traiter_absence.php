<?php
session_start();

// Vérifier les paramètres
if (!isset($_GET['action']) || !isset($_GET['index'])) {
    header('Location: ../Views/gestionAbsResp.php?error=missing_params');
    exit;
}

$action = $_GET['action'];
$index = (int)$_GET['index'];

if (!in_array($action, ['valider', 'refuser'])) {
    header('Location: ../Views/gestionAbsResp.php?error=invalid_action');
    exit;
}

// Chemins des fichiers
$csvFile = '../data/uploads.csv';
$csvStatuts = '../data/statuts.csv';

// Vérifier si le fichier existe
if (!file_exists($csvFile)) {
    header('Location: ../Views/gestionAbsResp.php?error=no_data');
    exit;
}

// Lire toutes les absences
$handle = fopen($csvFile, 'r');
$absences = [];

if ($handle) {
    while (($data = fgetcsv($handle, 1000, ';', '"', '')) !== FALSE) {
        if (count($data) >= 6) {
            $absences[] = $data;
        }
    }
    fclose($handle);
}

// Inverser pour avoir le bon index
$absences = array_reverse($absences);

// Vérifier que l'index existe
if (!isset($absences[$index])) {
    header('Location: ../Views/gestionAbsResp.php?error=invalid_index');
    exit;
}

// Déterminer le nouveau statut
$nouveauStatut = ($action == 'valider') ? 'valide' : 'refuse';

// Créer le dossier data s'il n'existe pas
$dirData = dirname($csvStatuts);
if (!is_dir($dirData)) {
    mkdir($dirData, 0755, true);
}

// Vérifier si un statut existe déjà pour cette absence
$statutsExistants = [];
if (file_exists($csvStatuts)) {
    $handleStatuts = fopen($csvStatuts, 'r');
    if ($handleStatuts) {
        while (($data = fgetcsv($handleStatuts, 1000, ';', '"', '')) !== FALSE) {
            if (count($data) >= 3) {
                // Format: date_soumission, nom_fichier, statut, date_traitement, responsable
                $key = $data[0] . '|' . $data[1]; // Clé unique
                $statutsExistants[$key] = true;
            }
        }
        fclose($handleStatuts);
    }
}

// Créer la clé unique pour cette absence
$absence = $absences[$index];
$key = $absence[0] . '|' . $absence[4]; // date_soumission|chemin_fichier

// Ajouter le statut dans le fichier des statuts
$fichierStatuts = fopen($csvStatuts, 'a');
if ($fichierStatuts) {
    // Si le statut existe déjà, ne rien faire (on pourrait aussi mettre à jour)
    if (!isset($statutsExistants[$key])) {
        fputcsv($fichierStatuts, [
            $absence[0],           // date_soumission
            $absence[4],           // chemin_fichier
            $nouveauStatut,        // statut
            date('Y-m-d H:i:s'),  // date_traitement
            $_SESSION['user_id'] ?? 'responsable' // responsable qui a traité
        ], ';', '"', '');
    }
    fclose($fichierStatuts);
}

// Redirection avec message de succès
$message = ($action == 'valider') ? 'Absence validée avec succès' : 'Absence refusée';
header('Location: ../Views/gestionAbsResp.php?success=' . urlencode($message));
exit;
?>