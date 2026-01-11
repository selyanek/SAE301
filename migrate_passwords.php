<?php
/**
 * Script de migration pour hacher tous les mots de passe existants dans la base de données
 * À exécuter une seule fois après la mise en place du système de hachage
 * 
 * Usage: php migrate_passwords.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use src\Database\Database;

try {
    echo "=== Migration des mots de passe ===" . PHP_EOL;
    echo "Début du processus..." . PHP_EOL . PHP_EOL;
    
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Récupérer tous les utilisateurs
    $stmt = $pdo->query("SELECT idCompte, identifiantCompte, mot_de_passe FROM Compte");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = count($users);
    $updated = 0;
    $skipped = 0;
    
    echo "Nombre total d'utilisateurs trouvés : $total" . PHP_EOL . PHP_EOL;
    
    foreach ($users as $user) {
        $id = $user['idcompte'];
        $identifiant = $user['identifiantcompte'];
        $currentPassword = $user['mot_de_passe'];
        
        // Vérifier si le mot de passe est déjà haché (commence par $2y$ pour bcrypt)
        if (strpos($currentPassword, '$2y$') === 0 || strpos($currentPassword, '$2a$') === 0 || strpos($currentPassword, '$2b$') === 0) {
            echo "✓ Utilisateur $identifiant : mot de passe déjà haché, ignoré" . PHP_EOL;
            $skipped++;
            continue;
        }
        
        // Hacher le mot de passe en clair
        $hashedPassword = password_hash($currentPassword, PASSWORD_DEFAULT);
        
        // Mettre à jour la base de données
        $updateStmt = $pdo->prepare("UPDATE Compte SET mot_de_passe = :hashed WHERE idCompte = :id");
        $success = $updateStmt->execute([
            ':hashed' => $hashedPassword,
            ':id' => $id
        ]);
        
        if ($success) {
            echo "✓ Utilisateur $identifiant : mot de passe haché avec succès" . PHP_EOL;
            $updated++;
        } else {
            echo "✗ Utilisateur $identifiant : erreur lors du hachage" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== Résumé ===" . PHP_EOL;
    echo "Total d'utilisateurs : $total" . PHP_EOL;
    echo "Mots de passe hachés : $updated" . PHP_EOL;
    echo "Déjà hachés (ignorés) : $skipped" . PHP_EOL;
    echo PHP_EOL . "Migration terminée avec succès !" . PHP_EOL;
    
} catch (Exception $e) {
    echo PHP_EOL . "ERREUR : " . $e->getMessage() . PHP_EOL;
    echo "Trace : " . $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
