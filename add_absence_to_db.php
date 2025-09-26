<?php
// Paramètres de connexion à la base de données
$host = 'node2.liruz.fr';
$port = '5435';
$dbname = 'sae';
$username = 'sae';
$password = '1zevkN&49b&&a*Pi97C';

try {
    // Création de la connexion PDO
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "Connexion à la base de données réussie !";
    
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}


// Fermeture de la connexion
$pdo = null;
?>