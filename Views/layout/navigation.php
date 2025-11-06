<?php
// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['role'])) {
    die('Accès non autorisé');
}

$role = $_SESSION['role'];

// Charger la sidebar appropriée selon le rôle
switch ($role) {
    case 'etudiant':
        include __DIR__ . '/sidebars/sidebar_etudiant.php';
        break;
    
    case 'professeur':
        include __DIR__ . '/sidebars/sidebar_professeur.php';
        break;
    
    case 'responsable_pedagogique':
        include __DIR__ . '/sidebars/sidebar_responsable.php';
        break;
}
?>