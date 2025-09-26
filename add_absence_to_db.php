<?php
require 'Database.php';
try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $sql = "SELECT * FROM etudiant";

    foreach ($pdo->query($sql) as $row) {
        echo $row['id'] . ' - ' . $row['nom'] . ' ' . $row['prenom'] . ' - ' . $row['formation'] . '<br>';
    }

    $pdo = null;
    
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}


// Fermeture de la connexion
$pdo = null;