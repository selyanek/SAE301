<?php
// Gestion du timeout de session
// Vérifie l'inactivité de l'utilisateur et déconnecte automatiquement après une période d'inactivité

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Durée d'inactivité maximale en secondes (14 minutes)
if (!defined('TIMEOUT_DURATION')) {
    define('TIMEOUT_DURATION', 840);
}

$isAjaxRequest = (
    (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
    (!empty($_SERVER['HTTP_ACCEPT']) && strpos((string) $_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
);

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['login'])) {
    // Vérifier si le timestamp de dernière activité existe
    if (isset($_SESSION['last_activity'])) {
        // Calculer le temps écoulé depuis la dernière activité
        $elapsed_time = time() - $_SESSION['last_activity'];
        // Si le délai d'inactivité est dépassé, déconnecter l'utilisateur
        if ($elapsed_time > TIMEOUT_DURATION) {
            // Détruire la session
            session_unset();
            session_destroy();
            // Supprimer le cookie de session
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }

            if ($isAjaxRequest) {
                http_response_code(401);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'Session expirée, veuillez vous reconnecter.'
                ]);
                exit;
            }

            // Rediriger vers la page de connexion avec un message
            header('Location: /public/index.php?timeout=1');
            exit;
        }
    }
    // Mettre à jour le timestamp de dernière activité
    $_SESSION['last_activity'] = time();
    // Régénérer l'ID de session périodiquement pour plus de sécurité (toutes les 5 minutes)
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}
?>
