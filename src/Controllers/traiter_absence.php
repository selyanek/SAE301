<?php
session_start();
require __DIR__ . '/../Database/Database.php';
require __DIR__ . '/../Models/Absence.php';
require __DIR__ . '/session_timeout.php';
require __DIR__ . '/GetFiles.php';

// Noms de classes fully-qualified pour éviter d'utiliser 'use' après du code exécutable
$dbClass = '\\src\\Database\\Database';
$absenceClass = '\\src\\Models\\Absence';
$db = new $dbClass();
$pdo = $db->getConnection();
$absenceModel = new $absenceClass($pdo);

$absence = null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// A présent, nous opérons sur la base de données via le modèle Absence ($absenceModel)

// Gérer la compatibilité GET : action=valider/refuser&id=<id>
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    if ($action === 'valider') {
        $absenceModel->updateJustifie($id, true);
    } elseif ($action === 'refuser') {
        $absenceModel->updateJustifie($id, false);
    }
    header('Location: ../Views/gestionAbsResp.php');
    exit();
}

// Vue des détails - GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($id !== null) {
        $absence = $absenceModel->getById($id);
    }
    require __DIR__ . '/../Views/traitementDesJustificatif.php';
    exit();
}

// POST : mise à jour du statut via la BDD (champ boolean 'justifie')
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $idPost = isset($_POST['id']) ? (int)$_POST['id'] : null;
    
    if ($idPost !== null) {
        if ($action === 'valider') {
            $result = $absenceModel->updateJustifie($idPost, true);
            if ($result) {
                header('Location: ../Views/gestionAbsResp.php?success=' . urlencode('Absence validée avec succès'));
            } else {
                header('Location: ../Views/gestionAbsResp.php?error=' . urlencode('Erreur lors de la validation'));
            }
            exit();
        } elseif ($action === 'refuser') {
            // Récupérer la raison du refus si elle est fournie
            $raisonRefus = isset($_POST['raison_refus']) ? trim($_POST['raison_refus']) : null;
            $result = $absenceModel->updateJustifie($idPost, false, $raisonRefus);
            if ($result) {
                header('Location: ../Views/gestionAbsResp.php?success=' . urlencode('Absence refusée avec succès'));
            } else {
                header('Location: ../Views/gestionAbsResp.php?error=' . urlencode('Erreur lors du refus'));
            }
            exit();
        } elseif ($action === 'Demande_justif') {
            // TODO: Implémenter la logique pour demander un justificatif
            // Pour l'instant, on redirige simplement
            header('Location: ../Views/gestionAbsResp.php?info=' . urlencode('Demande de justificatif envoyée'));
            exit();
        }
    }
    
    // Si aucune action valide, rediriger sans message
    header('Location: ../Views/gestionAbsResp.php');
    exit();
}

?>
