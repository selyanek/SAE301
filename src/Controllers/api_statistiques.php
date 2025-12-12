<?php
/**
 * API REST pour les statistiques
 * Retourne les données au format JSON pour Chart.js
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use src\Controllers\StatistiquesController;

// Headers CORS et JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    $controller = new StatistiquesController();
    $controller->getDonneesJSON();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur serveur',
        'message' => $e->getMessage()
    ]);
}