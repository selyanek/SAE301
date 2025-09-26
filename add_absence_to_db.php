<?php
require 'Database.php';
try {
    $db = new Database();
    $pdo = $db->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération des données du formulaire
        $etudiant_id = $_POST['etudiant_id'];
        $date_absence = $_POST['date_absence'];
        $justificatif = $_POST['justificatif']; // téléchargement de fichiers à gérer

        // Appel de la fonction pour ajouter l'absence
        $absence_id = ajouterAbsence($pdo, $etudiant_id, $date_absence, $justificatif);
        echo "Absence ajoutée avec l'ID : " . $absence_id;
    } else {
        echo "Méthode de requête non prise en charge.";
    }
    $pdo = null;
    
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

function ajouterAbsence($pdo, $etudiant_id, $date_absence, $justificatif) {
    $sql = "INSERT INTO absences (etudiant_id, date_absence, justificatif) VALUES (:etudiant_id, :date_absence, :justificatif)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':etudiant_id' => $etudiant_id,
        ':date_absence' => $date_absence,
        ':justificatif' => $justificatif
    ]);
    return $pdo->lastInsertId();
}


// Fermeture de la connexion
$pdo = null;