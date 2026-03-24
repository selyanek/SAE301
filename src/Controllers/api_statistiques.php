<?php
/**
 * API REST pour les statistiques
 * Retourne les données au format JSON pour Chart.js
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/session_timeout.php';

use src\Database\Database;
use src\Models\Statistiques;

// Headers JSON
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Méthode non autorisée.'
        ]);
        exit;
    }

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'responsable_pedagogique') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Accès refusé.'
        ]);
        exit;
    }

    $filtres = [
        'annee_but' => $_GET['annee_but'] ?? '',
        'type_cours' => $_GET['type_cours'] ?? '',
        'matiere' => $_GET['matiere'] ?? '',
        'groupe' => $_GET['groupe'] ?? '',
        'date_debut' => $_GET['date_debut'] ?? '',
        'date_fin' => $_GET['date_fin'] ?? ''
    ];

    $db = new Database();
    $pdo = $db->getConnection();
    $stats = new Statistiques($pdo);
    $stats->chargerAbsences($filtres);

    $globales = $stats->calculerStatistiquesGlobales();
    $donnees = $stats->getDonneesAPI();

    echo json_encode(array_merge([
        'success' => true,
        'globales' => $globales,
        'donnees' => $donnees
    ], $donnees));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur',
        'message' => $e->getMessage()
    ]);
}