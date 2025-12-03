<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use src\Database\Database;
use src\Models\GestionCSV;

// Augmenter le temps d'exécution maximum pour les gros fichiers CSV
set_time_limit(300); // 5 minutes
ini_set('memory_limit', '512M'); // Augmenter la mémoire disponible

// Vérifier que l'utilisateur est connecté et a le rôle de secrétaire
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'secretaire') {
    header('Location: /index.php');
    exit();
}

// Vérifier que des fichiers ont été envoyés
if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
    $_SESSION['message'] = "Aucun fichier n'a été sélectionné.";
    $_SESSION['message_type'] = 'error';
    header('Location: /src/Views/secretaire/envoie_des_absences.php');
    exit();
}

try {
    $database = new Database();
    $gestionCSV = new GestionCSV();
    
    $totalStats = [
        'comptes' => 0,
        'etudiants' => 0,
        'ressources' => 0,
        'professeurs' => 0,
        'cours' => 0,
        'absences' => 0,
        'fichiers' => 0
    ];
    
    $files = $_FILES['files'];
    $fileCount = count($files['name']);
    
    // Créer un dossier temporaire pour les fichiers uploadés
    $uploadDir = __DIR__ . '/../uploads/temp_csv/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $errors = [];
    
    for ($i = 0; $i < $fileCount; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $fileName = basename($files['name'][$i]);
            $tmpName = $files['tmp_name'][$i];
            
            // Vérifier que c'est bien un fichier CSV
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if ($fileExtension !== 'csv') {
                $errors[] = "Le fichier $fileName n'est pas un fichier CSV valide.";
                continue;
            }
            
            // Déplacer le fichier uploadé
            $filePath = $uploadDir . time() . '_' . $fileName;
            if (move_uploaded_file($tmpName, $filePath)) {
                try {
                    // Importer les données du CSV
                    $result = $gestionCSV->exportToDB($filePath, $database);
                    $stats = json_decode($result, true);
                    
                    // Ajouter aux statistiques totales
                    $totalStats['comptes'] += $stats['comptes'];
                    $totalStats['etudiants'] += $stats['etudiants'];
                    $totalStats['ressources'] += $stats['ressources'];
                    $totalStats['professeurs'] += $stats['professeurs'];
                    $totalStats['cours'] += $stats['cours'];
                    $totalStats['absences'] += $stats['absences'];
                    $totalStats['fichiers']++;
                    
                    // Fusionner les tableaux d'éléments existants
                    if (isset($stats['etudiants_existants'])) {
                        if (!isset($totalStats['etudiants_existants'])) {
                            $totalStats['etudiants_existants'] = [];
                        }
                        $totalStats['etudiants_existants'] = array_unique(array_merge($totalStats['etudiants_existants'], $stats['etudiants_existants']));
                    }
                    if (isset($stats['professeurs_existants'])) {
                        if (!isset($totalStats['professeurs_existants'])) {
                            $totalStats['professeurs_existants'] = [];
                        }
                        $totalStats['professeurs_existants'] = array_unique(array_merge($totalStats['professeurs_existants'], $stats['professeurs_existants']));
                    }
                    if (isset($stats['ressources_existantes'])) {
                        if (!isset($totalStats['ressources_existantes'])) {
                            $totalStats['ressources_existantes'] = [];
                        }
                        $totalStats['ressources_existantes'] = array_unique(array_merge($totalStats['ressources_existantes'], $stats['ressources_existantes']));
                    }
                    if (isset($stats['cours_existants'])) {
                        if (!isset($totalStats['cours_existants'])) {
                            $totalStats['cours_existants'] = [];
                        }
                        $totalStats['cours_existants'] = array_unique(array_merge($totalStats['cours_existants'], $stats['cours_existants']));
                    }
                    
                    // Supprimer le fichier temporaire
                    unlink($filePath);
                    
                } catch (Exception $e) {
                    $errors[] = "Erreur lors de l'import de $fileName : " . $e->getMessage();
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            } else {
                $errors[] = "Impossible de télécharger le fichier $fileName.";
            }
        } else {
            $errors[] = "Erreur lors du téléchargement du fichier " . $files['name'][$i];
        }
    }
    
    // Préparer le message de retour
    if ($totalStats['fichiers'] > 0) {
        // Calculer les totaux incluant les éléments existants
        $totalEtudiants = $totalStats['etudiants'] + (isset($totalStats['etudiants_existants']) ? count($totalStats['etudiants_existants']) : 0);
        $totalProfesseurs = $totalStats['professeurs'] + (isset($totalStats['professeurs_existants']) ? count($totalStats['professeurs_existants']) : 0);
        $totalRessources = $totalStats['ressources'] + (isset($totalStats['ressources_existantes']) ? count($totalStats['ressources_existantes']) : 0);
        $totalCours = $totalStats['cours'] + (isset($totalStats['cours_existants']) ? count($totalStats['cours_existants']) : 0);
        
        $message = "Import réussi ! <br>";
        $message .= "Fichiers importés : {$totalStats['fichiers']}<br>";
        $message .= "Étudiants traités : {$totalEtudiants}";
        if ($totalStats['etudiants'] > 0) {
            $message .= " (dont {$totalStats['etudiants']} nouveaux)";
        }
        $message .= "<br>";
        $message .= "Professeurs traités : {$totalProfesseurs}";
        if ($totalStats['professeurs'] > 0) {
            $message .= " (dont {$totalStats['professeurs']} nouveaux)";
        }
        $message .= "<br>";
        $message .= "Ressources traitées : {$totalRessources}";
        if ($totalStats['ressources'] > 0) {
            $message .= " (dont {$totalStats['ressources']} nouvelles)";
        }
        $message .= "<br>";
        $message .= "Cours traités : {$totalCours}";
        if ($totalStats['cours'] > 0) {
            $message .= " (dont {$totalStats['cours']} nouveaux)";
        }
        $message .= "<br>";
        $message .= "Absences enregistrées : {$totalStats['absences']}";
        
        if (!empty($errors)) {
            $message .= "<br><br>Quelques erreurs sont survenues :<br>";
            $message .= implode("<br>", $errors);
        }
        
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Aucun fichier n'a pu être importé.<br>" . implode("<br>", $errors);
        $_SESSION['message_type'] = 'error';
    }
    
} catch (Exception $e) {
    $_SESSION['message'] = "Erreur critique : " . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

// Rediriger vers la page d'envoi
header('Location: /src/Views/secretaire/envoie_des_absences.php');
exit();
