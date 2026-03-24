<?php
/**
 * api_login.php
 * 
 * Endpoint Ajax pour la connexion utilisateur
 * Retourne JSON avec succès ou erreur
 */

header('Content-Type: application/json');

require_once '../../vendor/autoload.php';

use src\Models\Login;

try {
    // Vérifier que le request est POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Méthode HTTP non autorisée.'
        ]);
        exit;
    }

    $identifiant = $_POST['identifiant'] ?? '';
    $password = $_POST['mot_de_passe'] ?? '';

    // Validation basique
    if (empty($identifiant) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Identifiant et mot de passe requis.'
        ]);
        exit;
    }

    // Initialiser le modèle de connexion
    $loginModel = new Login();

    // Tentative de connexion
    $user = $loginModel->authenticate($identifiant, $password);

    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Identifiant ou mot de passe incorrect.'
        ]);
        exit;
    }

    // Démarrer la session si elle n'est pas déjà commencée
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Stocker les infos utilisateur en session
    $_SESSION['login'] = $user['identifiantCompte'] ?? $identifiant;
    $_SESSION['prenom'] = $user['prenom'] ?? '';
    $_SESSION['nom'] = $user['nom'] ?? '';
    $_SESSION['role'] = $user['fonction'] ?? 'unknown';
    $_SESSION['idCompte'] = $user['idCompte'] ?? null;
    $_SESSION['identifiantEtu'] = $user['identifiantCompte'] ?? null;

    // Déterminer où rediriger
    $redirectUrl = '/src/Views/etudiant/dashboard.php';
    
    if (isset($user['fonction'])) {
        $role = strtolower(trim($user['fonction']));
        
        if ($role === 'responsable_pedagogique') {
            $redirectUrl = '/src/Views/responsable/dashboard.php';
        } elseif ($role === 'professeur') {
            $redirectUrl = '/src/Views/professeur/dashboard.php';
        } elseif ($role === 'secretaire') {
            $redirectUrl = '/src/Views/secretaire/dashboard.php';
        }
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie.',
        'redirect' => $redirectUrl
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
    exit;
}
