<?php
require __DIR__ . '/../vendor/autoload.php';

use src\Controllers\AuthController;
use src\Utils\PseudoCron;
use src\Database\Database;

try {
    $db = new Database();
    $pdo = $db->getConnection();
    $pseudoCron = new PseudoCron($pdo);
    $pseudoCron->execute();
} catch (Exception $e) {
    error_log("Erreur PseudoCron: " . $e->getMessage());
}

// Initialiser le contrôleur d'authentification
$authController = new AuthController();
$message = $authController->checkTimeout();
$messageType = $message ? 'info' : null;

// Traiter la soumission du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $identifiant = trim($_POST['identifiant'] ?? '');
        $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');

        if ($identifiant === '' || $mot_de_passe === '') {
            $message = "Veuillez renseigner l'identifiant et le mot de passe.";
            $messageType = 'error';
        } else {
            $result = $authController->login($identifiant, $mot_de_passe);

            if ($result['success']) {
                header('Location: ' . $result['redirect']);
                exit();
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        }

    } catch (PDOException $e) {
        error_log("Erreur de connexion : " . $e->getMessage());
        $message = "Une erreur s'est produite. Veuillez réessayer.";
        $messageType = 'error';
    } catch (Exception $e) {
        error_log("Erreur inattendue : " . $e->getMessage());
        $message = "Une erreur inattendue s'est produite.";
        $messageType = 'error';
    }
}

// Afficher la vue de connexion
require __DIR__ . '/../src/Views/auth/login.php';
