<?php
// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['role'])) {
    die('Accès non autorisé');
}

$role = $_SESSION['role'];

// Charger la sidebar appropriée selon le rôle
switch ($role) {
    case 'etudiant':
    case 'etudiante':
        include __DIR__ . '/sidebar/nav_etudiant.php';
        break;
    
    case 'professeur':
        include __DIR__ . '/sidebar/nav_professeur.php';
        break;
    
    case 'responsable_pedagogique':
        include __DIR__ . '/sidebar/nav_responsable.php';
        break;
}
?>