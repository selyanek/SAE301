<?php
session_start();
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../Database/Database.php';
require __DIR__ . '/../Models/Absence.php';
require __DIR__ . '/../Models/HistoriqueDecision.php';
require __DIR__ . '/session_timeout.php';
require __DIR__ . '/GetFiles.php';

// Init des modeles
$dbClass = '\\src\\Database\\Database';
$absenceClass = '\\src\\Models\\Absence';
$historiqueClass = '\\src\\Models\\HistoriqueDecision';

$db = new $dbClass();
$pdo = $db->getConnection();
$absenceModel = new $absenceClass($pdo);
$historiqueModel = new $historiqueClass($pdo);

$absence = null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$idResp = $_SESSION['idCompte'] ?? null;

// GET : actions
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    if ($action === 'valider') {
        // Récupérer l'absence avant modification pour obtenir l'ancien statut
        $abs = $absenceModel->getById($id);
        $ancienStatut = $abs['justifie'] ?? null;
        $ok = $absenceModel->updateJustifie($id, true);

        // Envoi d'email non bloquant pour notifier l'étudiant
        try {
            if ($ok && $abs) {
                $emailService = new \src\Models\EmailService();
                $identifiant = $abs['identifiantcompte'] ?? '';
                $nomEtu = trim(($abs['prenomcompte'] ?? '') . ' ' . ($abs['nomcompte'] ?? ''));
                $emailEtu = (strpos($identifiant, '@') !== false) ? $identifiant : $identifiant . '@uphf.fr';
                if (filter_var($emailEtu, FILTER_VALIDATE_EMAIL)) {
                    $emailService->sendRevisionDecisionEmail($emailEtu, $nomEtu, $abs['date_debut'], $abs['date_fin'],
                        ($ancienStatut === true ? 'valide' : ($ancienStatut === false ? 'refuse' : 'en_attente')),
                        'valide',
                        'Votre absence a été validée.');
                }
            }
        } catch (Exception $e) {
            error_log('Erreur envoi email validation: ' . $e->getMessage());
        }

        header('Location: ../Views/responsable/gestionAbsence.php');
        exit();
    } 
    elseif ($action === 'refuser') {
        // Récupérer l'absence avant modification pour obtenir l'ancien statut
        $abs = $absenceModel->getById($id);
        $ancienStatut = $abs['justifie'] ?? null;
        $ok = $absenceModel->updateJustifie($id, false);

        // Envoi d'email non bloquant pour notifier l'étudiant
        try {
            if ($ok && $abs) {
                $emailService = new \src\Models\EmailService();
                $identifiant = $abs['identifiantcompte'] ?? '';
                $nomEtu = trim(($abs['prenomcompte'] ?? '') . ' ' . ($abs['nomcompte'] ?? ''));
                $emailEtu = (strpos($identifiant, '@') !== false) ? $identifiant : $identifiant . '@uphf.fr';
                if (filter_var($emailEtu, FILTER_VALIDATE_EMAIL)) {
                    $emailService->sendRevisionDecisionEmail($emailEtu, $nomEtu, $abs['date_debut'], $abs['date_fin'],
                        ($ancienStatut === true ? 'valide' : ($ancienStatut === false ? 'refuse' : 'en_attente')),
                        'refuse',
                        'Votre absence a été refusée.');
                }
            }
        } catch (Exception $e) {
            error_log('Erreur envoi email refus: ' . $e->getMessage());
        }

        header('Location: ../Views/responsable/gestionAbsence.php');
        exit();
    }
    // US-9 : verrouiller
    elseif ($action === 'verrouiller') {
        if (!$idResp) {
            header('Location: ../Views/responsable/historiqueAbsResp.php?error=' . urlencode('Session expiree'));
            exit();
        }
        
        $abs = $absenceModel->getById($id);
        if (!$abs) {
            header('Location: ../Views/responsable/historiqueAbsResp.php?error=' . urlencode('Absence introuvable'));
            exit();
        }
        
        if ($abs['justifie'] === null) {
            header('Location: ../Views/responsable/historiqueAbsResp.php?error=' . urlencode('Impossible de verrouiller une absence en attente'));
            exit();
        }
        
        $ok = $absenceModel->verrouiller($id, $idResp);
        
        if ($ok) {
            $historiqueModel->ajouter([
                'id_absence' => $id,
                'id_responsable' => $idResp,
                'ancien_verrouillage' => false,
                'nouveau_verrouillage' => true,
                'type_action' => 'verrouillage',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            header('Location: ../Views/responsable/historiqueAbsResp.php?success=' . urlencode('Decision verrouillee'));
        } else {
            header('Location: ../Views/responsable/historiqueAbsResp.php?error=' . urlencode('Erreur verrouillage'));
        }
        exit();
    }
    // US-9 : deverrouiller
    elseif ($action === 'deverrouiller') {
        if (!$idResp) {
            header('Location: ../Views/responsable/historiqueAbsResp.php?error=' . urlencode('Session expiree'));
            exit();
        }
        
        $abs = $absenceModel->getById($id);
        if (!$abs) {
            header('Location: ../Views/responsable/historiqueAbsResp.php?error=' . urlencode('Absence introuvable'));
            exit();
        }
        
        $ok = $absenceModel->deverrouiller($id);
        
        if ($ok) {
            $historiqueModel->ajouter([
                'id_absence' => $id,
                'id_responsable' => $idResp,
                'ancien_verrouillage' => true,
                'nouveau_verrouillage' => false,
                'type_action' => 'deverrouillage',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            // Email a l'etudiant
            $emailService = new \src\Models\EmailService();
            $identifiant = $abs['identifiantcompte'] ?? '';
            $nomEtu = trim(($abs['prenomcompte'] ?? '') . ' ' . ($abs['nomcompte'] ?? ''));
            $emailEtu = (strpos($identifiant, '@') !== false) ? $identifiant : $identifiant . '@uphf.fr';
            
            $emailService->sendDeverrouillageEmail($emailEtu, $nomEtu, $abs['date_debut'], $abs['date_fin']);
            
            header('Location: ../Views/responsable/historiqueAbsResp.php?success=' . urlencode('Deverrouille - Email envoye'));
        } else {
            header('Location: ../Views/responsable/historiqueAbsResp.php?error=' . urlencode('Erreur deverrouillage'));
        }
        exit();
    }
    // US-9 : historique JSON
    elseif ($action === 'voir_historique') {
        header('Content-Type: application/json');
        $historique = $historiqueModel->getByAbsence($id);
        echo json_encode(['success' => true, 'historique' => $historique]);
        exit();
    }
}

// GET sans action = afficher details
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($id !== null) {
        $absence = $absenceModel->getById($id);
    }
    require __DIR__ . '/../Views/responsable/traitementDesJustificatif.php';
    exit();
}

// POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $idPost = isset($_POST['id']) ? (int)$_POST['id'] : null;
    
    if ($idPost !== null) {
        if ($action === 'valider') {
            // Récupérer l'absence avant modification
            $abs = $absenceModel->getById($idPost);
            $ancienStatut = $abs['justifie'] ?? null;
            $result = $absenceModel->updateJustifie($idPost, true);

            // Envoi d'email non bloquant
            try {
                if ($result && $abs) {
                    $emailService = new \src\Models\EmailService();
                    $identifiant = $abs['identifiantcompte'] ?? '';
                    $nomEtu = trim(($abs['prenomcompte'] ?? '') . ' ' . ($abs['nomcompte'] ?? ''));
                    $emailEtu = (strpos($identifiant, '@') !== false) ? $identifiant : $identifiant . '@uphf.fr';
                    if (filter_var($emailEtu, FILTER_VALIDATE_EMAIL)) {
                        $emailService->sendRevisionDecisionEmail($emailEtu, $nomEtu, $abs['date_debut'], $abs['date_fin'],
                            ($ancienStatut === true ? 'valide' : ($ancienStatut === false ? 'refuse' : 'en_attente')),
                            'valide',
                            'Votre absence a été validée.');
                    }
                }
            } catch (Exception $e) {
                error_log('Erreur envoi email validation POST: ' . $e->getMessage());
            }

            $msg = $result ? 'success=' . urlencode('Validee') : 'error=' . urlencode('Erreur');
            header('Location: ../Views/responsable/gestionAbsence.php?' . $msg);
            exit();
        } 
        elseif ($action === 'refuser') {
            $raisonRefus = isset($_POST['raison_refus']) ? trim($_POST['raison_refus']) : null;
            $typeRefus = isset($_POST['type_refus']) ? trim($_POST['type_refus']) : 'definitif';
            
            if (!in_array($typeRefus, ['definitif', 'ressoumission'])) {
                $typeRefus = 'definitif';
            }
            
            // Récupérer l'absence avant modification
            $abs = $absenceModel->getById($idPost);
            $ancienStatut = $abs['justifie'] ?? null;

            $result = $absenceModel->updateJustifie($idPost, false, $raisonRefus, $typeRefus);

            // Envoi d'email non bloquant
            try {
                if ($result && $abs) {
                    $emailService = new \src\Models\EmailService();
                    $identifiant = $abs['identifiantcompte'] ?? '';
                    $nomEtu = trim(($abs['prenomcompte'] ?? '') . ' ' . ($abs['nomcompte'] ?? ''));
                    $emailEtu = (strpos($identifiant, '@') !== false) ? $identifiant : $identifiant . '@uphf.fr';
                    if (filter_var($emailEtu, FILTER_VALIDATE_EMAIL)) {
                        $emailService->sendRevisionDecisionEmail($emailEtu, $nomEtu, $abs['date_debut'], $abs['date_fin'],
                            ($ancienStatut === true ? 'valide' : ($ancienStatut === false ? 'refuse' : 'en_attente')),
                            'refuse',
                            $raisonRefus ?: 'Votre absence a été refusée.');
                    }
                }
            } catch (Exception $e) {
                error_log('Erreur envoi email refus POST: ' . $e->getMessage());
            }

            $message = ($typeRefus === 'ressoumission') 
                ? 'Refusee - Ressoumission possible' 
                : 'Refusee definitivement';
            $msg = $result ? 'success=' . urlencode($message) : 'error=' . urlencode('Erreur');
            header('Location: ../Views/responsable/gestionAbsence.php?' . $msg);
            exit();
        } 
        elseif ($action === 'Demande_justif') {
            $absenceModel->setEnRevision($idPost, true);
            header('Location: ../Views/responsable/traitementDesJustificatif.php?id=' . $idPost . '&demande=true');
            exit();
        } 
        elseif ($action === 'envoyer_demande_justif') {
            $absence = $absenceModel->getById($idPost);
            if (!$absence) {
                header('Location: ../Views/responsable/gestionAbsence.php?error=' . urlencode('Absence non trouvee'));
                exit();
            }
            
            $motif = trim($_POST['motif_demande'] ?? '');
            if (empty($motif)) {
                header('Location: ../Views/responsable/traitementDesJustificatif.php?id=' . $idPost . '&demande=true&error=champ_vide');
                exit();
            }
            
            $emailService = new \src\Models\EmailService();
            $identifiant = $absence['identifiantcompte'] ?? '';
            $studentName = trim(($absence['prenomcompte'] ?? '') . ' ' . ($absence['nomcompte'] ?? ''));
            
            if (empty($identifiant)) {
                header('Location: ../Views/responsable/traitementDesJustificatif.php?id=' . $idPost . '&demande=true&error=identifiant_manquant');
                exit();
            }
            
            $studentEmail = (strpos($identifiant, '@') !== false) ? $identifiant : $identifiant . '@uphf.fr';
            
            if (!filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
                header('Location: ../Views/responsable/traitementDesJustificatif.php?id=' . $idPost . '&demande=true&error=email_invalide');
                exit();
            }
            
            $success = $emailService->sendJustificationRequestEmail($studentEmail, $studentName, $motif);
            
            if ($success) {
                header('Location: ../Views/responsable/traitementDesJustificatif.php?id=' . $idPost . '&email_sent=true');
            } else {
                header('Location: ../Views/responsable/traitementDesJustificatif.php?id=' . $idPost . '&demande=true&error=envoi_echoue');
            }
            exit();
        }
        // US-9 : reviser
        elseif ($action === 'reviser') {
            if (!$idResp) {
                header('Location: ../Views/responsable/historiqueAbsResp.php?error=' . urlencode('Session expiree'));
                exit();
            }
            
            $abs = $absenceModel->getById($idPost);
            if (!$abs) {
                header('Location: ../Views/responsable/historiqueAbsResp.php?error=' . urlencode('Absence introuvable'));
                exit();
            }
            
            $nouveauStatut = $_POST['nouveau_statut'] ?? '';
            $nouvelleRaison = trim($_POST['nouvelle_raison'] ?? '');
            $justif = trim($_POST['justification_revision'] ?? '');
            
            if (empty($justif)) {
                header('Location: ../Views/responsable/historiqueAbsResp.php?error=' . urlencode('Justification obligatoire'));
                exit();
            }
            
            $ancienStatut = $abs['justifie'];
            if ($nouveauStatut === 'valide') {
                $nouveauStatutBool = true;
            } elseif ($nouveauStatut === 'refuse') {
                $nouveauStatutBool = false;
            } else {
                $nouveauStatutBool = null;
            }
            
            $ok = $absenceModel->reviserDecision($idPost, $nouveauStatutBool, $nouvelleRaison, $idResp);
            
            if ($ok) {
                $historiqueModel->ajouter([
                    'id_absence' => $idPost,
                    'id_responsable' => $idResp,
                    'ancien_statut' => $ancienStatut === true ? 'valide' : ($ancienStatut === false ? 'refuse' : 'en_attente'),
                    'ancienne_raison' => $abs['raison_refus'] ?? null,
                    'nouveau_statut' => $nouveauStatut,
                    'nouvelle_raison' => $nouvelleRaison,
                    'type_action' => 'revision',
                    'justification' => $justif,
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                // Email
                $emailService = new \src\Models\EmailService();
                $identifiant = $abs['identifiantcompte'] ?? '';
                $nomEtu = trim(($abs['prenomcompte'] ?? '') . ' ' . ($abs['nomcompte'] ?? ''));
                $emailEtu = (strpos($identifiant, '@') !== false) ? $identifiant : $identifiant . '@uphf.fr';
                
                $ancienLabel = $ancienStatut === true ? 'valide' : ($ancienStatut === false ? 'refuse' : 'en_attente');
                $emailService->sendRevisionDecisionEmail($emailEtu, $nomEtu, $abs['date_debut'], $abs['date_fin'], $ancienLabel, $nouveauStatut, $justif);
                
                header('Location: ../Views/responsable/historiqueAbsResp.php?success=' . urlencode('Revisee - Email envoye'));
            } else {
                header('Location: ../Views/responsable/historiqueAbsResp.php?error=' . urlencode('Erreur revision'));
            }
            exit();
        }
    }
    
    header('Location: ../Views/responsable/gestionAbsence.php');
    exit();
}
?>
