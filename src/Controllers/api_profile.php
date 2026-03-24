<?php
/**
 * api_profile.php
 * 
 * Endpoint Ajax pour les opérations de profil utilisateur
 * Gère le changement de mot de passe via Ajax
 */

header('Content-Type: application/json');
session_start();

require_once '../../vendor/autoload.php';

use src\Models\ProfileModel;

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
    $identifiant = $_SESSION['login'];

    // Initialiser le modèle
    $profileModel = new ProfileModel();

    // Router les actions
    switch ($action) {
        case 'update_password':
            updatePassword($profileModel, $identifiant);
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
 * Mettre à jour le mot de passe de l'utilisateur
 */
function updatePassword($profileModel, $identifiant) {
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validation basique
    if (empty($old) || empty($new) || empty($confirm)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Veuillez remplir tous les champs.'
        ]);
        exit;
    }

    // Vérifier que les mots de passe correspondent
    if ($new !== $confirm) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Les nouveaux mots de passe ne correspondent pas.'
        ]);
        exit;
    }

    // Vérifier la longueur du nouveau mot de passe
    if (strlen($new) < 6) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Le nouveau mot de passe doit contenir au moins 6 caractères.'
        ]);
        exit;
    }

    // Récupérer l'utilisateur
    $user = $profileModel->getUserByIdentifiant($identifiant);

    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Utilisateur introuvable.'
        ]);
        exit;
    }

    // Vérifier l'ancien mot de passe
    if (!password_verify($old, $user['mot_de_passe'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'L\'ancien mot de passe est incorrect.'
        ]);
        exit;
    }

    // Mettre à jour le mot de passe
    if ($profileModel->updateUserPassword($identifiant, $new)) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Mot de passe mis à jour avec succès.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour du mot de passe.'
        ]);
    }
}
