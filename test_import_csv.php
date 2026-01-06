<?php
// Initialisation de la session et chargement des dépendances
session_start();
require_once __DIR__ . '/vendor/autoload.php';

use src\Database\Database;
use src\Models\GestionCSV;

// Simuler un utilisateur secrétaire pour les permissions
$_SESSION['role'] = 'secretaire';

// Chemin du fichier CSV à importer
$csvFile = __DIR__ . '/data/CSV/Evaluation-260106-10absences.CSV';

// Vérifier que le fichier existe
if (!file_exists($csvFile)) {
    die("ERREUR: Fichier CSV introuvable\n");
}

echo "Import du fichier: $csvFile\n\n";

try {
    // Connexion à la base de données
    $database = new Database();
    $gestionCSV = new GestionCSV();
    $pdo = $database->getConnection();
    
    // Lancer l'import et mesurer le temps d'exécution
    $startTime = microtime(true);
    $result = $gestionCSV->exportToDB($csvFile, $database);
    $stats = json_decode($result, true);
    $duration = round($endTime - $startTime, 2);
    
    // Afficher les statistiques d'import
    echo "STATISTIQUES:\n";
    echo "  Comptes      : " . ($stats['comptes'] ?? 0) . "\n";
    echo "  Etudiants    : " . ($stats['etudiants'] ?? 0) . "\n";
    echo "  Professeurs  : " . ($stats['professeurs'] ?? 0) . "\n";
    echo "  Ressources   : " . ($stats['ressources'] ?? 0) . "\n";
    echo "  Cours        : " . ($stats['cours'] ?? 0) . "\n";
    echo "  Absences     : " . ($stats['absences'] ?? 0) . "\n\n";
    
    echo "ABSENCES IMPORTEES:\n";
    
    // Récupérer les absences du cours d'évaluation
    $sql = "SELECT 
                CONCAT(c.prenom, ' ', c.nom) as etudiant,
                co.type,
                TO_CHAR(a.date_debut, 'DD/MM/YYYY HH24:MI') as date,
                a.motif,
                CASE 
                    WHEN a.justifie IS NULL THEN 'En attente'
                    WHEN a.justifie = TRUE THEN 'Justifiee'
                    ELSE 'Non justifiee'
                END as statut
            FROM Absence a
            INNER JOIN Etudiant e ON a.idEtudiant = e.idEtudiant
            INNER JOIN Compte c ON e.idEtudiant = c.idCompte
            INNER JOIN Cours co ON a.idCours = co.idCours
            INNER JOIN Ressource r ON co.idRessource = r.idRessource
            WHERE r.nom = 'INFFIS3-GESTION DE PROJET & DES ORGANISATIONS (T3BUTINFFI-R3.09)'
              AND co.evaluation = TRUE
            ORDER BY a.idAbsence DESC
            LIMIT 15";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $absences = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Afficher chaque absence sur une ligne
    foreach ($absences as $i => $a) {
        echo ($i + 1) . ". {$a['etudiant']} - {$a['type']} - {$a['date']} - {$a['motif']} [{$a['statut']}]\n";
    }
    
    // Afficher le résumé final
    echo "\nOK - " . count($absences) . " absences importees\n";
    
} catch (Exception $e) {
    // Afficher l'erreur si l'import échoue
    echo "ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
