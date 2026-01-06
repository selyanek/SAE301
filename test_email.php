<?php
/**
 * Script de test pour l'envoi d'email
 * Utilisez ce script pour tester la configuration SMTP
 */

require_once __DIR__ . '/vendor/autoload.php';
use src\Models\EmailService;

// Activer l'affichage des erreurs pour le debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test d'envoi d'email</h2>";

try {
    $emailService = new EmailService();
    
    // Remplacez ces valeurs par vos données de test
    $testEmail = 'dilara.simsek@uphf.fr'; // CHANGEZ CETTE ADRESSE
    $testName = 'Test Étudiant';
    $testDateStart = '2024-01-15 09:00:00';
    $testDateEnd = '2024-01-15 11:00:00';
    $testMotif = 'Test d\'absence - Ne pas prendre en compte';
    
    echo "<p>Envoi d'un email de test à : <strong>{$testEmail}</strong></p>";
    echo "<p>Veuillez vérifier votre boîte de réception (et les spams).</p>";
    
    $result = $emailService->sendAbsenceConfirmationEmail(
        $testEmail,
        $testName,
        $testDateStart,
        $testDateEnd,
        $testMotif
    );
    
    if ($result) {
        echo "<p style='color: green;'><strong>✓ Email envoyé avec succès !</strong></p>";
        echo "<p>Vérifiez votre boîte de réception à l'adresse : {$testEmail}</p>";
    } else {
        echo "<p style='color: red;'><strong>✗ Échec de l'envoi de l'email</strong></p>";
        echo "<p>Vérifiez les logs d'erreur PHP pour plus de détails.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Erreur :</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Détails :</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><a href='src/Views/depotJustif.php'>Retour au formulaire de dépôt</a></p>";
?>

