<?php

// fichier généré pour gérer l'import des CSV dans la base de données

require_once __DIR__ . '/vendor/autoload.php';

use src\Database\Database;
use src\Models\GestionCSV;

// Connexion à la base de données
$database = new Database();
$gestionCSV = new GestionCSV();

echo "=== Import des données CSV vers la base de données ===\n\n";

// Liste des fichiers CSV à importer
$csvFiles = [
    __DIR__ . '/data/CSV/BUT1-240122-240223_anonymise.CSV',
    __DIR__ . '/data/CSV/BUT2-240122-240223_anonymise.CSV',
    __DIR__ . '/data/CSV/BUT3-240122-240223_anonymise.CSV'
];

foreach ($csvFiles as $file) {
    if (!file_exists($file)) {
        echo "⚠️  Fichier non trouvé: $file\n";
        continue;
    }
    
    echo "📁 Import de: " . basename($file) . "\n";
    
    try {
        $result = $gestionCSV->exportToDB($file, $database);
        $stats = json_decode($result, true);
        
        echo "✅ Import réussi!\n";
        echo "   - Comptes créés: " . $stats['comptes'] . "\n";
        echo "   - Étudiants créés: " . $stats['etudiants'] . "\n";
        echo "   - Ressources créées: " . $stats['ressources'] . "\n";
        echo "   - Professeurs créés: " . $stats['professeurs'] . "\n";
        echo "   - Cours créés: " . $stats['cours'] . "\n";
        echo "   - Absences créées: " . $stats['absences'] . "\n";
        echo "\n";
        
    } catch (Exception $e) {
        echo "❌ Erreur lors de l'import: " . $e->getMessage() . "\n\n";
    }
}

echo "=== Vérification des données importées ===\n\n";

try {
    $pdo = $database->getConnection();
    
    // Compter les étudiants
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Etudiant");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "👨‍🎓 Nombre d'étudiants: $count\n";
    
    // Afficher quelques exemples d'étudiants
    $stmt = $pdo->query("SELECT c.identifiantCompte, c.nom, c.prenom, e.formation 
                         FROM Etudiant e 
                         JOIN Compte c ON e.idEtudiant = c.idCompte 
                         LIMIT 5");
    echo "   Exemples:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - {$row['identifiantcompte']} ({$row['prenom']} {$row['nom']}) - {$row['formation']}\n";
    }
    echo "\n";
    
    // Compter les professeurs
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Professeur");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "👨‍🏫 Nombre de professeurs: $count\n";
    
    // Afficher quelques exemples de professeurs
    $stmt = $pdo->query("SELECT c.identifiantCompte, c.nom, c.prenom 
                         FROM Professeur p 
                         JOIN Compte c ON p.idProfesseur = c.idCompte 
                         LIMIT 5");
    echo "   Exemples:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - {$row['identifiantcompte']} ({$row['prenom']} {$row['nom']})\n";
    }
    echo "\n";
    
    // Compter les ressources
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Ressource");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "📚 Nombre de ressources: $count\n";
    
    // Afficher quelques exemples
    $stmt = $pdo->query("SELECT nom FROM Ressource LIMIT 5");
    echo "   Exemples:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - {$row['nom']}\n";
    }
    echo "\n";
    
    // Compter les cours
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Cours");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "🎓 Nombre de cours: $count\n";
    
    // Détails sur les cours avec évaluations
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Cours WHERE evaluation = TRUE");
    $evaluations = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "   - Avec évaluation: $evaluations\n";
    echo "   - Sans évaluation: " . ($count - $evaluations) . "\n";
    echo "\n";
    
    // Compter les absences
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Absence");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "📋 Nombre d'absences: $count\n";
    
    // Détails sur les absences
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Absence WHERE justifie = TRUE");
    $justified = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "   - Justifiées: $justified\n";
    echo "   - Non justifiées: " . ($count - $justified) . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification: " . $e->getMessage() . "\n";
}

echo "\n✨ Import terminé!\n";
