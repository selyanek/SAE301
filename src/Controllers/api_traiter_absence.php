<?php
/**
 * api_traiter_absence.php
 * 
 * Endpoint Ajax pour les opérations de traitement des absences
 * - Valider une absence
 * - Refuser une absence
 * - Demander des justificatifs supplémentaires
 */

header('Content-Type: application/json');

require_once '../../vendor/autoload.php';
require_once 'session_timeout.php';

use src\Models\Absence;
use src\Models\EmailService;
use src\Database\Database;

try {
    // Vérifier l'authentification
    if (!isset($_SESSION['login'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Non authentifié. Veuillez vous reconnecter.'
        ]);
        exit;
    }

    // Vérifier que le request est POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Méthode HTTP non autorisée.'
        ]);
        exit;
    }

    $action = $_POST['action'] ?? 'unknown';
    $idAbsence = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($idAbsence <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID absence invalide.'
        ]);
        exit;
    }

    // Initialiser les modèles
    $absenceModel = new Absence();
    $absence = $absenceModel->getById($idAbsence);

    if (!$absence) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Absence non trouvée.'
        ]);
        exit;
    }

    // Router les actions
    switch ($action) {
        case 'valider':
            validerAbsence($idAbsence, $absence, $absenceModel);
            break;

        case 'refuser':
            refuserAbsence($idAbsence, $absenceModel);
            break;

        case 'envoyer_demande_justif':
            envoyerDemandejustificatif($idAbsence, $absence, $absenceModel);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Action inconnue: ' . htmlspecialchars($action)
            ]);
            exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
    exit;
}

/**
 * Valider une absence
 */
function validerAbsence($idAbsence, $absence, $absenceModel) {
    if ($absenceModel->updateAbsenceStatus($idAbsence, 'valide', null)) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Absence validée avec succès.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la validation.'
        ]);
    }
}

/**
 * Refuser une absence
 */
function refuserAbsence($idAbsence, $absenceModel) {
    $raisonRefus = $_POST['raison_refus'] ?? '';

    if (empty($raisonRefus)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'La raison du refus est obligatoire.'
        ]);
        exit;
    }

    if ($absenceModel->updateAbsenceStatus($idAbsence, 'refuse', $raisonRefus)) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Absence refusée avec succès.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors du refus.'
        ]);
    }
}

/**
 * Envoyer une demande de justificatifs
 */
function envoyerDemandejustificatif($idAbsence, $absence, $absenceModel) {
    $motif = $_POST['motif_demande'] ?? '';

    if (empty($motif)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Le motif de la demande est obligatoire.'
        ]);
        exit;
    }

    try {
        // Récupérer l'étudiant
        $idEtudiant = $absence['idEtudiant'] ?? null;
        if (!$idEtudiant) {
            throw new Exception('Étudiant non trouvé.');
        }

        // Envoyer l'email via le service
        $emailService = new EmailService();
        $studentEmail = ($absence['identifiantEtu'] ?? 'student') . '@uphf.fr';
        $studentName = $absence['nom'] . ' ' . $absence['prenom'];

        $success = $emailService->sendDemandJustificatifsEmail(
            $studentEmail,
            $studentName,
            $motif
        );

        if ($success) {
            // Marquer l'absence comme ayant une demande en cours
            $pdo = Database::connect();
            $stmt = $pdo->prepare('UPDATE Absence SET revision = TRUE WHERE idAbsence = ?');
            $stmt->execute([$idAbsence]);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Demande de justificatifs envoyée avec succès.'
            ]);
        } else {
            throw new Exception('Erreur lors de l\'envoi de l\'email.');
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur: ' . $e->getMessage()
        ]);
    }
}
