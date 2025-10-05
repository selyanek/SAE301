<?php
// get_id.php
// (à compléter)
// Ce script récupère l'ID d'un étudiant à partir de son nom d'utilisateur, qui sera affiché sur la page d'accueil.
require 'Database.php';
try {
    $username = // Récupérer le nom d'utilisateur de la session ou d'une autre source
    $db = new Database();
    $pdo = $db->getConnection();
    $sql = 'SELECT id FROM Etudiant WHERE username = :username';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $result['id'];
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>