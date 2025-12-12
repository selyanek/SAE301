<?php
session_start();
require __DIR__ . '/../../vendor/autoload.php';
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
            // Rediriger vers le formulaire de demande
            header('Location: ../Views/traitementDesJustificatif.php?id=' . $idPost . '&demande=true');
            exit();
        } elseif ($action === 'envoyer_demande_justif') {
            // Envoyer l'email à l'étudiant
            $absence = $absenceModel->getById($idPost);
            
            if (!$absence) {
                header('Location: ../Views/gestionAbsResp.php?error=' . urlencode('Absence non trouvée'));
                exit();
            }
            
            $motif = trim($_POST['motif_demande'] ?? '');
            
            if (empty($motif)) {
                header('Location: ../Views/traitementDesJustificatif.php?id=' . $idPost . '&demande=true&error=champ_vide');
                exit();
            }
            
            $emailService = new \src\Models\EmailService();
            
            // Récupérer l'identifiant de l'étudiant (comme dans monProfil.php)
            $identifiant = $absence['identifiantcompte'] ?? '';
            $studentName = trim(($absence['prenomcompte'] ?? '') . ' ' . ($absence['nomcompte'] ?? ''));
            
            // Construire l'email comme dans monProfil.php : si ce n'est pas déjà un email, ajouter @uphf.fr
            if (empty($identifiant)) {
                header('Location: ../Views/traitementDesJustificatif.php?id=' . $idPost . '&demande=true&error=identifiant_manquant');
                exit();
            }
            
            // Si l'identifiant contient déjà un @, c'est un email, sinon on ajoute @uphf.fr
            $studentEmail = (strpos($identifiant, '@') !== false) ? $identifiant : $identifiant . '@uphf.fr';
            
            // Valider l'email final
            if (!filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
                header('Location: ../Views/traitementDesJustificatif.php?id=' . $idPost . '&demande=true&error=email_invalide');
                exit();
            }
            
            $success = $emailService->sendJustificationRequestEmail($studentEmail, $studentName, $motif);
            
            if ($success) {
                header('Location: ../Views/traitementDesJustificatif.php?id=' . $idPost . '&email_sent=true');
            } else {
                header('Location: ../Views/traitementDesJustificatif.php?id=' . $idPost . '&demande=true&error=envoi_echoue');
            }
            exit();
        }
    }
    
    // Si aucune action valide, rediriger sans message
    header('Location: ../Views/gestionAbsResp.php');
    exit();
}

?>
